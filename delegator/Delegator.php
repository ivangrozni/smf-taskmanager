<?php
/**********************************************************************************
* Delegator.php                                                                   *
***********************************************************************************
* Delegator                                                                       *
* =============================================================================== *
* Software Version:           Delegator 0.1                                       *
* Software by:                iskra dot@studentska-iskra.org                      *
* Original software: 	      To-Do list          				  *
* Original software by:       grafitus (beratdogan@hileci.org)                    *
* Copyright 2009-2014 by:     grafitus (beratdogan@hileci.org)                    *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
***********************************************************************************
* Delegator is continued work from To Do list created by grafitus - slava mu      *
**********************************************************************************/

// First of all, we make sure we are accessing the source file via SMF so that people can not directly access the file. Sledeci vrstici sta dodani, da kdo ne sheka (SMF uporablja v vseh fajlih, mod ni uporabljal).
if (!defined('SMF'))
    die('Hack Attempt...');

//Tu se zacne originalni To-Do list mod
function Delegator()
{
    global $context, $txt, $scripturl; // potrebne variable
                                       // $context - kontekst smf-ja kot takega. globalna spremenljivka, ki nudi Delegatorju
                                       //       kot modulu interakcijo s smf forumom
                                       // $txt - notri so vsa prikazana besedila (zaradi prevodov)
                                       // $scripturl - obstojec url, vpisan v brskalnik (simple nacin za generirat linke)

    //isAllowedTo('view_todo');        // za zdaj smo izkljucili permissione

    loadTemplate('Delegator');         // nalozi template

    $context['page_title'] = $txt['delegator'];   //poberes page title iz $txt['delegator']

    $subActions = array(                      //definira se vse funkcije v sklopu delegatorja
        'delegator' => 'delegator_main',      //tukaj bo pregled nad projekti in nedokoncanimi zadolzitvami
        'personal_view' => 'personal_view',   //zadolzitve trenutno logiranega uporabnika
        'proj' => 'proj',                     //[!]omfg, kje si g1smo?
                                                        //tukaj!

                                                        // ta pejdz bo vseboval seznam vseh projektov
        'add_proj' => 'add_proj',             // dodajanje novega projekta
        'add_task' => 'add_task',             // dodajanje novega taska
        'edit_task' => 'edit_task',           // urejanje taska
        'edit_proj' => 'edit_proj',           // editanje projekta
        'view_task' => 'view_task',           // pregled/podrobnosti taska
        'view_proj' => 'view_proj',           // pregled/podrobnosti projekta
            // Kasneje bomo dodali se razlicne view-je - prikaz casovnice...
            // Spodnji komentarji so stara To-Do list mod koda
            //'ToDo' => 'ToDoMain',
            //'add' => 'add',
            //'add2' => 'add2',
            //'delete' => 'delete',
            //'did' => 'didChange',
    );

    // Delegator v celoti je kot en modul. Razni viewi so "subactioni". Defaulten subaction je delegator. Ce
    // je izbran action ($_REQUEST['sa']) neveljaven ali ni specificiran, se zloada defaulten subaction, to je "delegator"
    if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))     //tega
        $sub_action = 'delegator';                                           //tko res
    else                                                                     //ne
        $sub_action = $_REQUEST['sa'];                                       //stekam

    // Dodaj delegator na navigacijo v zgornjem delu strani
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator',
        'name' => $txt['delegator']
    );

    $subActions[$sub_action]();

//Sixth, begin doing all the stuff that we want this action to display
// Store the results of this stuff in the $context array.
// This action's template(s) will display the contents of $context.

}

function delegator_main()                                      //glavna funkcija - prikaze taske
{
    // tukaj bi rad prikazal projekte in zadolzitve - mogoce je pomembnejse najprej zadolzitve
    global $context, $scripturl, $sourcedir, $smcFunc, $txt;   //globalne spremenljivke lahko kliceju funkcije iz zunaj kajne?

    //isAllowedTo('view_todo');                                // izkljuceni permissioni (za zdaj)

    $list_options = array(
        //'id' => 'list_todos',                                //stara To-Do List koda
        'id' => 'list_tasks',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator',       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE !!! uredi querry !!!

/*predlog za querry (prvi del):
SELECT *
FROM {db_prefix}tasks T1 LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
WHERE T1.state = 0 OR T1.state = 1

[!]Vprasanje: pod kaksnimi imeni bodo vrnjeni stolpci v $row? T1.name? name? tasks_name?
*/

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT *
					FROM {db_prefix}tasks
					WHERE state = 0 OR state = 1
					ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}\',
					array(
						\'id_member\' => $id_member,
						\'sort\' => $sort,
						\'start\' => $start,
						\'per_page\' => $items_per_page,
					)
				);
				$tasks = array();
				while ($row = $smcFunc[\'db_fetch_assoc\']($request))
					$tasks[] = $row;
				$smcFunc[\'db_free_result\']($request);

				return $tasks;                                    //funkcija vrne taske
                                '),
            'params' => array(
                'id_member' => $context['user']['id'],         //[!]zopet - kateri member? vcasih je
                 ),
        ),

        'get_count' => array(
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}tasks
                                        WHERE state = 0 OR state = 1\',
					array(
					)
				);
				list($total_tasks) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_tasks;
			'),
        ),
        'no_items_label' => $txt['tasks_empty'],
        'columns' => array(
            // ocitno imamo header, data in sort znotraj posamezne vrednosti v tabeli
            // name, deadline, priority - so ze narejeni
            // avtor, worker(s), projekt, stanje - se manjkajo - ugotoviti, kako jih zajeti

            // doda id stolpec v tabelo, ki se pojavi na strani od delegatorja
            'id' => array(
                'header' => array(
                    'value' => 'id',
                ),
                'data' => array(
                    'function' => create_function('$row', 'return $row[\'id\'];'
                    ),
                 'sort' => array(
                     'default' => 'id',
                     'reverse' => 'id DESC',
                    ),
                ),

            ), // oklepaji
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array( // ime taska
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array(
                    'function' => create_function('$row', '
						if (strtolower($row[\'name\']) == \'i love grafitus\')
							return parse_bbc($row[\'name\']) . \' <br /><em>grafitus said: "Me too you... :)))"</em>\';

						return parse_bbc($row[\'name\']);
					'),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'project' => array(      //PROJEKT - dodal Jaka - delo v teku
                'header' => array(
                    'value' => $txt['project_name'],      //dodano v modification.xml
                ),
                'data' => array(                               //tu je treba ugotoviti, kako dobiti ime projekta - kako dobiti ime iz tabele projektov preko povezave z ID-ji
                    'function' => create_function('$row,$scripturl', '
                        $label = "<a href=\"$scripturl?sa=view_task&taskid=$row[\'id\']\">$row[\'name\']</a>";
						return parse_bbc($row[$label]);
					'),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'deadline' => array(      //ROK
                'header' => array(
                    'value' => $txt['delegator_deadline'],    //pojma nimam iz kje dobi to - morda je zgolj nek znak
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$row[\'deadline\'] = strtotime($row[\'deadline\']);
						return timeformat($row[\'deadline\'], \'%d %B %Y, %A\');
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'deadline',
                    'reverse' => 'deadline DESC',
                ),
            ),

            'priority' => array(      //POMEMBNOST
                'header' => array(
                    'value' => $txt['delegator_priority'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $settings, $txt;

						if ($row[\'priority\'] == 0)
							$image = \'warning_watch\';
						elseif ($row[\'priority\'] == 1)
							$image = \'warn\';
						elseif ($row[\'priority\'] == 2)
							$image = \'warning_mute\';

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" alt="Priority: ' . $txt["delegator_priority" . $row["priority"]] . '" /> \' . $txt[\'to_do_priority\'] . $row[\'priority\']];
                        '),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/\'. ($row[\'state\'] ? \'package_old\' : \'package_installed\'). \'.gif" alt="" /></a><a href="\'. $scripturl. \'?action=delegator;sa=delete;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="" /></a>\';
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );

    require_once($sourcedir . '/Subs-List.php');

    createList($list_options);
}

function add()   //ni se prava funkcija za dodajanje - samo za gumb?
{
    global $smcFunc, $scripturl, $context, $txt;

    //isAllowedTo('add_new_todo');      //spet izkljuceni permissioni

    $context['sub_template'] = 'add';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=add', //spet add
        'name' => $txt['delegator_add']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_add
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_add dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_add label
		{
			font-weight: bold;
		}
		dl.delegator_add dd
		{
			float: left;
			width: 69%;
			margin: 0.5em 0 0 0;
		}
		#confirm_buttons
		{
			text-align: center;
			padding: 1em 0;
		}
	</style>';
}

//Prava funkcija za dodajanje taska:
// Kaj se vpise v bazo, ko se ustvari task?
//id, id_proj, id_author, name, description, creation_date, deadline, priority, state
// MANJKA: description
function add_task()
{
    global $smcFunc, $context;

    //isAllowedTo('add_new_todo');

    checkSession();

    $id_author = $context['user']['id'];

    $name = strtr($smcFunc['htmlspecialchars']($_POST['name']), array("\r" => '', "\n" => '', "\t" => ''));
    $description = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));
    $deadline = $smcFunc['htmlspecialchars']($_POST['duet3'] . '-' . $_POST['duet1'] . '-' . $_POST['duet2']);
    $state = 0;

    if ($smcFunc['htmltrim']($_POST['name']) === '' || $smcFunc['htmltrim']($_POST['duet2']) === '')
        fatal_lang_error('to_do_empty_fields', false);

    $smcFunc['db_insert']('', '{db_prefix}tasks',
    array(
        'id_proj' => 'int', 'id_author' => 'int', 'name' => 'string', 'description' => 'string', 'deadline' => 'date', 'priority' => 'int', 'state' => 'int',
    ),
    array(
        $_POST['id_proj'], $id_author, $name, $description, $deadline, $_POST['priority'], $state,
    ),
    array('id')
    );

    redirectexit('action=delegator'); //ali moram tole spremeniti???
    // Pomoje ne...
}

// analogija funkciji add()
function proj()
{
    global $smcFunc, $scripturl, $context, $txt;

    //isAllowedTo('add_new_todo');

    $context['sub_template'] = 'proj';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=proj',
        'name' => $txt['delegator_proj']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_proj
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_proj dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_proj label
		{
			font-weight: bold;
		}
		dl.delegator_proj dd
		{
			float: left;
			width: 69%;
			margin: 0.5em 0 0 0;
		}
		#confirm_buttons
		{
			text-align: center;
			padding: 1em 0;
		}
	</style>';
}


// add_project: id, id_coord, name, description, start, end
function add_proj() // mrbit bi moral imeti se eno funkcijo, v stilu add pri taskih
{
    global $smcFunc, $context;

    //isAllowedTo('add_new_todo');

    checkSession();

    $id_coord = $context['user']['id'];

    $name = strtr($smcFunc['htmlspecialchars']($_POST['name']), array("\r" => '', "\n" => '', "\t" => ''));
    $description = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));
    $start = $smcFunc['htmlspecialchars']($_POST['duet3'] . '-' . $_POST['duet1'] . '-' . $_POST['duet2']);
    $end = $smcFunc['htmlspecialchars']($_POST['dend3'] . '-' . $_POST['dend1'] . '-' . $_POST['dend2']);

    if ($smcFunc['htmltrim']($_POST['name']) === '' || $smcFunc['htmltrim']($_POST['duet2']) === '')
        fatal_lang_error('delegator_empty_fields', false);
// description manjka
    $smcFunc['db_insert']('', '{db_prefix}projects',
    array(
        'id_coord' => 'int', 'name' => 'string', 'description' => 'string', 'start' => 'date', 'end' => 'date',
    ),
    array(
        $id_coord, $name, $description, $start, $end,
    ),
    array('id')
    );

    redirectexit('action=delegator'); // redirect exit - logicno
}




// To bomo smotrno preuredili!!!
function didChange()
{
    global $smcFunc;

    checkSession('get');

    $request = $smcFunc['db_query']('', '
		SELECT id_todo, is_did
		FROM {db_prefix}to_dos
		WHERE id_todo = {int:id_todo}
		LIMIT 1',
    array(
        'id_todo' => (int) $_GET['id'],
    )
    );
    list ($id_todo, $is_did) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if (!empty($id_todo))
	{
            $smcFunc['db_query']('', '
			UPDATE {db_prefix}to_dos
			SET is_did = {int:is_did}
			WHERE id_todo = {int:id_todo}',
            array(
                'id_todo' => $id_todo,
                'is_did' => $is_did ? 0 : 1,
            )
            );
	}

    redirectexit('action=delegator');
}

function delete()
{
    global $smcFunc, $context;

    checkSession('get');

    $todo_id = (int) $_GET['id'];
    $id_member = $context['user']['id'];

    $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}to_dos
		WHERE id_todo = {int:todo_id}
			AND id_member = {int:id_member}',
    array(
        'todo_id' => $todo_id,
        'id_member' => $id_member,
    )
    );

    redirectexit('action=delegator');
}

is_not_guest();

?>