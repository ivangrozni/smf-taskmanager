Taskmanager app za iskrin forum

http://wiki.studentska-iskra.org/index.php?title=TaskManagerApp

______________________________________________________________

DONE:
 - zasnova baze (tm_new.txt)
 - zasnova querry-jev (tm_new.txt)

TO DO:
 - Ustvari bazo in jo populiraj (3 tabele - Tasks, Projects, Members)
 - Preveri Querry-je
 - Popravi Querry-je
 - Vgradi Querry-je v PHP funkcije
 - Uvozi PHP funkcije v smf-mod

______________________________________________________________


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

===Verzija 1.0 taskmanager:===

BAZA - urejena kronolosko 
<table>
 <tr> <td> id_todo     </td> <td> INT(10) PRIM auto_incr      </td> <td> </td> </tr>
 <tr> <td>id_todo_sec  </td> <td> INT(10)                     </td> <td>  default = 0 poveca se s st izvrsevalcev </td> </tr>
 <tr> <td>id_autor     </td> <td>INT(10)                      </td> <td>  avtor naloge </td> </tr>
 <tr> <td>task         </td> <td>CHAR(50)                     </td> <td>  ime naloge</td> </tr>
 <tr> <td>project      </td> <td>CHAR(50)                     </td> <td>  h kateremu projektu pase</td> </tr>
 <tr> <td>description  </td> <td>CHAR(250)                    </td> <td>  opis naloge</td> </tr>
 <tr> <td>start_date   </td> <td>DATE                         </td> <td>  kdaj je treba nalogo zaceti izvrsevati</td> </tr>
 <tr> <td>duedate      </td> <td>DATE                         </td> <td>  rok </td></tr>
 <tr> <td>priority     </td> <td>TINYINT(2)                   </td> <td>  0 - low, 1 - normal, 2 - high </td></tr>

 <tr> <td>is_did       </td> <td>TINYINT(2)                   </td> <td>  stanje: 0prazno,1vteku,2zakljuceno,3preklic</td> </tr>
 <tr> <td>id_member    </td> <td>INT(10)                      </td> <td>  izvrsevalec naloge </td></tr>

 <tr> <td>end_date     </td> <td>DATE                         </td> <td>  datum zakljucka naloge</td> </tr>
 <tr> <td>end_comment  </td> <td>CHAR(250)                    </td> <td>  komentar ob zakljucku naloge</td> </tr>
</table>
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

 - datebase.php  - ustvarjena je baza

TO DO
  
 - package-info.xml
 - modification.xml
 - TaskList.php      (akcije)
 - TaskList.template.php   (views)