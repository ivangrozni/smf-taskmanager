[size=3][b][color=maroon]Delegator[/color][/b][/size] by Iskra
[b]Description:[/b] You can use this to tell people what to do

[b]Version 0.1:[/b]
2014 11 09 
Odkrita huda napaka na forum_kopija, na katerem naj bi testirali delegator.
Zato so bila testiranja neuspesna. Krivec je Jaka Perovsek. Pozabil je pobrisat Cache file.

2015 02 21
Torej, dobil sem novo idejo. Vsaj projektu bi lahko dali zraven še topic_id, v katerem bi potekala debata o tem projektu... Kjer bi bil pregled nad zadolžitvami projekta...

2015 03 01
* add_task ne doda delegatov
* najdalsa dolzina stringa za description je 50 znakov, ceprav je v database.php 250 - DELA
* isMemberWorker se vedno bricka cel delegator - DELA (ne smes imeti enako imenovanih funkcij funkcij v Delegator.php in Delegator.template.php )

*end_task - DELA
Kako ves, da je task v izvrsevanju - v tabeli workers je njegov id...

Lahko bi stvar malce preuredili in end_comment, end_date ter end_state dodali k tabeli workers

Ampak to ne spremeni dejstva, da end_task ne dela!

Ena moznost je, da se da state v prikaz pod my_tasks -> tam zaenkrat se prikaze vse taske, ne glede na state.

Claim in Unclaim morata spremenit state. Kjerkoli pac ze...

To, kako se bodo urejala stanja je treba resno natuhtat.
-----------------------------------------------------------

DONE:
edit_task
view_projects
view_worker

TO DO:
claim/unclaim updejtata tabelo tasks state => 1
my_tasks (gumbek za myfinished tasks)
isMemberWorker
end_task
tabela logov, kjer se vse vidi, kdo je kaj delal
php_sidebar

FUTURE
edit_proj, del_proj

Dalo bi se naredit funkcijo, ki dobi kot argument string, ki je query za bazo podatkov, in vrne fetch results... Tako ne bi rabili tistih anonimnih funkcij skoz...
