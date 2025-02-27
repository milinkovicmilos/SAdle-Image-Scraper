from selenium import webdriver
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException
from dataclasses import dataclass
import json
import time

@dataclass
class Mission:
    title: str
    origin: str 
    giver: str 
    description: str 
    objective: list
    reward: str

service = Service(executable_path="./geckodriver")
driver = webdriver.Firefox(service=service)
places = ['Los Santos', 'Countryside', 'San Fierro', 'Desert', 'Las Venturas']

driver.get("https://gta.fandom.com/wiki/Missions_in_GTA_San_Andreas")

WebDriverWait(driver, 5).until(
    EC.presence_of_element_located((By.CLASS_NAME, "mw-headline"))
)


tables = driver.find_elements(By.CLASS_NAME, 'wikitable')
rows = []
descriptions = []
current_descriptions = []
rewards_current = []
missions = []
giver = ""

for table in tables:
    rows = (table.find_elements(By.XPATH, './/tbody/tr'))[1:]
    giver = table.find_elements(By.XPATH, 'preceding-sibling::h3[1]')[0]
    origin = table.find_elements(By.XPATH, 'preceding-sibling::h2[1]')[0].text[:-9]
    if origin == "Return to Los Santos":
        origin = "Los Santos"
    for row in rows:
        title = row.find_elements(By.XPATH, './/td')[0].text
        mission = Mission("", "", "", "", [], "")
        mission.giver = giver.text
        mission.origin = origin
        try:
            mission.description = row.find_element(By.TAG_NAME, 'small').text
        except NoSuchElementException:
            try:
                mission.description = row.find_element(By.TAG_NAME, 'ul').text
            except NoSuchElementException: 
                mission.description = row.find_elements(By.TAG_NAME, 'td')[1].text
        try:
            mission.objective = row.find_element(By.TAG_NAME, 'ul').text
        except NoSuchElementException:
            mission.objective = mission.description

        mission.reward = row.find_elements(By.XPATH, './/td')[2].text
        mission.title = title
        missions.append(mission)
with open("missions.json", "w") as f:
    json.dump([mission.__dict__ for mission in missions], f, indent=4)

# for mis in missions:
#     print(mis.title)

driver.quit()
