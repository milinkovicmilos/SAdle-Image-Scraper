from selenium import webdriver
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By

import mariadb
import time
import requests

driver = webdriver.Firefox()
driver.get("https://www.igcd.net/game.php?l=en&id=402224")

# connection parameters
conn_params= {
    "user" : "rey",
    "password" : "",
    "host" : "localhost",
    "database" : "sadle_db"
}

# Establish a connection
connection= mariadb.connect(**conn_params)

cursor= connection.cursor()

# retrieve data
cursor.execute("SELECT id, name FROM vehicles")

# print content
row= cursor.fetchall()
for el in row:
    print(str(el[0]) + ', ' + el[1])

cars = driver.find_elements(By.XPATH, "//div[contains(@class, 'voiture mdl-card')]")

length = len(cars)
i = length - 45

while i < length:
    
    car = cars[i]
    car_name = car.find_element(By.XPATH, './/center[1]/i').get_attribute('innerHTML')
    car_link = car.find_element(By.CLASS_NAME, 'Thumbnail')
    car_link.click()

    image_links = driver.find_elements(By.CLASS_NAME, 'VehiclePicture')
    print(image_links)
    
    time.sleep(0.2)
    front_image = image_links[0].get_attribute('src')
    print(front_image)

    img_data = requests.get(front_image).content
    with open(f'./python/Downloads/{car_name}_FRONT.jpg', 'wb') as handler:
        handler.write(img_data)

    try:
        back_image = image_links[1].get_attribute('src')

        img_data = requests.get(back_image).content
        with open(f'./python/Downloads/{car_name}_BACK.jpg', 'wb') as handler:
            handler.write(img_data)
    except: pass
    
    driver.back()

    cars = driver.find_elements(By.XPATH, "//div[contains(@class, 'voiture mdl-card')]")
    i += 1
    if (car_name == 'Dinghy'): break

driver.quit()

# free resources
cursor.close()
connection.close()
