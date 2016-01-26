<?php
/**
 * Helper functions for Delegator (SMF taskmanager mod)
 *
 * Here are most of db querries.
 */

// First of all, we make sure we are accessing the source file via SMF so that people can not directly access the file. Sledeci vrstici sta dodani, da kdo ne sheka (SMF uporablja v vseh fajlih, mod ni uporabljal).
if (!defined('SMF'))
    die('Hack Attempt...');

function getPriorityIcon($row) {
    global $settings, $txt;

    if ($row['priority'] == 0) {
        $image = 'warning_watch';
    } elseif ($row['priority'] == 1) {
        $image = 'warn';
    } elseif ($row['priority'] == 2) {
        $image = 'warning_mute';
    }

    return '<img src="' . $settings['images_url'] . '/'. $image
        . '.gif" title="Priority: ' . $txt['delegator_priority_' . $row['priority']]
        . '" alt="Priority: ' . $txt['delegator_priority_' . $row['priority']] . '" /> ';
}

function getPriorities($row, $txt) {
    $priorities = "";
    for ($i = 0; $i < 3; $i++) {
        $checked = ($i == $row["priority"]) ? "checked" : "";
        $priorities .= '
            <li>
                <input type="radio" name="priority" id="priority_' . $i . '" value="' . $i . '" class="input_radio" class="input_radio"' . $checked . '/>
                ' . $txt['delegator_priority_' . $i] . '
            </li>
        ';
    }
    return $priorities;
}

// Get status (ce gledas memberja, podas true in gre od 1 naprej)
// @todo Preveri ali je int ali ni!!!

/**
 * Get status function
 *
 * Helps us find out which state to choose when making
 * db querries.
 */
function getStatus($isMember = false) {

    if( isset($_GET['status']) ){
        $status = $_GET['status'];
    }
    else{
        $status = ($isMember) ? 1 : 0;
    }
    return $status;
}


/**
 * Returns name of the member.
 *
 * Is used in template_my_tasks, template_view_worker
 */
function member_name($id_member){
    $request = $smcFunc['db_query']('', '
        SELECT T1.real_name AS name FROM {db_prefix}members T1
        WHERE T1.id_member={int:id_member}',
        array('id_member' => $id_member)
    );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    return $row['name'];
}

/**
 * Checks if specific worker on particular task.
 *
 * Actually it works only for active user since
 * You get the user from inside the function.
 * It would be nice to make a call with two params
 * member_id and task_id.
 */
function isMemberWorker($id_task) {
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

function isMemberCoordinator($id_proj) {
    // Pogledamo, id memberja in ga primerjamo s taski v tabeli
    // Funkcija je tudi pogoj za to, da se v templejtu view_task pojavi gumb End_task
    global $context, $smcFunc, $scripturl;
    $id_member = $context['user']['id'];

    $request = $smcFunc['db_query']('', '
        SELECT id_coord  FROM {db_prefix}projects
        WHERE id = {int:id_proj}',
        array('id_proj' => $id_proj)
    );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    $ret = ($row['id_coord'] == $id_member) ? TRUE : FALSE;
    return $ret;
}

/**
 * List of workers that are working on task.
 *
 * @return: Array of workers(id => name)
 */
function workers_on_task($id_task){
    global $smcFunc;

    $request = $smcFunc['db_query']('', '
        SELECT T1.id_member, T2.real_name
        FROM {db_prefix}workers T1
        LEFT JOIN {db_prefix}members T2 ON T1.id_member = T2.id_member
        WHERE T1.id_task = {int:id_task}',
        array('id_task' => $id_task)
    );
    
    $delegates = array();
    while ($member = $smcFunc['db_fetch_assoc']($request)){
        $delegates[$member["id_member"]] = $member["real_name"];
    }

    $smcFunc['db_free_result']($request);
    return $delegates;
}


/**
 * Counts number of workers working on a specific task.
 */
function numberOfWorkers($id_task) {

    global $context, $smcFunc;

    $request = $smcFunc['db_query']('', '
        SELECT COUNT(id) AS numworkers FROM {db_prefix}workers
        WHERE id_task = {int:id_task}', array('id_task' => $id_task));
    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    return $row['numworkers'];

}

/**
 * Writes Log about current action.
 *
 * Notation: When there is action on project id_task is less than zero (-1)
 */

function zapisiLog($id_proj, $id_task, $action) {
    global $smcFunc, $context;
    $id_member = $context['user']['id'];

    //checkSession(); // ali to rabimo???
    //najbrz ne, ker se vedno klice samo v funkcijah, ki so ze preverile session, al kaj... 3h je slo za to!!!

    if ($id_proj < 0) {
        $request = $smcFunc['db_query']('', '
            SELECT id_proj FROM {db_prefix}tasks
            WHERE id = {int:id_task}', array('id_task' => $id_task)
        );

        $row = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        $id_proj = $row['id_proj'];
    }

    $smcFunc['db_insert']('', '
        {db_prefix}delegator_log',
        array(
            'id_proj' => 'int',
             'id_task' => 'int',
             'action' => 'string',
             'id_member' => 'int',
             'action_date' => 'string'
        ),
        array($id_proj, $id_task, $action, $id_member, date('Y-m-d H-i-s')),
        array()
    );
    //  array( $id_proj, $id_task, $action, $id_member, date('Y-m-d') ),
}

// Prva Funkcija dobi argument status in optional id_member, Vrne taske!
// Druga Funkcija dobi iste argumente in vrne stevilo taskov...
// Fora je, da se bo dalo rezultate obeh funkcij združit/seštet...

/**
 * Returns array of tasks given the paramaters.
 *
 * Tasks of specific state, worker or project
 * @var: int(status), string(what), (int)value, sort=deadline, start=0, items_per_page=30
 * $what = [None, Project, Worker]
 * @return: list of tasks (as $row)
 */
function ret_tasks($status, $what, $value, $sort, $start, $items_per_page){

    global $smcFunc;
    if ($what == "None") {
        $query = '
    		SELECT T1.id AS id_task, T1.name AS task_name, T2.name AS project_name,
                T1.deadline AS deadline, T1.priority AS priority, T1.state AS state,
                T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author,
                T1.creation_date, T1.end_date AS end_date
    		FROM {db_prefix}tasks T1
    		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
    		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
    		WHERE T1.state = {int:state}
    		ORDER BY {raw:sort}
    		LIMIT {int:start}, {int:per_page}';

        $values = array (
            'state'     => $status,
            'sort'      => $sort,
            'start'     => $start,
            'per_page'  => $items_per_page
        );
    } elseif ($what == "Project") {
        $query = '
            SELECT T1.id AS id_task, T1.name AS task_name, T2.name AS project_name,
                T1.deadline AS deadline, T1.priority AS priority, T1.state AS state,
                T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author,
                T1.creation_date, T1.end_date AS end_date
		    FROM {db_prefix}tasks T1
		    LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		    LEFT JOIN {db_prefix}members T3 on T1.id_author = T3.id_member
		    WHERE T1.state = {int:state} AND T1.id_proj = {int:id_proj}
		    ORDER BY {raw:sort}
		    LIMIT {int:start}, {int:per_page}';
        $values = array(
           'state'    => $status,
           'id_proj'  => $value,
		   'sort'     => $sort,
           'start'    => $start,
           'per_page' => $items_per_page
        );
    } elseif ($what == "Worker") {
        $query = '
            SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name,
                T2.deadline AS deadline, T2.priority AS priority, T2.state AS state,
                T4.real_name AS author, T2.id_proj AS id_proj, T2.id_author AS id_author,
                T2.creation_date, T2.end_date AS end_date
            FROM {db_prefix}workers T1
            LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
            LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
            LEFT JOIN {db_prefix}members T4 ON T2.id_author = T4.id_member
            WHERE T1.id_member={int:id_member} AND T1.status = {int:status}
            ORDER BY {raw:sort}
    		LIMIT {int:start}, {int:per_page}';
        $values = array(
            'id_member' => $value,
            'status'    => $status,
            'sort'      => $sort,
            'start'     => $start,
            'per_page'  => $items_per_page,
        );
    } else {
        return "Wrong input";
    }
    $request = $smcFunc['db_query']('', $query , $values);
    $tasks = array();
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $tasks[] = $row;
    }
    $smcFunc['db_free_result']($request);

    return $tasks;  //funkcija vrne taske
}

/**
 * Returns number of database fields (tasks, projects and workers).
 *
 * @var: int(status), string(what), (int)value
 * $what = [None, Project, Worker]
 * @return: number of tasks/workers/projects (as $row)
 */
function ret_num($status, $what, $value){
    global $smcFunc;

    $query = 'SELECT COUNT(id) FROM {db_prefix}';

    if ($what == "None") {
        $query = $query . 'tasks
            WHERE state={int:state}';
        $values = array('state' => $status);

    } elseif ($what == "Project") {
        $query = $query . 'tasks
            WHERE state = {int:state} AND id_proj = {int:id_proj}';
        $values = array(
            'state'   => $status,
            'id_proj' => $value
        );
    } elseif ($what == "Worker") {
        $query = $query . 'workers
            WHERE id_member={int:id_member} AND status = {int:status}';
        $values = array(
            'id_member' =>  $value,
            'status'    => $status
        );
    }

    $request = $smcFunc['db_query']('', $query , $values);
    list($total_tasks) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    return $total_tasks;
}

/**
 * Creates string of html for input form.
 *
 * Used in add_task, add_project and all of the edit forms.
 * @param name txt type value size1 size2 class
 * @txt is string that is in $txt global variable
 * type: textarea, input-text
 * in case of textarea: size1=rows size2=cols
 * in case of input text: size1=size size2=maxlength
 */
function dl_form($name, $txt, $type, $value, $class, $size1=0, $size2=0){
    $output = '<dt> <label for="'.$name.'">'.$txt.'</dt><dd>';
    switch($type){
    case "textarea":
        $output .= '<textarea name="'.$name.'" rows="'.$size1.'" cols="'.$size2.'">'.
                 $value.'</textarea></dd>';
    case "input-text":
        $output .= '<input type="text" name="'.$name.'" size="'.$size1.'" class="'.$class.'"maxlength="'.$size2.'" value="'.$value.'" /></dd>'
                 }
    return $output;
}

/**
 * Creates string of html for view template
 *
 * Used in view templates.
 * @param name, txt, link, class
 * @todo to be finished ...

function dl_view(){

}
 */



// status bi lahko bil argument in glede na to vrnil deadline...
// @todo function show_task_list($finished=false) {
// @todo lahko bi se napisalo funkcijo, ki dobi argumente 'name', 'header', 'data'...

/**
 * Complete list of tasks compatible with SMF presentation.
 *
 * @return list of tasks.
 */
function show_task_list($status) {
    global $txt, $scripturl;

    if ($status === "unfinished") {
        $status = 0;
    } elseif ($status === "finished") {
        $status = 2;
    }

    $columns = array(
        'name' => array(		// TASK
            'header' => array(
                'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
            ),
            'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                'function' => function($row) use ($scripturl) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=view_task;id_task='. $row['id_task'] .'">'.$row['task_name'].'</a>';
                }
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
                'function' => function($row) use ($scripturl) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=view_project;id_proj='. $row['id_proj'] .'">'.$row['project_name'].'</a>';
                }
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
                'function' => function($row) use ($scripturl) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=view_worker;id_member='. $row['id_author'] .'">'.$row['author'].'</a>';
                }
            ),
            'sort' =>  array(
                'default' => 'author',
                'reverse' => 'author DESC',
            ),
        ),

        'deadline' => array(
            'header' => array(
                'value' => $txt['delegator_deadline'],
            ),
            'data' => array(
                'function' => function($row) use ($status)  {
                    $deadline = $row['deadline'];
                    if (date('Y-m-d') > $deadline and $status < 2) return "<span class=\"overdue relative-time\">$deadline</span>";
                    elseif ($status > 1){
                        if ($row['end_date'] > $deadline) return "<span class=\"overdue\">$deadline</span>";
                        else return $deadline;

                    }
                    else return "<span class=\"relative-time\">$deadline</span>";
                },
            ),
            //'style' => 'width: 20%; text-align: center;',

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
                // @todo Use of undefined constant getPriorityIcon - assumed 'getPriorityIcon'
                // @gismoe bi to znal resit
                'function' => getPriorityIcon,
                'style' => 'width: 10%; text-align: center;',
            ),
            'sort' =>  array(
                'default' => 'priority',
                'reverse' => 'priority DESC',
            ),
        ),
        'creation_date' => array(      //ROK - "%j" vrne ven vrednost zaporedne stevilke dneva v letu - EVO TUKI GIZMO!
            'header' => array(
                'value' => $txt['delegator_creation_date'],
            ),
            'data' => array(
                'function' => function($row) { return $row['creation_date']; },
                'style' => 'width: 20%; text-align: center;',
            ),
            'sort' =>  array(
                'default' => 'creation_date',
                'reverse' => 'creation_date DESC',
            ),
        ),
        'actions' => array(      //Zakljuci/Skenslaj (se koda od To-Do Lista)
            'header' => array(
                'value' => $txt['delegator_actions'],
            ),
            'data' => array(
                'function' => function($row) use ($status) {
                    global $context, $settings, $scripturl;
                    if ($status < 2 ){
                        // ali pa ce bi to totalno skrajsal...
                        if (isMemberCoordinator($row['id_proj'])===TRUE) {
                            return '
                                <a title="Super Edit task" href="'. $scripturl. '?action=delegator;sa=super_edit;id_task='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                                    <img src="'. $settings['images_url']. '/buttons/super_edit.gif" alt="Edit task" />
                                </a>
                                <a title="Delete task" href="'. $scripturl. '?action=delegator;sa=del_task;id_task='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                                    <img src="'. $settings['images_url']. '/icons/quick_remove.gif" alt="Delete task" />
                                </a>';
                        } else {
                            return '
                                <a title="Edit task" href="'. $scripturl. '?action=delegator;sa=edit_task;id_task='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                                    <img src="'. $settings['images_url']. '/buttons/im_reply_all.gif" alt="Edit task" />
                                </a>
                                <a title="Delete task" href="'. $scripturl. '?action=delegator;sa=del_task;id_task='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                                    <img src="'. $settings['images_url']. '/icons/quick_remove.gif" alt="Delete task" />
                                </a>';
                        }
                    } else {
                        return '
                            <a title="Super Edit task" href="'. $scripturl. '?action=delegator;sa=super_edit;id_task='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                                <img src="'. $settings['images_url']. '/buttons/super_edit.gif" alt="Edit task" />
                            </a>';
                    }
                },
                'style' => 'width: 10%; text-align: center;',
            ),
        ),
    );

    return $columns;
}


/**************************************************
 * Vnesi javascript za autocomplete               *
 *                                                *
 * input     - input box, za vpis memberja        *
 * container - prostor za rezultate               *
 * param     - parameter ki nosi seznam memberjev *
 *************************************************/
function generateMemberSuggest ($input, $container, $param) {
    global $context, $txt;
    return '
		<script type="text/javascript" src="Themes/default/scripts/suggest.js?fin20"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
			var oAddMemberSuggest = new smc_AutoSuggest({
				sSelf: \'oAddMemberSuggest\',
				sSessionId: \'' . $context['session_id'] . '\',
				sSessionVar: \'' . $context['session_var'] . '\',
				sSuggestId: \'' . $input . '\',
				sControlId: \'' . $input . '\',
				sSearchType: \'member\',
				bItemList: true,
				sPostName: \'' . $param . '\',
				sURLMask: \'action=profile;u=%item_id%\',
				sTextDeleteItem: \'' . $txt['autosuggest_delete_item'] . '\',
				sItemListContainerId: \'' . $container . '\',
				aListItems: []
			});
        // ]]></script>';
}

/**
 * Recieves table of states and counts the databes imports of given state.
 *
 * @var: array(states), string(what), (int)value
 * $what = [None, Project, Worker]
 * @return: list of tasks (as $row)
 * 
 * @todo case statement 
 */
function count_states($states, $what, $value){
    //dobi tabelo stanj in jo dopolni...
    // @todo Naslednji korak k temu, da iz Delegator.template odstranimo queryje
    global $smcFunc, $txt, $scripturl, $context;

    if ($what==="Worker") {
        // Workers don't have state 0
        $query = '
            SELECT COUNT(id) FROM {db_prefix}workers
            WHERE id_member={int:id_member} AND status = {int:status}';
        $values = function ($status) use ($value) {
            return array(
                'id_member' => $value,
                'status' => $status
            );
        };
    } elseif ($what==="None") {
        $query = '
            SELECT COUNT(id) FROM {db_prefix}tasks
            WHERE  state = {int:state}';
        $values = function ($status) {
            return array('state' => $status);
        };
    } elseif ($what==="Project") {
        $query = '
            SELECT COUNT(id) FROM {db_prefix}tasks
            WHERE state = {int:state} AND id_proj = {int:id_proj}';
        $values = function ($status) use ($value) {
            return array(
                'state' => $status,
                'id_proj' => $value
            );
        };
    }

    foreach($states as $status => $count){
        $request = $smcFunc['db_query']('', $query, $values($status));
        $row = $smcFunc['db_fetch_assoc']($request);
        $states[$status] = $row['COUNT(id)'];
        $smcFunc['db_free_result']($request);
    }

    return $states;
}

function delegator_send_mail(){

    global $smcFunc, $txt, $scripturl, $context;

	$request = $smcFunc['db_query']('', '
        SELECT T2.real_name AS member, T2.email_address AS email, T3.name AS project_name,
            T4.name AS task_name, T4.description AS description
        FROM {db_prefix}workers T1
        LEFT JOIN {db_prefix}members T2 ON T1.id_member = T2.id_member
        LEFT JOIN {db_prefix}tasks T4 ON T1.id_task = T4.id
        LEFT JOIN {db_prefix}projects T3 ON T4.id_proj = T3.id
        WHERE T1.id_task = {int:id_task}
        ', array(
            "id_task" => $id_task
        )
    );

    $workerji = array();
    $emaili = array();
    $projekt = "";
    $task = "";
    $opis = "";
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $workerji[] = $row["member"];
        $emaili[] = $row["email"];
        $projekt = $row["project_name"];
        $task = $row["task_name"];
        $opis = $row["description"];
    }
    $smcFunc['db_free_result']($request);

    $statusi = [
        2 => "uspešno",
        3 => "neuspešno",
        4 => "preklicano"
    ];
    $status = $statusi[$state];
    $subject = "Opravilo $task v projektu $projekt zaključeno ($status)";

    $body = "Pri projektu $projekt je bilo zaključeno opravilo $task, s stanjem \"$status\".\n";
    $body .= "Opis:\n$opis\nOpravilo so zaključili: ";
    $body .= implode(", ", $workerji) . ". Slava jim!\n";

    // @TODO test? nevem ce dela to sploh.
	sendmail($emaili, $subject, $body, null, null, false, 5);
    /*
    var_dump($emaili);
    var_dump($subject);
    var_dump($body);
    die();
    */


    // @TODO pošlji tudi koordinatorju (ne samo delavcem)


    // @TODO Dodajmo proper email template za notifikacijo ob zaključitvi taska
	//$emaildata = loadEmailTemplate('new_announcement', $replacements, $cur_language);
}

/**
 * Deletes from database.
 */
function db_del_task($id_task){

    global $smcFunc, $txt, $scripturl, $context;

    zapisiLog(-1, $id_task, 'del_task'); // Has to be before DELETE happens...

    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}tasks
        WHERE id = {int:id_task}',
        array(
            'id_task' => $id_task
        )
    );

    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}workers
        WHERE id_task = {int:id_task}',
        array(
            'id_task' => $id_task
        )
    );
}

/**
 * Returns project information as row.
 *
 * Creates a database querry.
 * IN: template_view_project
 */
function project_info($id_proj){

    global $smcFunc, $txt, $scripturl, $context;

    $request = $smcFunc['db_query']('', '
        SELECT T1.id AS id, T1.name AS proj_name, T1.id_coord AS id_coord,
            T1.description AS description, T1.start AS start, T1.end AS end,
            T2.real_name AS coord_name
		FROM {db_prefix}projects T1
		LEFT JOIN {db_prefix}members T2 on T1.id_coord = T2.id_member
		WHERE T1.id = {int:id_proj}', array('id_proj' => $id_proj)
    ); // pred array je manjkala vejica in je sel cel forum v kT1.state =0
    
    $row = $smcFunc['db_fetch_assoc']($request);

    $smcFunc['db_free_result']($request);

    return $row;
}

/**
 * Returns list of projects
 *
 * IN: template_edit_task, template_add_task, template_super_edit
 */

function list_projects(){
    global $smcFunc, $txt, $scripturl, $context;

    $request_p = $smcFunc['db_query']('', '
        SELECT id, name
        FROM  {db_prefix}projects'
    );
    
    $projects = array();
    
    while ($row_p = $smcFunc['db_fetch_assoc']($request_p)) {
        $projects[$row_p["id"]] = $row_p["name"];
    }
    $smcFunc['db_free_result']($request_p);
    
    return $projects;
}

/**
 * Information about task.
 *
 * Workers/delegates are missing.
 * IN: template_edit_task,
 * @return task info as (array)row.
 */
function task_info($id_task){
    
    global $smcFunc, $txt, $scripturl, $context;

    $request = $smcFunc['db_query']('', '
        SELECT T1.id, T1.name AS task_name,  T1.deadline,
            T1.description, T1.priority, T1.id_proj, T1.id_author, T1.state, T1.start_date,
            T1.end_date, T1.end_comment, T3.real_name AS author, T1.creation_date,
            T2.name AS project_name
		FROM {db_prefix}tasks T1
		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
        WHERE T1.id = {int:id_task}',
        array('id_task' => $id_task)
    );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    return $row;
}

