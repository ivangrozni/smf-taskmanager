Taskmanager app za iskrin forum

http://wiki.studentska-iskra.org/index.php?title=TaskManagerApp

DONE:
   - baza je ustvarjena in populirana

TO DO:
   - natunat bazo (AUTO_INCREMENT... http://www.mysqltutorial.org/mysql-sequence/)
   - poiskati querry-je za posamezne prikaze baze
   - vgraditi querry-je v php
   - vgraditi php v samo strukturo smf foruma

NOV NACRT:
Nasim potrebam se prilagodi mod TO-DO LIST (nahaja se v mapi to-do).
Naloga je zahtevnejsa kot se zdi - treba je znat strukturo smf mod-ov, zato pa ne obstaja dokumentacije na netu - skoraj nic.

Studiranje strukture se nahaja v datoteki taskmanager/scratch.txt.

Predlagam, da se instalira testni forum, na katerem bomo lahko testirali app.

Spet lahko ustanovimo torke zvecer - najmanj 4h - da se projekt spelje do konca. Pavsalna ocena je 150 ur skupnega programiranja vseh programerjev.

Verzija 1.0 taskmanager:

BAZA - urejena kronolosko 

id_todo       INT(10) PRIM auto_increment   |  
id_todo_sec   INT(10)                       |  default = 0 poveca se s st izvrsevalcev
id_autor      INT(10)                       |  avtor naloge
task          CHAR(50)                      |  ime naloge
project       CHAR(50)                      |  h kateremu projektu pase
description   CHAR(250)                     |  opis naloge
start_date    DATE                          |  kdaj je treba nalogo zaceti izvrsevati
duedate       DATE                          |  rok
priority      TINYINT(2)                    |  0 - low, 1 - normal, 2 - high

is_did        TINYINT(2)                    |  stanje: 0prazno,1vteku,2zakljuceno,3preklic
id_member     INT(10)                       |  izvrsevalec naloge

end_date      DATE                          |  datum zakljucka naloge
end_comment   CHAR(250)                     |  komentar ob zakljucku naloge

AKCIJE

 add_task
 accept
 task_finished

VIEWS
 empty_tasks
 emptyAndInProgres_tasks
 finished_tasks
 timeline


Verzija 2.0
 - dobi tage k taskom

DONE

  datebase.php  - ustvarjena je baza

TO DO
  
  package-info.xml
  modification.xml
  TaskList.xml      (akcije)
  TaskList.template.php   (views)