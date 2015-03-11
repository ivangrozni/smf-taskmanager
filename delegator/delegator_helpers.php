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

function getStatus(){

    if( isset($_GET['status']) ){
        $status = $_GET['status'];
    }
    else{
        $status = 0;
    }
    return $status;
}

function getStatus1(){
    if( isset($_GET['status']) ){
        $status = $_GET['status'];
    }
    else{
        $status = 1;
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
                          array('id') ); 
    //  array( $id_proj, $id_task, $action, $id_member, date('Y-m-d') ),
}
?>