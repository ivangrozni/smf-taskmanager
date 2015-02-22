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
    global $context, $txt, $scripturl, $settings; // potrebne variable
                                       // $context - ne vem, se za kaj se uporablja
                                       // $txt - notri so vsa prikazana besedila (zaradi prevodov)
                                       // $scripturl - za razlicne URL-je brskalnika, da gre na pravo stran?

    //isAllowedTo('view_todo');        // za zdaj smo izkljucili permissione

    loadTemplate('Delegator');         // nalozi template

    $context['page_title'] = $txt['delegator'];   //poberes page title iz $txt['delegator']

    $subActions = array(                      //definira se vse funkcije v sklopu delegatorja
        'delegator' => 'delegator_main',      //tukaj bo pregled nad projekti in nedokoncanimi zadolzitvami
	'add' => 'add',                       // nalozi view za add task... al kaj
        'proj' => 'proj',                     // template za vnos projekta
        'add_proj' => 'add_proj',             // funkcija ki vnese projekt
        'add_task' => 'add_task',             // funkcija, ki vnese task

        'end_task' => 'end_task',             // MANJKA zakljucek zadolzitve
        'et' => 'et',                         // nalaganje edita - view
        'edit_task' => 'edit_task',           // editanje taska - funkcija, ki update-a bazo
        'del_task' => 'del_task',
        'claim_task' => 'claim_task',         //vzemi odgovornost v svoje roke!
        'unclaim_task' => 'unclaim_task',     //ali pa si premisli
        'edit_proj' => 'edit_proj',           //MANJKA in BO SE MANJKALO editanje projekta
        'view_proj' => 'view_proj',           // podrobnosti projekta
        'vt' => 'vt',                         // nalozi view za ogled zadolzitve
        'view_worker' => 'view_worker',       // prikaze naloge enega workerja
        'my_tasks' => 'my_tasks',             // moje naloge
        'view_projects' => 'view_projects',   // seznam vseh projektov

            // Kasneje bomo dodali se razlicne view-je - prikaz casovnice...
            // Spodnji komentarji so stara To-Do list mod koda
    );

	// Delegator v celoti je kot en modul. Razni viewi so "subactioni". Defaulten subaction je delegator. Ce
	// je izbran action ($_REQUEST['sa']) neveljaven ali ni specificiran, se zloada defaulten subaction, to je "delegator"
    if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
        $sub_action = 'delegator';
    else
        $sub_action = $_REQUEST['sa'];

    //Dodaj delegator na navigacijo v zgornjem delu strani
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator',
        'name' => $txt['delegator']
    );

    // CSS, javascript!
    $context['html_headers'] .= '
        <link rel="stylesheet" type="text/css" href="Themes/default/css/pikaday.css" />
        <script src="Themes/default/scripts/moment.min.js" type="text/javascript"></script>
        <script src="Themes/default/scripts/jquery-1.9.0.min.js" type="text/javascript"></script>
        <script src="Themes/default/scripts/pikaday.js" type="text/javascript"></script>
        <script src="Themes/default/scripts/pikaday.jquery.js" type="text/javascript"></script>
        <script src="Themes/default/scripts/delegator.js" type="text/javascript"></script>
    ';
    $subActions[$sub_action]();

// Sixth, begin doing all the stuff that we want this action to display
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
            // FUNKCIJE
/*
query posodobljen - zdaj sta združeni tabela taskov in projektov
nadalje moramo query urediti tako, da bo še dodana tabela memberjov
*/
            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT T1.id AS id, T1.name AS task_name, T2.name AS project_name, T1.deadline AS deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author
					FROM {db_prefix}tasks T1
					LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
					LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
					WHERE T1.state =0
					OR T1.state =1
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
                'id_member' => $context['user']['id'],
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}tasks T1
					LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
					LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
					WHERE T1.state =0
					OR T1.state =1\',
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
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=vt;task_id=\'. $row[\'id\'] .\'">\'.$row[\'task_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'task_name',
                    'reverse' => 'task_name DESC',
                ),
            ),

            'project' => array(      //PROJEKT - dela!
                'header' => array(
                    'value' => $txt['delegator_project_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id_proj\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
//'return parse_bbc($row[\'project_name\']);

					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

	    'author' => array(      //AVTOR - dela!
                'header' => array(
                    'value' => $txt['delegator_author'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
                                                return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_author\'] .\'">\'.$row[\'author\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne številke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_deadline'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$deadline = $row[\'deadline\'];
                        return "<span class=\"relative-time\">$deadline</span>";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'deadline',
                    'reverse' => 'deadline DESC',
                ),
            ),
            // spet undefined index priority je v errolog-u
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

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" title="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" alt="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" /> \' . $txt[\'to_do_priority\' . $row[\'priority\']];
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            // undefined index: task_actions
            // g1zmo - tole je ze delalo - ali sva pobrkala z verzijami???
            // mislim da ne dela, ker v tabeli tasks ni stolpca actions... al kaj
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a title="Edit task" href="\'. $scripturl. \'?action=delegator;sa=et;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/buttons/im_reply_all.gif" alt="Edit task" /></a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=del_task;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';
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
    $deadline = $smcFunc['htmlspecialchars']($_POST['duedate']);
    $state = 0;

    if ($smcFunc['htmltrim']($_POST['name']) === '')
        fatal_lang_error('to_do_empty_fields', false);

    $smcFunc['db_insert']('', '{db_prefix}tasks',
        array('id_proj' => 'int', 'id_author' => 'int', 'name' => 'string', 'description' => 'string', 'deadline' => 'date', 'priority' => 'int', 'state' => 'int', 'creation_date' => 'string'),
        array( $_POST['id_proj'], $id_author, $name, $description, $deadline, $_POST['priority'], $state, date("Y-m-d")),
        array('id')
    ); 

    $request = $smcFunc['db_query']('', '
    SELECT T1.id AS id_task FROM {db_prefix}tasks T1
    ORDER BY T1.id DESC
    LIMIT 1', array() );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    redirectexit('action=delegator;sa=vt&task_id='.$row['id_task'].'');     

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

    // Redirect vrze na isti projekt...
    // Fora je, da moram pogledat, kateri id je dobil...
    $request = $smcFunc['db_query']('', '
    SELECT T1.id AS id_proj FROM {db_prefix}projects T1
    ORDER BY T1.id DESC
    LIMIT 1', array() );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    redirectexit('action=delegator;sa=view_proj;id_proj='.$row['id_proj']); // redirect exit - logicno
}

##################################################################
########################## view_task #############################
##################################################################

// analogija funkciji add()
function vt()
{
    global $smcFunc, $scripturl, $context, $txt;

    $context['sub_template'] = 'vt';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=vt',
        'name' => $txt['delegator_view_task']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_vt
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_vt dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_vt label
		{
			font-weight: bold;
		}
		dl.delegator_vt dd
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

##################################################################
##################################################################
##################################################################

##################################################################
################### view project #################################
##################################################################

function view_proj()
{
    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'view_proj';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_proj',
        'name' => $txt['delegator_view_proj']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_view_proj
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_view_proj dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_view_proj label
		{
			font-weight: bold;
		}
		dl.delegator_view_proj dd
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

/////////////////////////////////////////////////////////////////////////////////////
//////////////////// prikaz taskov v projektu  //////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

    $id_proj = $_GET['id_proj'];

// tole lahko uporabimo za prikaz taskov, ampak si ne upam...
// matra me $id_proj, ker ne vem, kako naj ga dobim sem notri...
    $list_options = array(
        //'id' => 'list_todos',                                //stara To-Do List koda
        'id' => 'list_tasks_of_proj',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator',       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT T1.id AS id, T1.name AS task_name, T2.name AS project_name, T1.deadline AS deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author
					FROM {db_prefix}tasks T1
					LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
					LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
					WHERE (T1.state = 0 OR T1.state =1) AND T1.id_proj = '.$id_proj.'
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
                'id_member' => $context['user']['id'],
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
			SELECT COUNT(*)
			FROM {db_prefix}tasks T1
			LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
			LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
			WHERE T1.state =0
			OR T1.state =1\', array()
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
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=vt;task_id=\'. $row[\'id\'] .\'">\'.$row[\'task_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'task_name',
                    'reverse' => 'task_name DESC',
                ),
            ),

            'project' => array(      //PROJEKT - dela!
                'header' => array(
                    'value' => $txt['delegator_project_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id_proj\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
//'return parse_bbc($row[\'project_name\']);

					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

	    'author' => array(      //AVTOR - dela!
                'header' => array(
                    'value' => $txt['delegator_author'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
                                                return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_author\'] .\'">\'.$row[\'author\'].\'</a>\'; '
                    //return parse_bbc($row[\'author\']);
					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne številke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_deadline'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$deadline = $row[\'deadline\'];
                        return "<span class=\"relative-time\">$deadline</span>";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'deadline',
                    'reverse' => 'deadline DESC',
                ),
            ),
            // spet undefined index priority je v errolog-u
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

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" title="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" alt="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" /> \' . $txt[\'to_do_priority\' . $row[\'priority\']];
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            // undefined index: task_actions
            // g1zmo - tole je ze delalo - ali sva pobrkala z verzijami???
            // mislim da ne dela, ker v tabeli tasks ni stolpca actions... al kaj
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'">wat</a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=delete;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';
					'),

                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );

    require_once($sourcedir . '/Subs-List.php');
    createList($list_options);

}

##################################################################
##################################################################
##################################################################

# prikaze vse projekte in to v lepo urejeni tabeli...

function view_projects()
{
    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'view_projects';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_projects',
        'name' => $txt['delegator_view_projects']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_view_projects
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_view_projects dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_view_projects label
		{
			font-weight: bold;
		}
		dl.delegator_view_projects dd
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

    $list_options = array(
        'id' => 'list_of_projects',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator',       //prvi del URL-ja
        'default_sort_col' => 'end',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT T1.id AS id, T1.name AS project_name, T1.start AS start, T1.end AS end, T1.id_coord AS id_coord  T2.real_name AS coordinator
					FROM {db_prefix}projects T1
					LEFT JOIN {db_prefix}members T2 ON T1.id_coord = T2.id

					ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}\',
					array(
						\'sort\' => $sort,
						\'start\' => $start,
						\'per_page\' => $items_per_page,
					)
				);
				$projects = array();
				while ($row = $smcFunc[\'db_fetch_assoc\']($request))
					$projects[] = $row;
				$smcFunc[\'db_free_result\']($request);

				return $projects;
                                '),
            'params' => array(
                'id_member' => $context['user']['id'], // tega ne rabim, ane...
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
			SELECT COUNT(*)
			FROM {db_prefix}projects T1\', array()
				);
				list($total_projects) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_projects;
			'),
        ),
        'no_items_label' => $txt['projects_empty'],
        'columns' => array(
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// PROJECT
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
                ),
            ),

            'coordinator' => array(      //KOORDINATOR
                'header' => array(
                    'value' => $txt['delegator_coordinator_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_coord\'] .\'">\'.$row[\'coordinator\'].\'</a>\'; '

					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

	    'start' => array(      
                'header' => array(
                    'value' => $txt['project_start'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
                                                return $row[\'start\'] ;'
					),
                ),
                'sort' =>  array(
                    'default' => 'start',
                    'reverse' => 'start DESC',
                ),
            ),

            'end' => array( 
                'header' => array(
                    'value' => $txt['project_end'],
                ),
                'data' => array(
                    'function' => create_function('$row', ' return $row[\'end\'] '),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'end',
                    'reverse' => 'end DESC',
                ),
            ),

            /*'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'">wat</a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=delete;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';
					'),

                    'style' => 'width: 10%; text-align: center;',
                ),
             ),*/
        ),
    );

    require_once($sourcedir . '/Subs-List.php');
    createList($list_of_projects);

}




##################################################################
##################################################################
##################################################################

##################################################################
#################### view member #################################
##################################################################

// oglej si delavca - lahko bi se prikazalo se kaj njegove statistike

function view_worker() 
{
// More pokazat zadolzitve, pri katerih si worker...

    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'view_worker';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_worker',
        'name' => $txt['delegator_view_worker']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_view_worker
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_view_worker dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_view_worker label
		{
			font-weight: bold;
		}
		dl.delegator_view_worker dd
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

/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

    $id_member = $_GET['id_member'];

// tole lahko uporabimo za prikaz taskov, ampak si ne upam...
// matra me $id_proj, ker ne vem, kako naj ga dobim sem notri...
    $list_options = array(
        //'id' => 'list_todos',                                //stara To-Do List koda
        'id' => 'list_tasks_of_worker',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator',       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                                        SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.state AS state, T4.real_name AS author, T2.id_proj AS id_proj
                                        FROM {db_prefix}workers T1
                                        LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
                                        LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
                                        LEFT JOIN {db_prefix}members T4 ON T2.id_author = T4.id_member
                                        WHERE T1.id_member={int:id_member}
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
                'id_member' => $context['user']['id'],
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}tasks T1
					LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
					LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
					WHERE T1.state = 0
					OR T1.state =1\',
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
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=vt;task_id=\'. $row[\'id_task\'] .\'">\'.$row[\'task_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'task_name',
                    'reverse' => 'task_name DESC',
                ),
            ),

            'project' => array(      //PROJEKT - dela!
                'header' => array(
                    'value' => $txt['delegator_project_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id_proj\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
//'return parse_bbc($row[\'project_name\']);

					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

	    'author' => array(      //AVTOR - dela!
                'header' => array(
                    'value' => $txt['delegator_author'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
						return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_author\'] .\'">\'.$row[\'author\'].\'</a>\'; '
//return parse_bbc($row[\'author\']);
					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne številke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_deadline'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$deadline = $row[\'deadline\'];
                        return "<span class=\"relative-time\">$deadline</span>";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'deadline',
                    'reverse' => 'deadline DESC',
                ),
            ),
            // spet undefined index priority je v errolog-u
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

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" title="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" alt="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" /> \' . $txt[\'to_do_priority\' . $row[\'priority\']];
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            // undefined index: task_actions
            // g1zmo - tole je ze delalo - ali sva pobrkala z verzijami???
            // mislim da ne dela, ker v tabeli tasks ni stolpca actions... al kaj
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'">wat</a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=del_task;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';
					'),

                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );


    //require_once($sourcedir . '/Subs-List.php'); // recimo, da ne vem kaj je to in da ne rabim

    require_once($sourcedir . '/Subs-List.php');
    createList($list_options);

}


function my_tasks() 
{


    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'my_tasks';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=my_tasks',
        'name' => $txt['delegator_my_tasks']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_my_tasks
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_my_tasks dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_my_tasks label
		{
			font-weight: bold;
		}
		dl.delegator_my_tasks dd
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

    $id_member = $context['user']['id'];

// tole lahko uporabimo za prikaz taskov, ampak si ne upam...
// matra me $id_proj, ker ne vem, kako naj ga dobim sem notri...
    $list_options = array(
        //'id' => 'list_todos',                                //stara To-Do List koda
        'id' => 'list_tasks_of_worker',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator',       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                                        SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.state AS state, T4.real_name AS author, T2.id_proj AS id_proj
                                        FROM {db_prefix}workers T1
                                        LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
                                        LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
                                        LEFT JOIN {db_prefix}members T4 ON T2.id_author = T4.id_member
                                        WHERE T1.id_member={int:id_member}
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
                'id_member' => $context['user']['id'],
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}tasks T1
					LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
					LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
					WHERE T1.state = 0
					OR T1.state = 1\',
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
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                    'function' => create_function('$row', 
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=vt;task_id=\'. $row[\'id_task\'] .\'">\'.$row[\'task_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'task_name',
                    'reverse' => 'task_name DESC',
                ),
            ),

            'project' => array(      //PROJEKT - dela!
                'header' => array(
                    'value' => $txt['delegator_project_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id_proj\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
//'return parse_bbc($row[\'project_name\']);

					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

	    'author' => array(      //AVTOR - dela!
                'header' => array(
                    'value' => $txt['delegator_author'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
						return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_author\'] .\'">\'.$row[\'author\'].\'</a>\'; '
//return parse_bbc($row[\'author\']);
					),
                ),
                'sort' =>  array(
                    'default' => 'name',
                    'reverse' => 'name DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne številke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_deadline'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$deadline = $row[\'deadline\'];
                        return "<span class=\"relative-time\">$deadline</span>";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'deadline',
                    'reverse' => 'deadline DESC',
                ),
            ),
            // spet undefined index priority je v errolog-u
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

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" title="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" alt="Priority: \' . $txt[\'priority_\' . $row[\'priority\']] . \'" /> \' . $txt[\'to_do_priority\' . $row[\'priority\']];
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            // undefined index: task_actions
            // g1zmo - tole je ze delalo - ali sva pobrkala z verzijami???
            // mislim da ne dela, ker v tabeli tasks ni stolpca actions... al kaj
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'">wat</a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=del_task;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';
					'),

                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );


    //require_once($sourcedir . '/Subs-List.php'); // recimo, da ne vem kaj je to in da ne rabim

    require_once($sourcedir . '/Subs-List.php');
    createList($list_options);

}




##################################################################
##################################################################
##################################################################

function et()
{
    // prebere podatke o tem tasku
    // odpre template z vpisanimi podatki
    // naredis UPDATE v bazi z novimi podatki -> funkcija edit_task

    global $smcFunc, $scripturl, $context, $txt;

    $context['sub_template'] = 'et';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=et',
        'name' => $txt['delegator_edit_task']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_et
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_et dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_et label
		{
			font-weight: bold;
		}
		dl.delegator_et dd
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

function edit_task()
{
    global $smcFunc, $context;

    //isAllowedTo('add_new_todo');

    checkSession();

    $id_author = $context['user']['id'];
    //var_dump($_GET); var_dump($_POST); die;
    $id_task = (int) $_POST['id_task'];
    $id_proj = $_POST['id_proj'];

    $name = strtr($smcFunc['htmlspecialchars']($_POST['name']), array("\r" => '', "\n" => '', "\t" => ''));
    $description = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));
    //$deadline = $smcFunc['htmlspecialchars']($_POST['duet3'] . '-' . $_POST['duet1'] . '-' . $_POST['duet2']);
    $deadline = strtr($smcFunc['htmlspecialchars']($_POST['deadline']), array("\r" => '', "\n" => '', "\t" => ''));
    //$state = 0; // !!!!!!!!! POPRAVI V TEMPLATE-u

    $smcFunc['db_query']('','
                  UPDATE {db_prefix}tasks T1
                  SET T1.name={string:name}, T1.description={string:description}, T1.deadline={string:deadline},  T1.id_proj={int:id_proj}
                  WHERE T1.id = {int:id_task}', array('name' => $name, 'description' => $description, 'deadline' => $deadline, 'id_proj' => $id_proj, 'id_task' => $id_task) );

    redirectexit('action=delegator;sa=vt&task_id='.$id_task.'');     
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
			UPDATE {db_prefix}ta
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

function del_task()
{
    global $smcFunc, $context;

    checkSession('get');

    $task_id = (int) $_GET['task_id'];
    //$id_member = $context['user']['id'];

    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}tasks
        WHERE id = {int:task_id}',
        array(
            'task_id' => $task_id
        )
    );

    redirectexit('action=delegator');
}

function claim_task()
{
    global $smcFunc, $context, $scripturl;

    checkSession('get');

    $task_id = (int) $_GET['task_id'];
    $member_id = (int) $context['user']['id'];

    $smcFunc['db_insert']('', '{db_prefix}workers',
        array(
            'id_member' => 'int', 'id_task' => 'int'
        ),
        array(
            $member_id, $task_id
        ),
        array('id')
    );

    $smcFunc['db_free_result']($request);

    //redirectexit($scripturl . '?action=delegator;sa=view_task;task_id=' . $task_id);
    redirectexit('action=delegator;sa=vt;task_id=' . $task_id);
}

function unclaim_task()
{
    global $smcFunc, $context, $scripturl;

    checkSession('get');

    $task_id = (int) $_GET['task_id'];
    $member_id = (int) $context['user']['id'];

    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}workers
        WHERE id_task = {int:task_id} AND id_member = {int:member_id}',
        array(
            'task_id' => $task_id,
            'member_id' => $member_id
        )
    );

    $smcFunc['db_free_result']($request);

    //redirectexit($scripturl . '?action=delegator;sa=view_task;task_id=' . $task_id);
    redirectexit('action=delegator;sa=vt&task_id=' . $task_id);
}

is_not_guest();

?>
