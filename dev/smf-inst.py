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
        

    def logout(self):
        if (len(self.browser.find_elements_by_id("button_logout")) > 0):
            self.browser.find_element_by_id("button_logout").click()

    def check(self):
        """ Returns states:
            0 - no module
            1 - only uploaded
            2 - uploaded and installed"""
        url2="?action=admin;area=packages"
        self.browser.get(self.url+url2)
        # fino bi bilo pogledat ce je Delegator
        # installiran in njegovo stanje
        try:
            td = selfbrowser.find_element_by_xpath("//td[text()='Delegator']")
        except: # NoSuchElementError:
            return 0
        td = self.browser.find_elements_by_xpath("//tr/td/a[contains(@href, 'package="+self.modname.lower()+".zip')]")
        return td - 1

    def uninst(self):
        url2="?action=admin;area=packages"
        self.browser.get(self.url+url2)
        td = self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=uninstall;package="+self.modname.lower()+".zip')]")
        td.click()
