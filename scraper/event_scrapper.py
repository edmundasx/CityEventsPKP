from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException, NoSuchElementException, ElementClickInterceptedException
from bs4 import BeautifulSoup
import pandas as pd
import time
import json
import sqlite3

class VilniusEventsFullScraper:
    def __init__(self, headless=True):
        options = Options()
        if headless:
            options.add_argument('--headless=new')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.add_argument('--disable-gpu')
        options.add_argument('--window-size=1920,1080')
        options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
        
        self.driver = webdriver.Chrome(options=options)
        self.base_url = "https://www.vilnius-events.lt"
        self.wait = WebDriverWait(self.driver, 10)
    
    def click_load_more_until_end(self):
        """
        Spaudžia 'Daugiau renginių' mygtuką tol, kol jis nebeatsiranda
        """
        click_count = 0
        
        while True:
            try:
                self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(1)
                
                load_more_button = None
                
                try:
                    load_more_button = self.wait.until(
                        EC.element_to_be_clickable((By.XPATH, 
                            "//a[contains(text(), 'Daugiau renginių')] | //button[contains(text(), 'Daugiau renginių')] | //div[contains(text(), 'Daugiau renginių')]"))
                    )
                except TimeoutException:
                    try:
                        load_more_button = self.driver.find_element(By.CLASS_NAME, "more-date-block")
                        load_more_button = load_more_button.find_element(By.TAG_NAME, "a")
                    except NoSuchElementException:
                        pass
                
                if not load_more_button:
                    print("Nebėra 'Daugiau renginių' mygtuko - visi renginiai užkrauti!")
                    break

                if not load_more_button.is_displayed():
                    print("Mygtukas neberodo - visi renginiai užkrauti!")
                    break

                self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", load_more_button)
                time.sleep(0.5)

                try:
                    load_more_button.click()
                except ElementClickInterceptedException:
                    self.driver.execute_script("arguments[0].click();", load_more_button)
                
                click_count += 1
                print(f"Paspaustas 'Daugiau renginių' mygtukas ({click_count} kartą)...")
                time.sleep(2)
                
            except TimeoutException:
                print("Mygtukas neberastas - visi renginiai užkrauti!")
                break
            except NoSuchElementException:
                print("Mygtukas nebeegzistuoja - visi renginiai užkrauti!")
                break
            except Exception as e:
                print(f"Klaida spaudžiant mygtuką: {e}")
                break
        print(f"\nIš viso paspaustas mygtukas {click_count} kartų")
        return click_count
    
    def scrape_all_events(self, url: str):
        """
        Scrape'ina visus renginius su automatiniu 'Daugiau renginių' spaudimu
        """
        try:
            print(f"Atidaro puslapį: {url}")
            self.driver.get(url)
            
            print("Laukiama, kol užsikraus pradinis turinys...")
            self.wait.until(
                EC.presence_of_element_located((By.CLASS_NAME, "o-card"))
            )
            time.sleep(2)

            print("\nPradedamas automatinis renginių krovimas...")
            self.click_load_more_until_end()

            self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(2)
            self.driver.execute_script("window.scrollTo(0, 0);")
            time.sleep(1)
            
            print("\n🔍 Ištraukiami renginių duomenys...")
            html = self.driver.page_source
            soup = BeautifulSoup(html, 'html.parser')

            event_cards = soup.find_all('div', class_='o-card')
            print(f"Rasta renginių kortelių: {len(event_cards)}")
            events = []

            for idx, card in enumerate(event_cards, 1):
                try:

                    title_elem = card.find('h5', class_='m-card__description-title')
                    title = title_elem.get_text(strip=True) if title_elem else ""

                    date_elem = card.find('p', class_='m-card__description-date')
                    date = date_elem.get_text(strip=True) if date_elem else ""
                    

                    location_elem = card.find('p', class_='m-card__location')
                    location = location_elem.get_text(strip=True) if location_elem else ""

                    category_elem = card.find('p', class_='m-card__category')
                    category = category_elem.get_text(strip=True) if category_elem else ""

                    link_elem = card.find('a', class_='u-text-decoration-none')
                    link = ""
                    if link_elem and link_elem.get('href'):
                        href = link_elem['href']
                        link = href if href.startswith('http') else f"{self.base_url}{href}"
                    

                    img_elem = card.find('img', class_='m-card__image')
                    image_url = ""
                    if img_elem and img_elem.get('src'):
                        src = img_elem['src']
                        image_url = src if src.startswith('http') else f"{self.base_url}{src}"
                    
                    if title:
                        events.append({
                            'id': idx,
                            'pavadinimas': title,
                            'data_laikas': date,
                            'vieta': location,
                            'kategorija': category,
                            'nuoroda': link,
                            'paveiksliukas': image_url
                        })
                except Exception as e:
                    print(f"Klaida apdorojant kortelę #{idx}: {e}")
                    continue
            
            return events
            
        except Exception as e:
            print(f"Klaida: {e}")
            import traceback
            traceback.print_exc()
            return []
        
        finally:
            self.driver.quit()
    
    def save_results(self, events, base_filename='vilnius_events'):
        """Išsaugo rezultatus CSV, JSON ir SQLite duomenų bazės formatais"""
        if not events:
            print("\nNėra duomenų išsaugojimui!")
            return
        df = pd.DataFrame(events)
        csv_file = f'{base_filename}.csv'
        df.to_csv(csv_file, index=False, encoding='utf-8-sig')
        print(f"Išsaugota į {csv_file}")
        

        json_file = f'{base_filename}.json'
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(events, f, ensure_ascii=False, indent=2)
        print(f"Išsaugota į {json_file}")
        
        try:
            db_file = f'{base_filename}.db'
            conn = sqlite3.connect(db_file)

            cursor = conn.cursor()
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS renginiai (
                    id INTEGER PRIMARY KEY,
                    pavadinimas TEXT,
                    data_laikas TEXT,
                    vieta TEXT,
                    kategorija TEXT,
                    nuoroda TEXT,
                    paveiksliukas TEXT
                )
            ''')
            for event in events:
                cursor.execute('''
                    INSERT INTO renginiai (id, pavadinimas, data_laikas, vieta, kategorija, nuoroda, paveiksliukas)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ''', (
                    event['id'],
                    event['pavadinimas'],
                    event['data_laikas'],
                    event['vieta'],
                    event['kategorija'],
                    event['nuoroda'],
                    event['paveiksliukas']
                ))
            
            conn.commit()
            conn.close()
            print(f"Išsaugota į {db_file} (SQLite duomenų bazė)")
            
        except Exception as e:
            print(f"Nepavyko išsaugoti SQLite duomenų bazės: {e}")


def print_statistics(events):
    """Atspausdina statistiką apie surinktus renginius"""
    if not events:
        return
    print(f"\n{'='*80}")
    print("STATISTIKA")
    print(f"{'='*80}")
    print(f"\nIš viso renginių: {len(events)}")
    
    categories = {}
    for event in events:
        cat = event['kategorija']
        if cat:
            categories[cat] = categories.get(cat, 0) + 1
    
    if categories:
        print("\nRenginiai pagal kategorijas:")
        for cat, count in sorted(categories.items(), key=lambda x: x[1], reverse=True):
            print(f"   • {cat}: {count}")
    
    locations = {}
    for event in events:
        loc = event['vieta']
        if loc:
            locations[loc] = locations.get(loc, 0) + 1
    
    if locations:
        print(f"\n📍 Top 10 populiariausių vietų:")
        for loc, count in sorted(locations.items(), key=lambda x: x[1], reverse=True)[:10]:
            print(f"   • {loc}: {count}")
    print(f"\n{'='*80}")


def print_sample_events(events, n=5):
    """Atspausdina kelis pavyzdinius renginius"""
    if not events:
        return
    
    print(f"\n{'='*80}")
    print(f"PIRMI {min(n, len(events))} RENGINIAI:")
    print(f"{'='*80}")
    for i, event in enumerate(events[:n], 1):
        print(f"\n{i}. {event['pavadinimas']}")
        print(f"{event['data_laikas']}")
        print(f"{event['vieta']}")
        print(f"{event['kategorija']}")
        print(f"{event['nuoroda'][:80]}...")


def main():
    print("="*80)
    print("VILNIUS EVENTS FULL SCRAPER")
    print("="*80)
    print("Automatiškai spaudžia 'Daugiau renginių' ir surenka VISUS renginius!\n")

    scraper = VilniusEventsFullScraper(headless=True)
    
    url = "https://www.vilnius-events.lt/renginiai-pagal-vieta/"
    events = scraper.scrape_all_events(url)
    
    if events:
        print(f"\nSĖKMINGAI SURINKTA {len(events)} RENGINIŲ")
        print_statistics(events)
        print_sample_events(events, n=5)
        print(f"\n{'='*80}")
        print("IŠSAUGOJAMI REZULTATAI...")
        print(f"{'='*80}\n")
        scraper.save_results(events, 'vilnius_events_full')
        
        print(f"\n{'='*80}")
        print(" VISKAS ATLIKTA SĖKMINGAI!")
        print(f"{'='*80}\n")
    else:
        print("\n NEPAVYKO SURINKTI RENGINIŲ")
        print("\nGalimos problemos:")
        print("  1. Svetainės struktūra pasikeitė")
        print("  2. Interneto ryšio problemos")
        print("  3. Svetainė blokuoja automatinį naršymą")


if __name__ == "__main__":
    main()