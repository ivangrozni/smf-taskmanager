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

uname = ""
passwd = ""
url = ""
modname=""
path = "" # path to zip fajl


class smfInst:
    def __init__(self, uname, passwd, url, modname, mpath, browser=webdriver.Chrome): 
        self.uname = uname;
        self.passwd = passwd;
        self.url = url;
        self.modname = modname;
        self.mpath = mpath; #path to zip file on your computer
        self.browser = browser();

    def login(self):
        # preveri, ce sem ze logiran (do nothing in that case)
        # poisce polja in jih izpolni
        self.browser.get(self.url)
        # preverjanje
        if (len(self.browser.find_elements_by_id("button_logout")) > 0):
            return None
        # login
        # @todo: add waits, maybe that is why it fails second time
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
        # @todo BUG: did not find link to area packages (I suspect it needs delay)
        self.browser.find_element_by_xpath("//li[@id='button_admin']/a[contains(@href, 'action=admin')]").click()
        self.browser.find_element_by_xpath("//h5/a[contains(@href, 'area=packages')]").click()
        
    def check_state(self):
        """ Returns states:
            0 - no module
            1 - only uploaded
            2 - uploaded and installed"""
        td = len(self.browser.find_elements_by_xpath("//td[text()='"+self.modname+"']"))
        if td == 0:
            print "No smf module named "+self.modname+"."
            return td
        elif td > 0:
            td = len(self.browser.find_elements_by_xpath("//tr/td/a[contains(@href, 'sa=install;package="+self.modname.lower()+".zip')]"))
            if td:
                print self.modname+" only uploaded."
                return 1
            else:
                print self.modname+" uploaded and installed."
                return 2
        
    def uninst(self):
        # preveri se, ce obstajajo kaki testi, ki so failali
        self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=uninstall;package="+self.modname.lower()+".zip')]").click()
        # test
        td = self.browser.b.find_elements_by_xpath("//table[@class='table_grid']/tbody/tr/td[contains(., 'Test')]")
        for t in td:
            print t.text
            if t.text != "Test succesful":
                pass # prompt - do you want to continue? @todo

        self.browser.find_element_by_xpath("//form/input[@class='button_submit']").click()

    def delete(self):
        self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=remove;package="+self.modname.lower()+".zip')]").click()
        self.browser.find_element_by_xpath("//form/input[@class='button_submit']").click()        


# tako se dela delay, al kaj        
#             gumb = wdw(self.browser, 5).until(
#                EC.presence_of_element_located((By.CLASS_NAME, 'comment-show-hide'))
#            )        

# ToDo:
# - upload
# - install
# - test - all

    def upload(self):
        # you have to be in go_packages()
        # @todo maybe check
        self.browser.find_element_by_xpath("//a[@class='firstlevel' and contains(@href, 'sa=packageget')]").click()
        cc = self.browser.find_element_by_xpath("//input[@class='input_file' and @type='file']")
        cc.send_keys(self.path)

    def install(self):
        self.browser.find_element_by_xpath("//tr/td/a[contains(@href, 'sa=install;package="+self.modname.lower()+".zip')]").click()
        # test
        td = self.browser.b.find_elements_by_xpath("//table[@class='table_grid']/tbody/tr/td[contains(., 'Test')]")
        for t in td:
            print t.text
            if t.text != "Test succesful":
                pass # prompt - do you want to continue? @todo

        self.browser.find_element_by_xpath("//div[@id='admin_content']/div/form/div/input[@class='button_submit']").click() # @todo be more specific

def new_version(self):
    # run ./mkpkg.sh
    a = smfInst(uname, passwd, url, modname, mpath, webdriver.Firefox)
    a.login()
    a.go_packages()
    s = a.check_state()
    if s==0:
        a.upload()
        a.install()
    elif s==1:
        a.delete()
        a.upload()
        a.install()
    elif s==2:
        a.uninst()
        a.delete()
        a.upload()
        a.install()
    else:
        print "Unkown state!\nAborting operation."
    a.browser.quit()
    return None
