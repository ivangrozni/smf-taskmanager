<?php
/****************************************************************+
 * Ta koda je samo za v simple portal... Prikaze moje zadolzitve *
 * in link do njih ...                                           *
 * Presteje tudi unclaimane zadolzitve in link do njih           *
 * Moje Zadolzitve za rokom obarva rdece                         *
 *****************************************************************/

function getPriorityIcon2($row) {
    global $settings, $txt;

    if ($row['priority'] == 0)
        $image = 'warning_watch';
    elseif ($row['priority'] == 1)
        $image = 'warn';
    elseif ($row['priority'] == 2)
        $image = 'warning_mute';

    return '<img src="'. $settings['images_url']. '/'. $image. '.gif" title="Priority: ' . $txt['delegator_priority_' . $row['priority']] . '" alt="Priority: ' . $txt['delegator_priority_' . $row['priority']] . '" /> ';
}

function show(){
    
    global $smcFunc, $context, $txt, $scipturl;

    $id_member = $context['user']['id'];
    $today = date('Y-m-d');

// Prestejem stevilo unclaimanih taskov
    $request3 = $smcFunc['db_query']('', '
            SELECT COUNT(id) FROM {db_prefix}tasks
            WHERE  state = 0',
            array() );
    $row3 = $smcFunc['db_fetch_assoc']($request3); // tole zna da ne bo delal
    $count_unclaim = $row3['COUNT(id)']; //kao bi moral ze to spremenit $states, ampak jih ne
    $smcFunc['db_free_result']($request3);

// Prestejem stevilo zadolzitev osebe
    $request2 = $smcFunc['db_query']('', '
            SELECT COUNT(id) FROM {db_prefix}workers
            WHERE id_member={int:id_member} AND status = 1',
            array('id_member' => $id_member, ) );
    $row2 = $smcFunc['db_fetch_assoc']($request2); // tole zna da ne bo delal
    $count_my = $row2['COUNT(id)']; //kao bi moral ze to spremenit $states, ampak jih ne
    $smcFunc['db_free_result']($request2);

    $request = $smcFunc['db_query']('','
    SELECT T1.id_task AS id_task,T2.name AS task_name, T3.name AS project_name, T2.deadline AS deadline, T2.priority AS priority, T2.id_proj AS id_proj
    FROM {db_prefix}workers T1
    LEFT JOIN {db_prefix}tasks T2 ON T1.id_task = T2.id
    LEFT JOIN {db_prefix}projects T3 ON T2.id_proj = T3.id
    WHERE T1.id_member={int:id_member} AND T1.status = 1',
    array ('id_member' => $id_member));


    echo '<a href="', $scripturl, '?action=delegator;status=0">', $txt['delegator_state_0'], '</a>: &nbsp;', $count_unclaim ;

    echo '</br><a href="', $scripturl, '?action=delegator;sa=my_tasks">', $txt['delegator_my_tasks'], '</a>: &nbsp;', $count_my ;

    echo '<hr><table class="delegator-sidebar">';
    while ($row = $smcFunc['db_fetch_assoc']($request)){
        echo '<tr><td><a href="', $scripturl ,'?action=delegator;sa=vt;task_id=', $row['id_task'] ,'"> ',$row['task_name'],'</a></td>
          <td><a href="', $scripturl ,'?action=delegator;sa=view_proj;id_proj=', $row['id_proj'] ,'"> ',$row['project_name'],'</a></td>
          <td>';
        if ($today > $row['deadline']) echo '<font color="red">' .$row['deadline'].'</font></td>';
        else echo '<span class="relative-time">' . $row['deadline'] . '</span></td>';
        //echo '<td>'.$row['priority'].'</td></tr>';
        echo  '<td>'. getPriorityIcon2($row) . '</td></tr>';
}
    echo '</table>';
    $smcFunc['db_free_result']($request);
}

show();

?>
