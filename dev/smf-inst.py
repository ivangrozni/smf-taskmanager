#!/usr/bin/python2
# -*- coding: utf-8 -*-

from selenium import webdriver
from tinydb import TinyDB

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait as wdw
from selenium.webdriver.support import expected_conditions as EC

#from random import randint, random
#import re

# login/logout
# remove module
# install module

uname = "bratovsek"
passwd = "baba"
url = "http://www.studentska-iskra.org/testforum"
modname="Delegator"

class smfInst:
    def __init__(self, uname, passwd, url, modname, browser=webdriver.Chrome): 
        self.uname = uname;
        self.passwd = passwd;
        self.url = url;
        self.modname = modname;
        self.browser = browser();

    def login(self):
        # preveri, ce sem ze logiran (do nothing in that case)
        # poisce polja in jih izpolni
        self.browser.get(self.url)
        # preverjanje
        if (len(self.browser.find_elements_by_id("button_logout")) > 0):
            return None
        # login
        funame = self.browser.find_element_by_xpath("//form[@id='guest_form']/input[@name='user']")
        fpassw = self.browser.find_element_by_xpath("//form[@id='guest_form']/input[@name='passwrd']")
        funame.send_keys(self.uname)
        fpassw.send_keys(self.passwd)
        self.browser.find_element_by_xpath("//form[@id='guest_form']/input[@class='button_submit']").click()
        # ce naredim login(), logout(), login() - ne dela
        

    def logout(self):
        if (len(self.browser.find_elements_by_id("button_logout")) > 0):
            self.browser.find_element_by_id("button_logout").click()

    def go_packages(self):
        # url2="?action=admin;area=packages"
        self.browser.find_element_by_xpath("//li[@id='button_admin']/a[contains(@href, 'action=admin')]").click()
        self.browser.find_element_by_xpath("//h5/a[contains(@href, 'area=packages')]").click()
        
    def check(self):
        """ Returns states:
            0 - no module
            1 - only uploaded
            2 - uploaded and installed"""
        try:
            td = selfbrowser.find_element_by_xpath("//td[text()='Delegator']")
        except: # NoSuchElementError:
            return 0
        td = len(self.browser.find_elements_by_xpath("//tr/td/a[contains(@href, 'sa=install;package="+self.modname.lower()+".zip')]"))
        if td == True: return 1
        else: return 2

    def uninst(self):
        # preveri se, ce obstajajo kaki testi, ki so failali
        self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=uninstall;package="+self.modname.lower()+".zip')]").click()
        self.browser.find_element_by_xpath("//form/input[@class='button_submit']").click()

    def delete(self):
        self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=remove;package="+self.modname.lower()+".zip')]").click()
        self.browser.find_element_by_xpath("//form/input[@class='button_submit']").click()        


# tako se dela delay, al kaj        
#             gumb = wdw(self.browser, 5).until(
#                EC.presence_of_element_located((By.CLASS_NAME, 'comment-show-hide'))
#            )        
