<?php
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

function getPriorities($row, $txt)
{
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
function getStatus($isMember = false){

    if( isset($_GET['status']) ){
        $status = $_GET['status'];
    }
    else{
        $status = ($isMember) ? 1 : 0;
    }
    return $status;
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

function isMemberCoordinator($id_proj){
    // Pogledamo, id memberja in ga primerjamo s taski v tabeli
    // Funkcija je tudi pogoj za to, da se v templejtu vt pojavi gumb End_task
    global $context, $smcFunc, $scripturl;

    $id_member = $context['user']['id'];

    $request = $smcFunc['db_query']('', '
        SELECT id_coord  FROM {db_prefix}projects
        WHERE id = {int:id_proj}',
        array('id_proj' => $id_proj, ) );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    $ret = ($row['id_coord'] == $id_member ? TRUE : FALSE );
    return $ret;
}


function numberOfWorkers($id_task){
    // Presteje Stevilo Workerjev
    // Trenutno se uporabi zgolj v unclaim, saj smo add_task in edit_task resili bolj elegantno...
    global $context, $smcFunc;

    $request = $smcFunc['db_query']('', '
        SELECT COUNT(id) AS numworkers FROM {db_prefix}workers
        WHERE id_task = {int:id_task}', array('id_task' => $id_task));
    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    return $row['numworkers'];

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

    if ($id_proj < 0){
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
                          array() );
    //  array( $id_proj, $id_task, $action, $id_member, date('Y-m-d') ),
}

// Prva Funkcija dobi argument status in optional id_member, Vrne taske!
// Druga Funkcija dobi iste argumente in vrne stevilo taskov...
// Fora je, da se bo dalo rezultate obeh funkcij združit/seštet...


function ret_tasks($status, $what, $value, $sort, $start, $items_per_page){
    /*****************************************
    Input: $status (int), $what(string) $value(int)
    !!! Much attention needed: $what = [None, Project, Worker]

     **************************************** */

    global $smcFunc;
    if ($what == "None") {

        $query = '
		SELECT T1.id AS id_task, T1.name AS task_name, T2.name AS project_name, T1.deadline AS deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author, T1.creation_date, T1.end_date AS end_date
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
            'per_page'  => $items_per_page,);

    }

    elseif ($what == "Project") {
       $query = '
           SELECT T1.id AS id_task, T1.name AS task_name, T2.name AS project_name, T1.deadline AS deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author, T1.id_proj AS id_proj, T1.id_author AS id_author, T1.creation_date, T1.end_date AS end_date
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
						'per_page' => $items_per_page);
    }

    elseif ($what == "Worker"){
        $query = 'SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.state AS state, T4.real_name AS author, T2.id_proj AS id_proj, T2.id_author AS id_author, T2.creation_date, T2.end_date AS end_date
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
    }

    else {return "Wrng input";  }

    $request = $smcFunc['db_query']('', $query , $values );
    $tasks = array();
    while ($row = $smcFunc['db_fetch_assoc']($request))
        $tasks[] = $row;
    $smcFunc['db_free_result']($request);

    return $tasks;                                    //funkcija vrne taske

    }

function ret_num($status, $what, $value){
    global $smcFunc;

    $query = 'SELECT COUNT(id) FROM {db_prefix}';

    if ($what == "None") {
        $query = $query . 'tasks WHERE state={int:state}';
        $values = array ('state' => $status );

    }
    elseif ($what == "Project") {
        $query = $query . 'tasks WHERE state = {int:state} AND id_proj = {int:id_proj}';
        $values = array ('state'   => $status,
                         'id_proj' => $value,);
    }
    elseif ($what == "Worker"){
        $query = $query . 'workers
                    WHERE id_member={int:id_member} AND status = {int:status}';
        $values = array ('id_member' =>  $value,
                         'status' => $status,);
    }

    $request = $smcFunc['db_query']('', $query , $values );
    list($total_tasks) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    return $total_tasks;
}


// status bi lahko bil argument in glede na to vrnil deadline...
// @todo function show_task_list($finished=false) {
// @todo lahko bi se napisalo funkcijo, ki dobi argumente 'name', 'header', 'data'...
function show_task_list($status) {
    if ($status === "unfinished") $status = 0;
    elseif ($status === "finished") $status = 2;
    //else $status = $status;

    global $txt, $scripturl;
    //ena moznost je, da preverim stanje tu v funkciji, druga pa, da ga dam kot argument...

    $columns = array(
        'name' => array(		// TASK
            'header' => array(
                'value' => $txt['delegator_task_name'],  //Napisi v header "Name"... potegne iz index.english.php
            ),
            'data' => array( // zamenjal sem napisano funkcijo od grafitus-a...
                'function' => function($row) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=vt;task_id='. $row['id_task'] .'">'.$row['task_name'].'</a>';
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
                'function' => function($row) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=view_proj;id_proj='. $row['id_proj'] .'">'.$row['project_name'].'</a>';
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
                'function' => function($row) {
                    return '<a href="'. $scripturl .'?action=delegator;sa=view_worker;id_member='. $row['id_author'] .'">'.$row['author'].'</a>';
                }
            ),
            'sort' =>  array(
                'default' => 'author',
                'reverse' => 'author DESC',
            ),
        ),
        //////////////////////////////// Ali bi lahko tukaj if stavek uturil?
        /////////////////////////////// Mislim, da ne, ker sem v arrayu...
        /*'deadline' => array(
            'header' => array(
                'value' => function () use ($status, $txt){
                    if ($status < 2) return $txt['delegator_deadline'];
                    else return $txt['delegator_task_end_date'];
                },
            ),
            'data' => array(
                'function' => function($row) use ($status) {
                    if ($status < 2) {
                        $deadline = $row['deadline'];
                        if (date('Y-m-d') > $deadline) return "<font color=\"red\"><span class=\"relative-time\">$deadline</span></font>";
                        else return "<span class=\"relative-time\">$deadline</span>";
                    }
                    else {
                        return $row['end_date'];
                    }
                },
                ),
            //'style' => 'width: 20%; text-align: center;',

            'sort' =>  array(
                'default' => 'deadline',
                'reverse' => 'deadline DESC',

                //'default' => function () use ($status){
                //    if ($status < 2) return 'deadline';
                //    else return 'end_date';
                //},
                //'reverse' => function () use ($status){
                //    if ($status < 2) return 'deadline DESC';
                //    else return 'end_date DESC';
                //    }
            ),
        ),*/
        'deadline' => array(
            'header' => array(
                'value' => $txt['delegator_deadline'],
            ),
            'data' => array(
                'function' => function($row) use ($status)  {
                    $deadline = $row['deadline'];
                    if (date('Y-m-d') > $deadline and $status < 2) return "<font color=\"red\"><span class=\"relative-time\">$deadline</span></font>";
                    elseif ($status > 1){
                        if ($row['end_date'] > $deadline) return "<font color=\"red\">$deadline</font>";
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
        //////////////////////////////////////
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
                        if (isMemberCoordinator($row['id_proj'])===TRUE){
                            return '
                       <a title="Super Edit task" href="'. $scripturl. '?action=delegator;sa=se;task_id='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                            <img src="'. $settings['images_url']. '/buttons/super_edit.gif" alt="Edit task" />
                        </a>
                        <a title="Delete task" href="'. $scripturl. '?action=delegator;sa=del_task;task_id='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                            <img src="'. $settings['images_url']. '/icons/quick_remove.gif" alt="Delete task" />
                        </a>
';
                        }
                        else {
                    return '
                        <a title="Edit task" href="'. $scripturl. '?action=delegator;sa=et;task_id='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                            <img src="'. $settings['images_url']. '/buttons/im_reply_all.gif" alt="Edit task" />
                        </a>
                        <a title="Delete task" href="'. $scripturl. '?action=delegator;sa=del_task;task_id='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                            <img src="'. $settings['images_url']. '/icons/quick_remove.gif" alt="Delete task" />
                        </a>';}}
                    else {
                        return '
                        <a title="Super Edit task" href="'. $scripturl. '?action=delegator;sa=se;task_id='. $row['id_task']. ';' . $context['session_var'] . '=' . $context['session_id'] . '">
                            <img src="'. $settings['images_url']. '/buttons/super_edit.gif" alt="Edit task" />
                        </a>';

                    }
                    },
                    'style' => 'width: 10%; text-align: center;',
            ),
        ),
    );

    /* Hotel sem zamenjat deadline z end_date, pa query potem noce vec delat...
    if ($status > 1){
        $end_date = array (
            'header' => array(
                'value' => $txt['delegator_task_end_date'],
            ),
            'data' => array(
                'function' => function($row) {
                    if ($row['end_date'] > $row['deadline']) return '<font color=\"red\">'.$row['end_date'].'</font>';
                    return $row['end_date']; },
                //'style' => 'width: 20%; text-align: center;',
            ),
            'sort' =>  array(
                'default' => 'end_date',
                'reverse' => 'end_date DESC',
            ),
        );
        // sedaj moram pa to zamenjat...
        //$columns['end_date'] = $end_date;
        //unset($columns['deadline']);
        }*/


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

function count_states($states, $what, $value){
    //dobi tabelo stanj in jo dopolni...
    // @todo Naslednji korak k temu, da iz Delegator.template odstranimo queryje
    global $smcFunc, $txt, $scripturl, $context;

    if ($what==="Worker" ){
        // Workers don't have state 0
        $query = 'SELECT COUNT(id) FROM {db_prefix}workers
            WHERE id_member={int:id_member} AND status = {int:status}';
        $values = function ($status) use ($value) {return array ('id_member' => $value, 'status' => $status);};
    }
    elseif ($what==="None"){
        $query = 'SELECT COUNT(id) FROM {db_prefix}tasks
            WHERE  state = {int:state}';
        $values = function ($status) {return array ('state' => $status);};
    }
    elseif ($what==="Project"){
        $query = 'SELECT COUNT(id) FROM {db_prefix}tasks
            WHERE  state = {int:state} AND id_proj = {int:id_proj}';
        $values = function ($status) use ($value) {return array ('state' => $status, 'id_proj' => $value);};
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

    		$request = $smcFunc['db_query']('', '
            SELECT T2.real_name AS member, T2.email_address AS email, T3.name AS project_name, T4.name AS task_name, T4.description AS description
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

?>
