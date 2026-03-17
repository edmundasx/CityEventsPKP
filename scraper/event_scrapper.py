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

class VilniusEventsScraper:
    def __init__(self, headless=True):
        options = Options()
        if headless:
            options.add_argument('--headless=new')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.add_argument('--disable-gpu')
        options.add_argument('--window-size=1920,1080')
        options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
        
        self.driver = webdriver.Chrome(options=options)
        self.base_url = "https://www.vilnius-events.lt"
        self.wait = WebDriverWait(self.driver, 10)
    
    def click_load_more(self):
        click_count = 0
        
        while True:
            try:
                self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(1)
                
                load_more_button = None
                
                try:
                    load_more_button = self.wait.until(
                        EC.element_to_be_clickable((By.XPATH, 
                            "//a[contains(text(), 'Daugiau renginiu')] | //button[contains(text(), 'Daugiau renginiu')]"))
                    )
                except TimeoutException:
                    try:
                        load_more_button = self.driver.find_element(By.CLASS_NAME, "more-date-block")
                        load_more_button = load_more_button.find_element(By.TAG_NAME, "a")
                    except NoSuchElementException:
                        pass
                
                if not load_more_button or not load_more_button.is_displayed():
                    break
                
                self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", load_more_button)
                time.sleep(0.5)
                
                try:
                    load_more_button.click()
                except ElementClickInterceptedException:
                    self.driver.execute_script("arguments[0].click();", load_more_button)
                
                click_count += 1
                print(f"Clicked load more button ({click_count} times)")
                time.sleep(2)
                
            except (TimeoutException, NoSuchElementException):
                break
            except Exception as e:
                print(f"Error clicking button: {e}")
                break
        
        print(f"Total clicks: {click_count}")
        return click_count
    
    def scrape_events(self, url: str):
        try:
            print(f"Loading page: {url}")
            self.driver.get(url)
            
            self.wait.until(
                EC.presence_of_element_located((By.CLASS_NAME, "o-card"))
            )
            time.sleep(2)
            
            print("Starting automatic loading...")
            self.click_load_more()
            
            self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(2)
            
            html = self.driver.page_source
            soup = BeautifulSoup(html, 'html.parser')
            
            event_cards = soup.find_all('div', class_='o-card')
            print(f"Found {len(event_cards)} events")
            
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
                    print(f"Error processing card #{idx}: {e}")
                    continue
            
            return events
            
        except Exception as e:
            print(f"Error: {e}")
            return []
        
        finally:
            self.driver.quit()
    
    def save_results(self, events, base_filename='vilnius_events'):
        if not events:
            print("No data to save")
            return
        
        df = pd.DataFrame(events)
        csv_file = f'{base_filename}.csv'
        df.to_csv(csv_file, index=False, encoding='utf-8-sig')
        print(f"Saved to {csv_file}")
        
        json_file = f'{base_filename}.json'
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(events, f, ensure_ascii=False, indent=2)
        print(f"Saved to {json_file}")


def main():
    print("VILNIUS EVENTS SCRAPER v3.0")
    
    scraper = VilniusEventsScraper(headless=True)
    url = "https://www.vilnius-events.lt/renginiai-pagal-vieta/"
    events = scraper.scrape_events(url)
    
    if events:
        print(f"\nCollected {len(events)} events")
        print("\nSaving results...")
        scraper.save_results(events, 'vilnius_events')
        print("\nComplete!")
    else:
        print("\nNo events collected")


if __name__ == "__main__":
    main()