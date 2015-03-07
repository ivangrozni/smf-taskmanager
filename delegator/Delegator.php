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

/*******************
 * Helper funkcije *
 ******************/


function getPriorityIcon($row) {
    global $settings, $txt;

    if ($row['priority'] == 0)
        $image = 'warning_watch';
    elseif ($row['priority'] == 1)
        $image = 'warn';
    elseif ($row['priority'] == 2)
        $image = 'warning_mute';

    return '<img src="'. $settings['images_url']. '/'. $image. '.gif" title="Priority: ' . $txt['delegator_priority_' . $row['priority']] . '" alt="Priority: ' . $txt['delegator_priority_' . $row['priority']] . '" /> ';
}

// Lahko bi razsiril to funkcijo, da bi pregledala, ce je uporabnik koordinator - bi bila vec uporabna in povabljiva
function isMemberWorker($id_task){
    // Pogledamo, id memberja in ga primerjamo s taski v tabeli
    // Funkcija je tudi pogoj za to, da se v templejtu vt pojavi gumb End_task
    global $context, $smcFunc, $scripturl;
    
    $id_member = $context['user']['id'];

    $request = $smcFunc['db_query']('', '
        SELECT id_member AS id_worker FROM {db_prefix}workers
        WHERE id_task = {int:id_task}', array('id_task' => $id_task));

    while ($row = $smcFunc['db_fetch_assoc']($request) ) {
        if ($row['id_worker'] == $id_member) {
            $smcFunc['db_free_result']($request);
            return TRUE;
        }
    }
    $smcFunc['db_free_result']($request);
    return FALSE;
}

function zapisiLog($id_proj, $id_task, $action){
    // Input: action - selfexplanatory
    // Output: None
    // What function does: Writes action into log table
    // Notation: When there is action on project id_task is less than zero (-1)

    global $smcFunc, $context;

    $id_member = $context['user']['id'];

    //checkSession(); // ali to rabimo???
    //najbrz ne, ker se vedno klice samo v funkcijah, ki so ze preverile session, al kaj... 3h je slo za to!!!

    if ($id_proj == -1){
        $request = $smcFunc['db_query']('', '
            SELECT id_proj FROM {db_prefix}tasks
            WHERE id = {int:id_task}', array('id_task' => $id_task) );

        $row = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        $id_proj = $row['id_proj'];
    }
    
    $smcFunc['db_insert']('', '{db_prefix}delegator_log',
                          array('id_proj' => 'int', 'id_task' => 'int', 'action' => 'string', 'id_member' => 'int', 'action_date' => 'string' ),
                          array( $id_proj, $id_task, $action, $id_member, date('Y-m-d H-i-s') ),
                          array('id') ); 
    //  array( $id_proj, $id_task, $action, $id_member, date('Y-m-d') ),
}


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
        'en' => 'en',                         // nalozi template za end_task
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
        'view_log' => 'view_log',   // seznam vseh projektov

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
    $subActions[$sub_action]();

}

// Sixth, begin doing all the stuff that we want this action to display
// Store the results of this stuff in the $context array.
// This action's template(s) will display the contents of $context.

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
query posodobljen - zdaj sta zdruzeni tabela taskov in projektov
nadalje moramo query urediti tako, da bo se dodana tabela memberjov
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
					WHERE T1.state = 0 OR T1.state =1\',
					array(
					)
				);
				list($total_tasks) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_tasks;
			'),
        ),
        'no_items_label' => $txt['delegator_tasks_empty'],
        'columns' => array(
            // ocitno imamo header, data in sort znotraj posamezne vrednosti v tabeli
            // name, deadline, priority - so ze narejeni
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
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
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
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
                    'default' => 'author',
                    'reverse' => 'author DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
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
                    'function' => getPriorityIcon,
                    'style' => 'width: 10%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'priority',
                    'reverse' => 'priority DESC',
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
        'name' => $txt['delegator_task_add']
    );
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
    $id_proj = $_POST['id_proj'];

    $name = strtr($smcFunc['htmlspecialchars']($_POST['name']), array("\r" => '', "\n" => '', "\t" => ''));
    $description = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));
    $deadline = $smcFunc['htmlspecialchars']($_POST['duedate']);
    $state = 0;

    if ($smcFunc['htmltrim']($_POST['name']) === '')
        fatal_lang_error('delegator_empty_fields', false);

    $smcFunc['db_insert']('', '{db_prefix}tasks',
        array('id_proj' => 'int', 'id_author' => 'int', 'name' => 'string', 'description' => 'string', 'deadline' => 'date', 'priority' => 'int', 'state' => 'int', 'creation_date' => 'string'),
        array( $id_proj, $id_author, $name, $description, $deadline, $_POST['priority'], $state, date("Y-m-d")),
        array('id')
    ); 

    // Dodaj delegirane memberje
    /*
    foreach ($_POST["member_add"] as $member) {
        $smcFunc['db_insert']('', '{db_prefix}workers',
            array('id_member' => 'int', 'id_task' => 'int'),
            array((int) $member, $id_task)
        );
        }*/
    
    $request = $smcFunc['db_query']('', '
    SELECT T1.id AS id_task FROM {db_prefix}tasks T1
    ORDER BY T1.id DESC
    LIMIT 1', array() );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    zapisiLog($id_proj, $row['id_task'], 'add_task');
    
    redirectexit('action=delegator;sa=vt&task_id='.$row['id_task']);
}

// analogija funkciji add()
function proj()
{
    global $smcFunc, $scripturl, $context, $txt;

    //isAllowedTo('add_new_todo');

    $context['sub_template'] = 'proj';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=proj',
        'name' => $txt['delegator_project_name']
    );
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
    $start = $smcFunc['htmlspecialchars']($_POST['start']);
    $end = $smcFunc['htmlspecialchars']($_POST['end']);

    if ($smcFunc['htmltrim']($_POST['name']) === '' || $smcFunc['htmltrim']($_POST['end']) === '')
        fatal_lang_error('delegator_empty_fields', false);

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

    zapisiLog($row['id_proj'], -1, 'add_proj');
    
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
}

##################################################################
################### view project #################################
##################################################################

function view_proj()
{
    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'view_proj';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_proj',
        'name' => $txt['delegator_view_project']
    );

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
        'base_href' => $scripturl . '?action=delegator;sa=view_proj;id_proj=' . $id_proj,       //prvi del URL-ja
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
        'no_items_label' => $txt['delegator_tasks_empty'],
        'columns' => array(
            // ocitno imamo header, data in sort znotraj posamezne vrednosti v tabeli
            // name, deadline, priority - so ze narejeni
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
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
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
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
                    'default' => 'author',
                    'reverse' => 'author DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
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
                    'function' => getPriorityIcon,
                    'style' => 'width: 10%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'priority',
                    'reverse' => 'priority DESC',
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

##################################################################
##################################################################
##################################################################

# prikaze vse projekte in to v lepo urejeni tabeli...

function view_projects()
{

    global $context, $scripturl, $sourcedir, $smcFunc, $txt;   //globalne spremenljivke lahko kliceju funkcije iz zunaj kajne?
    $id_member = 1; // kr neki...

    $context['sub_template'] = 'view_projects';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_projects',
        'name' => $txt['delegator_view_projects']
    );

    $list_options = array(
        'id' => 'list_of_projects',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator;sa=view_projects',       //prvi del URL-ja
        'default_sort_col' => 'end',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE
/*
query posodobljen - zdaj sta zdruzeni tabela taskov in projektov
nadalje moramo query urediti tako, da bo se dodana tabela memberjov
*/
            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                    SELECT T1.id AS id, T1.name AS project_name, T1.start AS start, T1.end AS end, T1.id_coord AS id_coord, T2.real_name AS coordinator
                    FROM {db_prefix}projects T1                                                                LEFT JOIN {db_prefix}members T2 ON T1.id_coord = T2.id_member                              ORDER BY {raw:sort}                                                                        LIMIT {int:start}, {int:per_page}\',                                   					array(
						\'id_member\' => $id_member,
						\'sort\' => $sort,
						\'start\' => $start,
						\'per_page\' => $items_per_page,
					)
				);
				$projects = array();
				while ($row = $smcFunc[\'db_fetch_assoc\']($request))
					$projects[] = $row;
				$smcFunc[\'db_free_result\']($request);

				return $projects;   '),
            'params' => array(
                'id_member' => $context['user']['id'],
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}projects T1\',
					array(
					)
				);
				list($total_projects) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_projects;
			'),
        ),
        'no_items_label' => $txt['delegator_projects_empty'],
        'columns' => array(

            'project_name' => array(		// PROJECT
                'header' => array(
                    'value' => $txt['delegator_project_name'],  //Napisi v header "Name"... potegne iz index.english.php
                ),
                'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                    'function' => create_function('$row','
                               return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_proj;id_proj=\'. $row[\'id\'] .\'">\'.$row[\'project_name\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
                ),
            ),

	       'coordinator' => array(      
                'header' => array(
                    'value' => $txt['delegator_coordinator_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row', '
                             return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_member=\'. $row[\'id_coord\'] .\'">\'.$row[\'coordinator\'].\'</a>\'; '
					),
                ),
                'sort' =>  array(
                    'default' => 'coordinator',
                    'reverse' => 'coordinator DESC',
                ),
            ),

            'start' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_project_start'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$start = $row[\'start\'];
                        return "$start";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'start',
                    'reverse' => 'start DESC',
                ),
            ),

            'end' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_project_end'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$end = $row[\'end\'];
                        return "$end";
					'),
                    'style' => 'width: 20%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'end',
                    'reverse' => 'end DESC',
                ),
            ),


        ),
    );

    require_once($sourcedir . '/Subs-List.php');
    createList($list_options);
}

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

    $id_member = (int) $_GET['id_member']; // valda, tole vrne kr neki...
    print_r($id_member);
    // id_member ze ima pravo vradnost, a ocitno se query izvrsi za trenutnega uporabnika

// tole lahko uporabimo za prikaz taskov, ampak si ne upam...
// matra me $id_proj, ker ne vem, kako naj ga dobim sem notri...
    $list_options = array(
        'id' => 'list_tasks_of_worker',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator;sa=view_worker;id_member='.$id_member,       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                                        SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.state AS state, T4.real_name AS author, T2.id_proj AS id_proj, T2.id_author AS id_author
                                        FROM {db_prefix}workers T1
                                        LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
                                        LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
                                        LEFT JOIN {db_prefix}members T4 ON T2.id_author = T4.id_member
                                        WHERE T1.id_member={int:id_member}
                                        ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}\',
					array(
						\'id_member\' => '. $id_member .',
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
					FROM {db_prefix}workers 
                    WHERE id_member={int:id_member} \',
                                          array( \'id_member\' => '. $id_member.',
					)
				);
				list($total_tasks) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_tasks;
			'),
        ),
        'no_items_label' => $txt['delegator_tasks_empty'],
        'columns' => array(
            'name' => array(		// TASK
                'header' => array(
                    'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
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
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
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
                    'default' => 'author',
                    'reverse' => 'author DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
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
                    'function' => getPriorityIcon,
                    'style' => 'width: 10%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'priority',
                    'reverse' => 'priority DESC',
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

                        return \'<a title="Edit task" href="\'. $scripturl. \'?action=delegator;sa=et;task_id=\'. $row[\'id_task\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/buttons/im_reply_all.gif" alt="Edit task" /></a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=del_task;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';


					'),

                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );

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
        'base_href' => $scripturl . '?action=delegator;sa=my_tasks',       //prvi del URL-ja
        'default_sort_col' => 'deadline',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort, $id_member', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                                        SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.state AS state, T4.real_name AS author, T2.id_proj AS id_proj, T2.id_author AS id_author
                                        FROM {db_prefix}workers T1
                                        LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
                                        LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
                                        LEFT JOIN {db_prefix}members T4 ON T2.id_author = T4.id_member
                                        WHERE T1.id_member={int:id_member}
                                        ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}\',
					array(
						\'id_member\' => '.$id_member.',
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
					FROM {db_prefix}workers 
                    WHERE id_member={int:id_member} \',
                                          array( \'id_member\' => '. $id_member.',
					)
				);
				list($total_tasks) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_tasks;
			'),
        ),
        'no_items_label' => $txt['delegator_tasks_empty'],
        'columns' => array(
            // ocitno imamo header, data in sort znotraj posamezne vrednosti v tabeli
            // name, deadline, priority - so ze narejeni
            // avtor, worker(s), stanje - se manjkajo - ugotoviti, kako jih zajeti
	    // projekt zdaj dela
            // vsaka stvar v tabeli ima header, data, sort

            'task_name' => array(		// TASK
                'header' => array(
                    'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
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
                    ),
                ),
                'sort' =>  array(
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
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
                    'default' => 'author',
                    'reverse' => 'author DESC',
                ),
            ),

            'deadline' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
                'header' => array(
                    'value' => $txt['delegator_deadline'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						$deadline = $row[\'deadline\'];
                        return "<span class=\"relative-time\">$deadline</span>";
					'),
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
                    'function' => getPriorityIcon,
                    'style' => 'width: 10%; text-align: center;',
                ),
                'sort' =>  array(
                    'default' => 'priority',
                    'reverse' => 'priority DESC',
                ),
            ),
            'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
                'header' => array(
                    'value' => $txt['delegator_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

                return \'<a title="Edit task" href="\'. $scripturl. \'?action=delegator;sa=et;task_id=\'. $row[\'id_task\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/buttons/im_reply_all.gif" alt="Edit task" /></a><a title="Delete task" href="\'. $scripturl. \'?action=delegator;sa=del_task;task_id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="Delete task" /></a>\';

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
}

function edit_task()
{
    global $smcFunc, $context;

    //isAllowedTo('add_new_todo');

    checkSession();

    $id_author = $context['user']['id'];
    $id_task = (int) $_POST['id_task'];
    $id_proj = $_POST['id_proj'];

    $name = strtr($smcFunc['htmlspecialchars']($_POST['name']), array("\r" => '', "\n" => '', "\t" => ''));
    $description = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));
    $deadline = strtr($smcFunc['htmlspecialchars']($_POST['deadline']), array("\r" => '', "\n" => '', "\t" => ''));

    $priority = (int) $_POST['priority'];

    $smcFunc['db_query']('','
        UPDATE {db_prefix}tasks
        SET name={string:name}, description={string:description}, deadline={string:deadline}, id_proj={int:id_proj}, priority={int:priority}
        WHERE id = {int:id_task}',
        array('name' => $name, 'description' => $description, 'deadline' => $deadline, 'id_proj' => $id_proj, 'id_task' => $id_task, 'priority' => $priority)
    );

    // Dodaj delegirane memberje
    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}workers
            WHERE id_task={int:id_task}',
        array('id_task' => $id_task)
    );
    foreach ($_POST["member_add"] as $member) {
        $smcFunc['db_insert']('', '{db_prefix}workers',
            array('id_member' => 'int', 'id_task' => 'int'),
            array((int) $member, $id_task)
        );
    }

    zapisiLog($id_proj, $id_task, 'edit_task');
    
    redirectexit('action=delegator;sa=vt&task_id='.$id_task);
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

    zapisiLog(-1, $row['id_task'], 'del_task');
    
    redirectexit('action=delegator');
}

function claim_task()
{
    global $smcFunc, $context, $scripturl;

    checkSession('get');

    //print_r("do sem pride"); die;

    $task_id = (int) $_GET['task_id'];
    $member_id = (int) $context['user']['id'];

    //print_r("do sem pride"); die;
    $smcFunc['db_insert']('', '{db_prefix}workers',
        array('id_member' => 'int', 'id_task' => 'int'),
        array($member_id, $task_id),
        array('id') );
    //print_r("do sem pride"); die;

    zapisiLog(-1, $task_id, 'claim_task');
    //print_r("do sem pride"); die;
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

    //$smcFunc['db_free_result']($request);

    zapisiLog(-1, $task_id, 'unclaim_task');
    //redirectexit($scripturl . '?action=delegator;sa=view_task;task_id=' . $task_id);
    redirectexit('action=delegator;sa=vt&task_id=' . $task_id);
}


// Tukaj bomo nalozili template za koncanje taska
// Funkcija ustreza et, add, najbrz tudi proj
function en()
{
    
    // prebere podatke o tem tasku
    // odpre template z vpisanimi podatki
    // naredis UPDATE v bazi z novimi podatki -> funkcija edit_task

    global $smcFunc, $scripturl, $context, $txt;

    $context['sub_template'] = 'en';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=en',
        'name' => $txt['delegator_end_task']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.delegator_en
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.delegator_en dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.delegator_en label
		{
			font-weight: bold;
		}
		dl.delegator_en dd
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

function end_task()
{

    global $smcFunc, $context, $scripturl;

    checkSession(); // ali je to ok???

    $id_task = (int) $_POST['id_task'];

    //print_r($id_task);
    //print_r(isMemberWorker($id_task));die;
    
    if (isMemberWorker($id_task)){
        $end_comment = strtr($smcFunc['htmlspecialchars']($_POST['end_comment']), array("\r" => '', "\n" => '', "\t" => ''));
        
        $state = (int) $_POST['state'];

        $smcFunc['db_query']('','
                  UPDATE {db_prefix}tasks
                  SET end_comment={string:end_comment}, end_date={string:end_date}, state={int:state}
                  WHERE id = {int:id_task}',
                  array( 'end_comment' => $end_comment, 'end_date' => date("Y-m-d"), 'state' => $state , 'id_task' => $id_task ));

        zapisiLog(-1, $id_task, 'end_task');

        redirectexit('action=delegator;sa=my_tasks');
    }
    print_r("Member is not worker"); die;
    redirectexit('action=delegator;sa=vt;task_id='.$id_task);
    
}

function view_log()
{

    global $smcFunc, $scripturl, $context, $txt, $sourcedir;

    $context['sub_template'] = 'view_log';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator;sa=view_log',
        'name' => $txt['delegator_view_log']
    );

    $id_member = $context['user']['id'];

// tole lahko uporabimo za prikaz taskov, ampak si ne upam...
// matra me $id_proj, ker ne vem, kako naj ga dobim sem notri...
    $list_options = array(
        'id' => 'log',
        'items_per_page' => 30,                                //stevilo taskov na stran
        'base_href' => $scripturl . '?action=delegator;sa=view_log',       //prvi del URL-ja
        'default_sort_col' => 'action_date',                      //razvrsis taske po roku
        'get_items' => array(
            // FUNKCIJE

            'function' => create_function('$start, $items_per_page, $sort ', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
                                        SELECT T1.action_date, T1.id_member, T1.id_task, T1.id_proj, T1.action, T2.real_name AS member, T3.name AS project_name, T4.name AS task_name

                                        FROM {db_prefix}delegator_log T1
                                        LEFT JOIN {db_prefix}members T2 ON T1.id_member = T2.id_member
                                        LEFT JOIN {db_prefix}projects T3 ON T1.id_proj = T3.id
                                        LEFT JOIN {db_prefix}tasks T4 ON T1.id_task = T4.id
                                        ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}\',
					array(
						\'sort\' => $sort,
						\'start\' => $start,
						\'per_page\' => $items_per_page,
					)
				);

				$logs = array();
				while ($row = $smcFunc[\'db_fetch_assoc\']($request))
					$logs[] = $row;
				$smcFunc[\'db_free_result\']($request);

				return $logs;                                    //funkcija vrne taske
                                '),
            'params' => array(
                'id_member' => $context['user']['id'], //tudi ne rabimo
                 ),
        ),

        'get_count' => array(							//tudi tu je posodobljen query
            'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'

					SELECT COUNT(*)
					FROM {db_prefix}delegator_log \',
                    array()
				);
				list($total_logs) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_logs;
			'),
        ),
        'no_items_label' => $txt['delegator_log_empty'],
        'columns' => array(

            'action_date' => array(		
                'header' => array(
                    'value' => $txt['delegator_action_date'],  
                ),
                'data' => array( 
                    'function' => create_function('$row',
                    'return $row[\'action_date\'];'
					),
                ),
                'sort' =>  array(
                    'default' => 'action_date',
                    'reverse' => 'action_date DESC',
                ),
            ),

            'member' => array(      //Member
                'header' => array(
                    'value' => $txt['delegator_member_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_proj=\'. $row[\'id_member\'] .\'">\'.$row[\'member\'].\'</a>\'; '

					),
                ),
                'sort' =>  array(
                    'default' => 'member',
                    'reverse' => 'member DESC',
                ),
            ),
            
            'action' => array(      
                'header' => array(
                    'value' => $txt['delegator_action'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return $row[\'action\']; '
                    ),
                ),
                'sort' =>  array(
                    'default' => 'action',
                    'reverse' => 'action DESC',
                ),
            ),

            'project' => array(   
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
                    'default' => 'project_name',
                    'reverse' => 'project_name DESC',
                ),
            ),
            'task' => array( 
                'header' => array(
                    'value' => $txt['delegator_task_name'],      //dodano v modification.xml
                ),
                'data' => array(
                    'function' => create_function('$row',
                    'return \'<a href="\'. $scripturl .\'?action=delegator;sa=vt;task_id=\'. $row[\'id_task\'] .\'">\'.$row[\'task_name\'].\'</a>\'; '

					),
                ),
                'sort' =>  array(
                    'default' => 'task_name',
                    'reverse' => 'task_name DESC',
                ),
            
            ),
        'style' => 'width: 10%; text-align: center;',
        ),
    );


    require_once($sourcedir . '/Subs-List.php');
    createList($list_options);
}

is_not_guest();

?>
