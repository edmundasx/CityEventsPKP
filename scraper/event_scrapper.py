from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException, NoSuchElementException, ElementClickInterceptedException
from bs4 import BeautifulSoup
import pandas as pd
import time

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
                    
                    if title:
                        events.append({
                            'id': idx,
                            'pavadinimas': title,
                            'data_laikas': date,
                            'vieta': location,
                            'kategorija': category
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


def main():
    print("VILNIUS EVENTS SCRAPER v2.0")
    
    scraper = VilniusEventsScraper(headless=True)
    url = "https://www.vilnius-events.lt/renginiai-pagal-vieta/"
    events = scraper.scrape_events(url)
    
    if events:
        print(f"\nCollected {len(events)} events")
        df = pd.DataFrame(events)
        print(df.head(10))
    else:
        print("\nNo events collected")


if __name__ == "__main__":
    main()