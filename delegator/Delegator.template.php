<?php

/*******************
 * Helper funkcije *
 ******************/

// Sestavi seznam prioritet (izbor)

include "/../../Sources/delegator_helpers.php";
// Tukaj znajo biti se tezave...

// @todo se bo v helper dalo, ampak bi sel rad spat zdaj :)

/******************
 *    Templati    *
 ******************/

function template_main()
{
    global $scripturl, $smcFunc, $txt;

	//if (allowedTo('add_new_todo'))
    echo 'sss, sss, kids! hey, kids!<br> wanna build communism?';
    template_button_strip(array(array('text' => 'delegator_task_add', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=add', 'active'=> true)), 'right');
    template_button_strip(array(array('text' => 'delegator_project_add', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=proj', 'active'=> true)), 'right');

    
    $status = getStatus();
    
    echo '<h2 style="font-size:1.5em" >'.$txt['delegator_state_'.$status ].' &nbsp;'.$txt['delegator_tasks'].'</h2> <hr>';
    
    // Prestejem taske v posameznih stanjih :)
    $states = count_states(array (0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0), "None", 1);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
        echo '<a href="'.$scipturl.'?action=delegator;status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp;'.$states[$status2].'</br>';
    }
    echo '<hr><a href="'.$scipturl.'?action=delegator;status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.($states[0]+$states[1]).'</br>';
    echo '<a href="'.$scipturl.'?action=delegator;status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';
    
    template_show_list('list_tasks');
}

function template_add()
{
	global $scripturl, $context, $txt;
        // id_author, name, description, creation_date, deadline, priority, state

        // dobiti moram projekte: // vir: http://wiki.simplemachines.org/smf/Db_query
        global $smcFunc;

        $id_proj=(isset($_GET['id_proj']) ? $_GET['id_proj'] : FALSE ) ;

        $request = $smcFunc['db_query']('', '
                 SELECT id, name
                 FROM  {db_prefix}projects  ', array()  ); // pred array je manjkala vejica in je sel cel forum v k
        // Zgoraj je treba querry tako popravit, da bo prikazoval se ne zakljucene projekte (POGOJ danasnji datum je pred koncem projekta)

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=add_task" method="post" accept-charset="', $context['character_set'], '" name="delegator_add">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_et">
					<dt>
                       <label for="name"> Zadolzitev </label>
					</dt>
					<dd>
						<input type="text" name="name" value="" size="50" maxlength="255" class="input_text" />
					</dd>
                    <dt>
		            	<label for="description">', $txt['delegator_task_desc'], '</label>
 					</dt>
                    <dd>
                		<textarea name="description" rows="3" cols="30"></textarea>
                    </dd>
					<dt>
                        <label for="duedate">', $txt['delegator_deadline'], '</label>
                    </dt>
					<dd>
						<input type="text" name="duedate" size="8" value="" class="input_text kalender" />
					</dd>
					<dt>
						<label for="user">Delegirani uporabniki</label>
					</dt>
					<dd>
						<input type="text" name="user" id="to-add">
						<span id="user-list"></span>
					</dd>
					<dt>
						<label for="priority">', $txt['delegator_priority'], '</label>
					</dt>
					<dd>
						<ul class="reset">
							' . getPriorities($row, $txt) . '
						</ul>
					</dd>
                    <dt>
						<label for="id_proj">', $txt['delegator_project_name'], '</label>
					</dt>
					<dd>
                       <select name="id_proj">'; // nadomestil navadno vejico
					    while ($row = $smcFunc['db_fetch_assoc']($request)) {
                            if ($row['id'] == $id_proj){
                                    echo '<option value="'.$row['id'].'" selected >--'.$row['name'].'--</option> ';
                                } else {
                                    echo '<option value="'.$row['id'].'" > '.$row['name'].'</option> ';
                                }
                            
					        }
					    $smcFunc['db_free_result']($request);
        				echo '
        				</select>
					</dd>
				</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_task_add'], '" class="button_submit" />
			</div>
            <span class="botslice"><span></span></span>
		</div>
		</form>
        ' . generateMemberSuggest("to-add", "user-list", "member_add") . '
	</div><br />';
}

//funkcija za dodajanje projektov
//imena morajo ustrezat subactionnom...
function template_proj()
{
	global $scripturl, $context, $txt;

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=add_proj" method="post" accept-charset="', $context['character_set'], '" name="delegator_proj">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_et">
					<dt>
						<label for="name">', $txt['delegator_project_name'], '</label>
					</dt>
					<dd>
						<input type="text" name="name" value="" size="50" maxlength="255" class="input_text" />
					</dd>

					<dt>
						<label for="description">', $txt['delegator_project_desc'], '</label>
					</dt>
					<dd>
					   <textarea name="description" rows="3" cols="30"> </textarea>
					</dd>
                    <dt>
						<label for="start">', $txt['delegator_project_start'], '</label><br />
					</dt>
					<dd>
						<input type="text" name="start" class="input_text kalender" />
					</dd>
                    <dt>
						<label for="end">', $txt['delegator_project_end'], '</label>
					</dt>
					<dd>
						<input type="text" name="end" class="input_text kalender" />
					</dd>
				</dl>
                <br />
				<input type="submit" name="submit" value="', $txt['delegator_project_add'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
		</form>
	</div><br />';
}

function template_vt() // id bi bil kar dober argument
{
    /*
      Imena zadolzitev je treba spremeniti v linke, id se lahko posreduje z metodo GET
      Iz baze moram potegniti podatke o tasku
      Prikazati: Ime, rok, pomembnost
                 Opis, opis, opis, opis, opis
                 Izvajalce...
      2 gumba: Back in Submit (nazaj in sprejmi zadolzitev)
     */
    global $scripturl, $context, $txt, $settings;
    global $smcFunc;
    // id_author, name, description, creation_date, deadline, priority, state

    // dobiti moram projekte: // vir: http://wiki.simplemachines.org/smf/Db_query
    $task_id = (int) $_GET['task_id'];

    $request = $smcFunc['db_query']('', '
        SELECT T1.id AS id, T1.name AS task_name, T2.name AS project_name, T1.deadline AS            deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author,            T1.creation_date AS creation_date, T1.description AS description, T1.id_proj             AS id_proj, T1.id_author AS id_author, T1.end_comment, T1.end_date
		FROM {db_prefix}tasks T1
		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
		WHERE T1.id = {int:task_id} ', array('task_id' => $task_id) ); // pred array je manjkala vejica in je sel cel forum v kT1.state =0
// id_proj in id_author searchamo, da bomo lahko linkali na view_person in view_proj

/*          SELECT *
          FROM {db_prefix}tasks
          WHERE id = '.$task_id .'', array() ); // pred array je manjkala vejica in je sel cel forum v k*/
// id od zeljenega taska potrebujemo podatke
    $row = $smcFunc['db_fetch_assoc']($request);
// v tale echo bo padla tudi kaka forma / claim task / edit task

    if ($row['priority'] == 0)
		$pimage = 'warning_watch';
	elseif ($row['priority'] == 1)
		$pimage = 'warn';
	elseif ($row['priority'] == 2)
		$pimage = 'warning_mute';

	$session_var = $context['session_var'];
	$session_id = $context['session_id'];

    // Imam task claiman?
	$member_id = (int) $context['user']['id'];

	$request = $smcFunc['db_query']('',
		'SELECT id
		FROM {db_prefix}workers
		WHERE id_task = {int:task_id} AND id_member = {int:member_id}',
		array(
			'task_id' => $task_id,
			'member_id' => $member_id
		)
	);

    $amClaimed = $smcFunc['db_fetch_assoc']($request);

    if ($amClaimed !== false) {
		$claimButton = '<a href="index.php?action=delegator;sa=unclaim_task;task_id=' . $task_id . ';' . $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_unclaim_task'] . '</a>';
    } else {
    	$claimButton = '<a href="index.php?action=delegator;sa=claim_task;task_id=' . $task_id . ';' . $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_claim_task'] . '</a>';
    }

    // Seznam delegatov

    $request = $smcFunc['db_query']('',
		'SELECT T1.id_member, T2.real_name
		 FROM {db_prefix}workers T1
		 LEFT JOIN {db_prefix}members T2 on T1.id_member = T2.id_member
         WHERE id_task = {int:task_id}',
		array(
			'task_id' => $task_id
		)
	);

    $members = array();
    while ($m = $smcFunc['db_fetch_assoc']($request)) {
    	$members[$m['id_member']] = $m["real_name"];
    }

	$delegates = "&nbsp;&nbsp; (\_/)<br />=(^.^)= &#268;upi<br />&nbsp;(\")_(\")";
	if (count($members)) {
        $delegates = ' ';
		//$delegates = implode(", ", $members);
        foreach( $members as $id_member => $real_name ){
            $delegates = $delegates .  '<a href='. $scripturl .'?action=delegator;sa=view_worker;id_member='. $id_member .'">'.$real_name.'</a>&nbsp;';

                       //<a href="\'. $scripturl .\'?action=delegator;sa=view_worker;id_proj=\'. $row[\'id_member\'] .\'">\'.$row[\'member\'].\'</a>\'
}
	}

    echo '
    <div id="container">
    <div class="cat_bar">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			<dl class="delegator_et">
				<dt>
					<label for="name">', $txt['delegator_task_name'], '</label>
				</dt>
				<dd>
                    <h3> ', $row['task_name'] ,' </h3>
					<!-- <input type="text" name="name" value="" size="50" maxlength="255" class="input_text" /> -->
				</dd>
                <dt>
					<label for="author">', $txt['delegator_task_author'], '</label>
				</dt>
				<dd>
                    <a href="', $scripturl ,'?action=delegator;sa=view_worker;id_member=', $row['id_author'] ,'"> ',$row['author'],'</a>
				</dd>
                <dt>
					<label for="project_name">', $txt['delegator_project_name'], '</label>
				</dt>
				<dd>
                    <a href="', $scripturl ,'?action=delegator;sa=view_proj;id_proj=', $row['id_proj'] ,'">', $row['project_name'], '</a>
				</dd>
                <dt>
					<label for="creation_date">', $txt['delegator_creation_date'], '</label>
				</dt>
				<dd>
                    <span class="format-time">', $row['creation_date'] ,'</span>
				</dd>
                <dt>
					<label for="deadline">', $txt['delegator_deadline'], '</label>
				</dt>
				<dd>
					<span class="relative-time">', $row['deadline'], '</span> (<span class="format-time">' , $row['deadline'], '</span>)
				</dd>
				<dt>
					<label for="delegates">', $txt['delegator_task_delegates'], '</label>
				</dt>
				<dd>
					', $delegates , '
				</dd>
                <dt>
					<label for="description">', $txt['delegator_task_desc'], '</label>
				</dt>
				<dd>
                    ', $row['description'] ,'
				</dd>
                <dt>
					<label for="priority">', $txt['delegator_priority'], '</label>
				</dt>
				<dd>
                    <img src="', $settings['images_url'], '/', $pimage, '.gif" /> ', $txt['delegator_priority_' . $row['priority']] ,'
				</dd>
                <dt>
					<label for="state">', $txt['delegator_state'], '</label>
				</dt>
				<dd> <!-- Stanje in priority je treba se spremenit... da bo kazalo tekst -->
                                       ', $row['state'] ,'
			</dd>';

            if ($row['state'] > 1) echo '
                 <dt>
					<label for="end_date">', $txt['delegator_task_end_date'], '</label>
				</dt>
				<dd>  ', $row['end_date'] ,'
                </dd>
                 <dt>
					<label for="end_comment">', $txt['delegator_task_end_comment'], '</label>
				</dt>
				<dd>  ', $row['end_comment'] ,'
                </dd>';

			echo '</dl> <br />';
                 if( $row['state'] < 2) echo $claimButton, '&nbsp;
                <a href="index.php?action=delegator;sa=et;task_id=', $task_id, '" class="button_submit">', $txt['delegator_edit_task'] ,'</a>&nbsp;
                <a href="index.php?action=delegator;sa=del_task;task_id=', $task_id, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_del_task'] ,'</a> 
               <!-- <a href="index.php?action=delegator;sa=del_task;task_id=', $task_id, ';sesc=', $session_id, '" class="button_submit">', $txt['delegator_del_task'] ,'</a> -->
            ';
        if(isMemberWorker($task_id) and $row['state']==1) echo '<a href="index.php?action=delegator;sa=en;task_id=', $task_id, '" class="button_submit">', $txt['delegator_end_task'] ,'</a>';
            // TUKAJ PRIDE GUMBEK ZA SUPER_EDIT
         // FORA - zakaj pri canceled tasks manjka super_edit???
        //print_r(isMemberCoordinator($row['id_proj'])); print_r($row['state']);
        if(isMemberCoordinator($row['id_proj']) and $row['state'] > 1) echo '<a href="index.php?action=delegator;sa=se;task_id=', $task_id,';" class="button_submit">', $txt['delegator_super_edit'] ,'</a>';
        echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div><br />
';
$smcFunc['db_free_result']($request);

}

//##############################//##############################
//##############################//##############################
//##############################//##############################


function template_view_proj()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_proj = (int) $_GET['id_proj'];

    $status = getStatus();
    
    $request = $smcFunc['db_query']('', '
        SELECT T1.id AS id, T1.name AS proj_name, T1.id_coord AS id_coord, T1.description AS description, T1.start AS start, T1.end AS end, T2.real_name AS coord_name
		FROM {db_prefix}projects T1
		LEFT JOIN {db_prefix}members T2 on T1.id_coord = T2.id_member
		WHERE T1.id = {int:id_proj}', array('id_proj' => $id_proj) ); // pred array je manjkala vejica in je sel cel forum v kT1.state =0

    $row = $smcFunc['db_fetch_assoc']($request);

    echo '
    <div id="container">
    <div class="cat_bar">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			<dl class="delegator_et">
				<dt>
					<label for="name">', $txt['delegator_project_name'], '</label>
				</dt>
				<dd>
                    ', $row['proj_name'] ,'
				</dd>
                <dt>
					<label for="coordinator">', $txt['delegator_project_coord'], '</label>
				</dt>
				<dd>
                    <a href="', $scripturl ,'?action=delegator;sa=view_worker;id_member=', $row['id_coord'] ,'"> ',$row['coord_name'],'</a>
				</dd>
                <dt>
					<label for="start">', $txt['delegator_project_start'], '</label>
				</dt>
				<dd>
                    <span class="format-date">', $row['start'] ,'</span>
				</dd>
                <dt>
					<label for="end">', $txt['delegator_project_end'], '</label>
				</dt>
				<dd>
                    <span class="format-date">', $row['end'] ,'</span>
				</dd>
                <dt>
					<label for="description">', $txt['delegator_project_desc'], '</label>
				</dt>
				<dd>
                    ', $row['description'] ,'
				</dd>

			</dl>
            <br />
			<a href="index.php?action=delegator;sa=add;id_proj=',$id_proj,'" class="button_submit">', $txt['delegator_task_add'] ,'</a>&nbsp;

           <!-- <a href="index.php?action=delegator;sa=ep;id_proj=', $id_proj, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_edit_proj'] ,'</a>&nbsp;
            <a href="index.php?action=delegator;sa=del_proj;proj_id=', $id_proj, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_del_proj'] ,'</a> -->
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div><br />
</div>
';

    $smcFunc['db_free_result']($request);
    // This part shows number of tasks in different state
    // @todo this part should be part of some upper div...
    // also upper div should be edited properly and nicely
    $states = count_states(array (0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0), "Project", $id_proj);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
         echo '<a href="'.$scipturl.'?action=delegator;sa=view_proj;id_proj='.$id_proj.';status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp'.$states[$status2].'</br>';

    }
    echo '<hr><a href="'.$scipturl.'?action=delegator;sa=view_proj;id_proj='.$id_proj.';status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.($states[0] + $states[1]).'</br>';
    echo '<a href="'.$scipturl.'?action=delegator;sa=view_proj;id_proj='.$id_proj.';status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';

    
    template_show_list('list_tasks_of_proj');
}


//##############################//##############################
//##############################//##############################
//##############################//##############################


function template_view_worker()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_member = (int) $_GET['id_member'];

    $request = $smcFunc['db_query']('', '
    SELECT T1.real_name AS name FROM {db_prefix}members T1
    WHERE T1.id_member={int:id_member}', array('id_member' => $id_member));

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_worker'] .': '.$row['name']. '</h2>';

    $states = count_states(array (1 => 0, 2 => 0, 3 => 0, 4 => 0), "Worker", $id_member);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
         echo '<a href="'.$scipturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp'.$states[$status2].'</br>';

    }
    echo '<hr><a href="'.$scipturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.$states[1].'</br>';
    echo '<a href="'.$scipturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';

    

    template_show_list('list_tasks_of_worker'); // ko bomo odkomentirali veliki del v Delegator.php, se odkomentira tudi to in vuala, bodo taski...

}

function template_my_tasks()
{
    // Tukaj mora biti privzeit status 1!!!
    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_member = $context['user']['id'];

    $status = getStatus(true);

    $request = $smcFunc['db_query']('', '
    SELECT T1.real_name AS name FROM {db_prefix}members T1
    WHERE T1.id_member={int:id_member}', array('id_member' => $id_member) );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    echo '<h2 style="font-size:1.5em" >'.$txt['delegator_my_tasks'].' </br>'. $txt['delegator_worker'] .': '.$row['name']. '</br>'.$txt['delegator_state_'.$status].'</h2> <hr>';
    
    $states = count_states(array (1 => 0, 2 => 0, 3 => 0, 4 => 0), "Worker", $id_member);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
         echo '<a href="'.$scipturl.'?action=delegator;sa=my_tasks;status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp'.$states[$status2].'</br>';

    }
    echo '<hr><a href="'.$scipturl.'?action=delegator;sa=my_tasks;status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.$states[1].'</br>';
    echo '<a href="'.$scipturl.'?action=delegator;sa=my_tasks;status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';
    
    template_show_list('list_tasks_of_worker'); // ko bomo odkomentirali veliki del v Delegator.php, se odkomentira tudi to in vuala, bodo taski...
}

function template_view_projects()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_view_projects'] .' </h2>';
    //print_r ("template se nalozi");

    template_show_list('list_of_projects'); // ko bomo odkomentirali veliki del v Delegator.php, se odkomentira tudi to in vuala, bodo taski...

}

function template_view_log()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $session_var = $context['session_var'];
	$session_id = $context['session_id'];
    
    template_button_strip(array(array('text' => 'delegator_del_log', 'image' => 'to_do_add.gif', 'lang' => true, 'url' => $scripturl . '?action=delegator' . ';sa=del_log;'.$session_var. '='. $session_id, 'active'=> true)), 'right');

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_view_log'] .' </h2><hr>';
    //print_r ("template se nalozi");

    //echo '<a href="index.php?action=delegator;sa=del_log;'. $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_del_log'] . '</a>';
    
    
    
    
    template_show_list('log'); // ko bomo odkomentirali veliki del v Delegator.php, se odkomentira tudi to in vuala, bodo taski...
}



//##############################//##############################
//##############################//##############################
//##############################//##############################

function template_et()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_task = (int) $_GET['task_id'];

    $request = $smcFunc['db_query']('', '
        SELECT T1.id, T1.name AS task_name,  T1.deadline, T1.description, T1.priority, T1.id_proj
		FROM {db_prefix}tasks T1
		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
        WHERE T1.id = {int:id_task}',
        array( 'id_task' => $id_task)
    );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    // Delegirani uporabniki
    $request_d = $smcFunc['db_query']('', '
        SELECT T1.id_member, T2.real_name
        FROM {db_prefix}workers T1
        LEFT JOIN {db_prefix}members T2 ON T1.id_member = T2.id_member
        WHERE T1.id_task = {int:id_task}',
        array('id_task' => $id_task)
    );

    $delegates = "";
    while ($member = $smcFunc['db_fetch_assoc']($request_d)) {
        $id = $member["id_member"];
        $name = $member["real_name"];
        $delegates .=
            '<div id="suggest_to-add_' . $id . '">
                <input name="member_add[]" value="' . $id . '" type="hidden">
                <a href="index.php?action=profile;u=' . $id . '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">' . $name . '</a>
                <img src="Themes/default/images/pm_recipient_delete.gif" alt="Delete Item" title="Delete Item" onclick="return oAddMemberSuggest.deleteAddedItem(' . $id . ');">
            </div>';
    }
    $smcFunc['db_free_result']($request_d);

    $request_p = $smcFunc['db_query']('', '
        SELECT id, name
        FROM  {db_prefix}projects'
    );

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=edit_task" method="post" accept-charset="', $context['character_set'], '" name="delegator_edit_task">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
                <dl class="delegator_et">
					<dt>
                        <label for="name">', $txt['delegator_task_name'], '</label>
					</dt>
					<dd>
						<input type="text" name="name" value="'.$row['task_name'].'" size="50" maxlength="255" class="input_text" />
                        <input type="hidden" name="id_task" value ="'.$id_task.'" />
					</dd>
                    <dt>
               		    <label for="description">', $txt['delegator_task_desc'], '</label>
 					</dt>
                    <dd>
               			<textarea name="description" rows="3" cols="30" > '.$row['description'].' </textarea>
                    </dd>
					<dt>
			    		<label for="deadline">', $txt['delegator_deadline'], '</label>
					</dt>
					<dd>
						<input class="kalender" type="text" name="deadline" value="' . $row['deadline'] . '"/>
                    </dd>
					<dt>
						<label for="user">', $txt['delegator_task_delegates'], '</label>
					</dt>
					<dd>
						<input id="to-add" type="text" name="user">
						<div id="user-list">
                            ' . $delegates . '
                        </div>
					</dd>
					<dt>
						<label>', $txt['delegator_priority'], '</label>
					</dt>
					<dd>
						<ul class="reset">
							' . getPriorities($row, $txt) . '
						</ul>
					</dd>
                    <dt>
                        <label for="id_proj"><b>', $txt['delegator_project_name'], '</b></label>
                    </dt>
                    <dd>
                        <select name="id_proj">'; // nadomestil navadno vejico
                            while ($row_p = $smcFunc['db_fetch_assoc']($request_p)) {
                                if ($row_p['id'] == $row['id_proj']){
                                    echo '<option value="'.$row_p['id'].'" selected >--'.$row_p['name'].'--</option> ';
                                } else {
                                    echo '<option value="'.$row_p['id'].'" > '.$row_p['name'].'</option> ';
                                }
                            }
                            $smcFunc['db_free_result']($request_p);
                            echo '
                        </select>
                    </dd>
				</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_edit_task'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
		</div>
		</form>
    ' . generateMemberSuggest("to-add", "user-list", "member_add") .  '
	</div><br />';
}

//en je okrajsava za end task...

function template_en()
{
	global $scripturl, $context, $txt, $settings;
    global $smcFunc;

	//$session_var = $context['session_var'];
	//$session_id = $context['session_id'];
        
       // rabim samo id_task-a, ki se zakljucuje

    $id_task = (int) $_GET['task_id'];

/***************************************************************************
 ******************* * Podvojeno iz vt *************************************
 **************************************************************************/

    $request = $smcFunc['db_query']('', '
       SELECT T1.id AS id, T1.name AS task_name, T2.name AS project_name, T1.deadline AS deadline, T1.priority AS priority, T1.state AS state, T3.real_name AS author, T1.creation_date AS creation_date, T1.description AS description, T1.id_proj AS id_proj, T1.id_author AS id_author
		FROM {db_prefix}tasks T1
		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
		WHERE T1.id = {int:id_task} ', array('id_task' => $id_task) ); // pred array je manjkala vejica in je sel cel forum v kT1.state =0
// id_proj in id_author searchamo, da bomo lahko linkali na view_person in view_proj

// id od zeljenega taska potrebujemo podatke
    $row = $smcFunc['db_fetch_assoc']($request);
// v tale echo bo padla tudi kaka forma / claim task / edit task

    if ($row['priority'] == 0)
		$pimage = 'warning_watch';
	elseif ($row['priority'] == 1)
		$pimage = 'warn';
	elseif ($row['priority'] == 2)
		$pimage = 'warning_mute';


	// Imam task claiman?
	$member_id = (int) $context['user']['id'];

	$request = $smcFunc['db_query']('',
		'SELECT id
		FROM {db_prefix}workers
		WHERE id_task = {int:id_task} AND id_member = {int:member_id}',
		array(
			'id_task' => $id_task,
			'member_id' => $member_id
		)
	);


    // Seznam delegatov

    $request = $smcFunc['db_query']('',
		'SELECT T2.real_name
		FROM {db_prefix}workers T1
			LEFT JOIN {db_prefix}members T2 on T1.id_member = T2.id_member

		WHERE id_task = {int:id_task}',
		array(
			'id_task' => $id_task
		)
	);

    $members = array();
    while ($m = $smcFunc['db_fetch_assoc']($request)) {
    	$members[] = $m["real_name"];
    }

	$delegates = "&nbsp;&nbsp;&nbsp;(\_/)<br />=(^.^)= &#268;upi<br />&nbsp;(\")_(\")";
	if (count($members)) {
		$delegates = implode(", ", $members);
	}

echo '
    <div id="container">
    <div class="cat_bar">
		<h3 class="catbg"><span class="left"></span>
			', $context['page_title'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="content">
			<dl class="delegator_vt">
				<dt>
					<label for="name">', $txt['delegator_task_name'], '</label>
				</dt>
				<dd>
                    <h3> ', $row['task_name'] ,' </h3>
					<!-- <input type="text" name="name" value="" size="50" maxlength="255" class="input_text" /> -->
				</dd>
                <dt>
					<label for="author">', $txt['delegator_task_author'], '</label>
				</dt>
				<dd>
                    <a href="', $scripturl ,'?action=delegator;sa=view_worker;id_member=', $row['id_author'] ,'"> ',$row['author'],'</a>
				</dd>
                <dt>
					<label for="project_name">', $txt['delegator_project_name'], '</label>
				</dt>
				<dd>
                    <a href="', $scripturl ,'?action=delegator;sa=view_proj;id_proj=', $row['id_proj'] ,'">', $row['project_name'], '</a>
				</dd>
                <dt>
					<label for="creation_date">', $txt['delegator_creation_date'], '</label>
				</dt>
				<dd>
                    <span class="format-time">', $row['creation_date'] ,'</span>
				</dd>
                <dt>
					<label for="deadline">', $txt['delegator_deadline'], '</label>
				</dt>
				<dd>
					<span class="relative-time">', $row['deadline'], '</span> (<span class="format-time">' , $row['deadline'], '</span>)
				</dd>
				<dt>
					<label for="delegates">', $txt['delegator_task_delegates'], '</label>
				</dt>
				<dd>
					', $delegates , '
				</dd>
                <dt>
					<label for="description">', $txt['delegator_task_desc'], '</label>
				</dt>
				<dd>
                    ', $row['description'] ,'
				</dd>
                <dt>
					<label for="priority">', $txt['delegator_priority'], '</label>
				</dt>
				<dd>
                    <img src="', $settings['images_url'], '/', $pimage, '.gif" /> ', $txt['delegator_priority_' . $row['priority']] ,'
				</dd>
                <dt>
					<label for="state">', $txt['delegator_state'], '</label>
				</dt>
				<dd> <!-- Stanje in priority je treba se spremenit... da bo kazalo tekst -->
                                       ', $row['state'] ,'
				</dd>
			 </dl>
			 <br />
        <!-- teh gumbkov ni vec, ker smo ze v end_task -->

			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div><br />
';
$smcFunc['db_free_result']($request);



/***************************************************************************
 ****************** Konec Podvojitve ***************************************
 **************************************************************************/

/**************************************************************************
 *********** Lepo je, ce se najprej prikaze specifikacija taska
 *******  Treba je se enkrat preverit, ce je trenutni uporabnik tudi izvajalec nadloge!
 ***************************************************************************/


	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=end_task" method="post" accept-charset="', $context['character_set'], '" name="delegator_end_task">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_et">
					<dt> <!-- tukaj more priti dolartxt[name]-->
                       <label for="name"> End Comment</label>
					</dt>
					<dd>
                         <textarea name="end_comment" rows="3" cols="30"></textarea>
                         <input type="hidden" name="id_task" value ="'.$id_task.'" />
					</dd>
                    <dt>
		            	<label for="state"> End state (nacin zakljucka) </label>
                        <!-- tukaj bo dolartxt spremenljivka -->
 					</dt>
                    <dd>
                        	<ul class="reset">
							<li><input type="radio" name="state" value="2"  class="input_radio" checked="checked" /> ', $txt['delegator_state_2'], '</li>
							<li><input type="radio" name="state"  value="3" class="input_radio" /> ', $txt['delegator_state_3'], '</li>
							<li><input type="radio" name="state"  value="4" class="input_radio" /> ', $txt['delegator_state_4'], '</li>
						</ul>
                    </dd>

				</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_end_task'], '" class="button_submit" />
			</div>
            <span class="botslice">&nbsp;</span>
		</div>
		</form>
        ' . generateMemberSuggest('to-add', 'user-list',  'member_add') . '
	</div><br />';
}

function template_se()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_task = (int) $_GET['task_id'];

    $request = $smcFunc['db_query']('', '
        SELECT T1.id, T1.name AS task_name,  T1.deadline, T1.description, T1.priority, T1.id_proj, T1.state, T1.start_date, T1.end_date, T1.end_comment
		FROM {db_prefix}tasks T1
		LEFT JOIN {db_prefix}projects T2 ON T1.id_proj = T2.id
		LEFT JOIN {db_prefix}members T3 ON T1.id_author = T3.id_member
        WHERE T1.id = {int:id_task}',
        array( 'id_task' => $id_task)
    );

    $row = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);

    // Delegirani uporabniki
    $request_d = $smcFunc['db_query']('', '
        SELECT T1.id_member, T2.real_name
        FROM {db_prefix}workers T1
        LEFT JOIN {db_prefix}members T2 ON T1.id_member = T2.id_member
        WHERE T1.id_task = {int:id_task}',
        array('id_task' => $id_task)
    );

    $delegates = "";
    while ($member = $smcFunc['db_fetch_assoc']($request_d)) {
        $id = $member["id_member"];
        $name = $member["real_name"];
        $delegates .=
            '<div id="suggest_to-add_' . $id . '">
                <input name="member_add[]" value="' . $id . '" type="hidden">
                <a href="index.php?action=profile;u=' . $id . '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">' . $name . '</a>
                <img src="Themes/default/images/pm_recipient_delete.gif" alt="Delete Item" title="Delete Item" onclick="return oAddMemberSuggest.deleteAddedItem(' . $id . ');">
            </div>';
    }
    $smcFunc['db_free_result']($request_d);

    $request_p = $smcFunc['db_query']('', '
        SELECT id, name
        FROM  {db_prefix}projects'
    );

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=edit_task" method="post" accept-charset="', $context['character_set'], '" name="delegator_edit_task">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl class="delegator_et">
						<dt>
                            <label for="name">', $txt['delegator_task_name'], '</label>
						</dt>
						<dd>
							<input type="text" name="name" value="'.$row['task_name'].'" size="50" maxlength="255" class="input_text" />
                            <input type="hidden" name="id_task" value ="'.$id_task.'" />
						</dd>
                        <dt>
                		    <label for="description">', $txt['delegator_task_desc'], '</label>
 						</dt>
                        <dd>
                			<textarea name="description" rows="3" cols="30" > '.$row['description'].' </textarea>
                        </dd>
						<dt>
							<label for="deadline">', $txt['delegator_deadline'], '</label>
						</dt>
						<dd>
							<input class="kalender" type="text" name="deadline" value="' . $row['deadline'] . '"/>
                        </dd>
						<dt>
							<label for="user">', $txt['delegator_task_delegates'], '</label>
						</dt>
						<dd>
							<input id="to-add" type="text" name="user">
							<div id="user-list">
                                ' . $delegates . '
                            </div>
						</dd>
						<dt>
							<label>', $txt['delegator_priority'], '</label>
						</dt>
						<dd>
							<ul class="reset">
								' . getPriorities($row, $txt) . '
							</ul>
						</dd>
					</dl>
                    <dt>
						<label for="id_proj"><b>', $txt['delegator_project_name'], '</b></label>
					</dt>
					<dd>
                    	<select name="id_proj">'; // nadomestil navadno vejico
					        while ($row_p = $smcFunc['db_fetch_assoc']($request_p)) {
					            if ($row_p['id'] == $row['id_proj']){
					                echo '<option value="'.$row_p['id'].'" selected >--'.$row_p['name'].'--</option> ';
					            }
					            else {
					                echo '<option value="'.$row_p['id'].'" > '.$row_p['name'].'</option> ';
					            }
					        }
        					$smcFunc['db_free_result']($request_p);

            			echo '
            			</select>
					</dd>

						<dt>
							<label for="state">', $txt['delegator_state'], '</label>
						</dt>
						<dd>
                            <select name="state">';

                        for ($i = 0; $i <= 5; $i++){
                            if ($row['state'] == $i) echo '<option value="'.$i.'" selected >--'.$txt['delegator_state_'.$i].'--</option> ';
                            else echo '<option value="'.$i.'">'.$txt['delegator_state_'.$i].'</option> ';
                            
                        }


                        echo   ' </select>

                        </dd>

						<dt>
							<label for="start_date">', $txt['delegator_task_start_date'], '</label>
						</dt>
						<dd>
							<input class="kalender" type="text" name="start_date" value="' . $row['start_date'] . '"/>
                        </dd>

                       <dt>
							<label for="end_date">', $txt['delegator_task_end_date'], '</label>
						</dt>
						<dd>
							<input class="kalender" type="text" name="en_ddate" value="'. $row['start_date'] . '"/>

                        </dd>

                        <dt>
                		    <label for="description">', $txt['delegator_end_comment'], '</label>
 						</dt>
                        <dd>
                			<textarea name="end_comment" rows="3" cols="30" > '.$row['end_comment'].' </textarea>
                        </dd>



				</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_edit_task'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
		</div>
		</form>
        ' . generateMemberSuggest('to-add', 'user-list', 'member_add') . '
	</div><br />';
}

//en je okrajsava za end task...


?>
