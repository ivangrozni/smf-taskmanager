<?php

// First of all, we make sure we are accessing the source file via SMF so that people can not directly access the file. Sledeci vrstici sta dodani, da kdo ne sheka (SMF uporablja v vseh fajlih, mod ni uporabljal).
if (!defined('SMF'))
    die('Hack Attempt...');

/*******************
 * Helper funkcije *
 ******************/

// Sestavi seznam prioritet (izbor)

require_once "$sourcedir/delegator_helpers.php";
// Tukaj znajo biti se tezave...

/******************
 *    Templati    *
 ******************/
/**
 * Main template
 *
 * Shows unclaimed unfinished tasks.
 */
function template_main()
{
    global $scripturl, $smcFunc, $txt;

	//if (allowedTo('add_new_todo'))
    echo 'sss, sss, kids! hey, kids!<br> wanna build communism?';

    // Task add button
    template_button_strip(array(array(
        'text' => 'delegator_task_add',
        'image' => 'to_do_add.gif',
        'lang' => true,
        'url' => "$scripturl?action=delegator;sa=add_task",
        'active'=> true
        )), 'right'
    );

    // Project add button
    template_button_strip(array(array(
        'text' => 'delegator_project_add',
        'image' => 'to_do_add.gif',
        'lang' => true,
        'url' => $scripturl . '?action=delegator;sa=add_project',
        'active'=> true
        )), 'right'
    );

    $status = getStatus();
    echo '<h2 style="font-size:1.5em" >' . $txt["delegator_state_$status"] . '&nbsp;' . $txt['delegator_tasks'] . '</h2><hr>';

    // Prestejem taske v posameznih stanjih :)
    $states = count_states(array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0), "None", 1);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
        echo "<a href=\"$scipturl?action=delegator;status=$status2\">" . $txt["delegator_state_$status2"] . '</a>:&nbsp;' . $states[$status2] . '</br>';
    }
    echo "<hr><a href=\"$scipturl?action=delegator;status=unfinished\">" . $txt['delegator_state_unfinished'] . '</a>:&nbsp;' . ($states[0] + $states[1]) . '</br>';
    echo "<a href=\"$scipturl?action=delegator;status=finished\">" . $txt['delegator_state_finished'] . '</a>:&nbsp;' . ($states[2] + $states[3] + $states[4]) . '</br><hr>';

    template_show_list('list_tasks');
}


/**
 * Add task template
 *
 * Querries for projects,
 */
function template_add_task()
{
	global $scripturl, $context, $txt;
    // id_author, name, description, creation_date, deadline, priority, state
    // dobiti moram projekte: // vir: http://wiki.simplemachines.org/smf/Db_query

    $id_proj=(isset($_GET['id_proj']) ? (int) $_GET['id_proj'] : FALSE ) ;

    $projects = list_projects();
    // @todo maybe we could show not finished projects.

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=add_task_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_add">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_edit_task">';
    echo dl_form("name",$txt['delegator_task_name'], "input-text", "", "input_text", 50, 255 );
    echo dl_form("description", $txt['delegator_task_desc'], "textarea", "", "", 3, 30);
    echo dl_form("duedate", $txt['delegator_deadline'], "input-text", "", "input_text kalender", 8, 100);
    echo dl_form("user", $txt['delegator_delegates'], "delegates", "", "");
    echo dl_form("priority", $txt['delegator_priority'], "priority", getPriorities($row, $txt), "reset", "", "");
    echo dl_form("id_proj", $txt['delegator_project_name'], "projects", $id_proj, "");
	echo '</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_task_add'], '" class="button_submit" />
			</div>
            <span class="botslice"><span></span></span>
		</div>
        ' . generateMemberSuggest("to-add", "user-list", "member_add") . '
		</form>
	</div><br />';
   // @gismoe Tukaj klicemo generateMemberSuggest, ki bi moral kot param dobit
   // parameter, ki nosi seznam memberjev, ta pa se nikjer ne ustvari ...
}

/**
 * Template for adding projects.
 */
function template_add_project()
{
	global $scripturl, $context, $txt;

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=add_project_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_proj">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_edit_task">';
    echo dl_form("name", $txt['delegator_project_name'], "input-text", "", "input_text", 50, 255 );
    echo dl_form("description", $txt['delegator_project_desc'], "textarea", "", "textarea", 3, 30);
    echo dl_form("start", $txt['delegator_project_start'], "input-text", "", "input_text kalender", 8, 100);
    echo dl_form("end", $txt['delegator_project_end'], "input-text", "", "input_text kalender", 8, 100);
    echo '
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

/**
 * Template shows particular task.
 *
 */
function template_view_task() // id bi bil kar dober argument
{

    global $smcFunc, $scripturl, $context, $txt, $settings;
    $session_var = $context['session_var'];
	$session_id = $context['session_id'];

    $id_task = (int) $_GET['id_task'];

    $row = task_info($id_task);

    if ($row['priority'] == 0)
		$pimage = 'warning_watch';
	elseif ($row['priority'] == 1)
		$pimage = 'warn';
	elseif ($row['priority'] == 2)
		$pimage = 'warning_mute';

    if (isMemberWorker($id_task)) {
		$claimButton = '<a href="index.php?action=delegator;sa=unclaim_task;id_task=' . $id_task . ';' . $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_unclaim_task'] . '</a>';
    }
    else {
    	$claimButton = '<a href="index.php?action=delegator;sa=claim_task;id_task=' . $id_task . ';' . $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_claim_task'] . '</a>';
    }

    $workers = workers_on_task($id_task);

	$delegates = "&nbsp;&nbsp; (\_/)<br />=(^.^)= &#268;upi<br />&nbsp;(\")_(\")";

    if (count($workers)) {
        $delegates = ' ';
        foreach ($workers as $id_member => $real_name) {
            $delegates = $delegates . "<a href=\"$scripturl?action=delegator;sa=view_worker;id_member=$id_member\">$real_name</a>&nbsp;";
        }}

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
			<dl class="delegator_edit_task">';

    echo dl_view("name", $txt['delegator_task_name'], "title", $row['task_name'], "");
    echo dl_view("author", $txt['delegator_task_author'], "link", "view_worker;id_member=".$row['id_author'] , $row['author']);
    echo dl_view("project_name", $txt['delegator_project_name'], "link", 'view_project;id_proj='.$row['id_proj'] , $row['project_name']);
    echo dl_view("creation_date", $txt['delegator_creation_date'], "date", $row['creation_date'] , "format-time");
    echo dl_view("deadline", $txt['delegator_deadline'], "date", $row['deadline'] , "relative-time");
    echo dl_view("delegates", $txt['delegator_task_delegates'], "description", $delegates, "");
    echo dl_view("description", $txt['delegator_task_desc'], "description", $row['description'], "");
    echo dl_view("priority", $txt['delegator_priority'], "priority", $settings['images_url'].'/'.$pimage .'.gif', $txt['delegator_priority_' . $row['priority']]);
    echo dl_view("state", $txt['delegator_state'], "description", $row['state'] , "");

    if ($row['state'] > 1) {
        echo dl_view("end_date", $txt['delegator_task_end_date'], "date", $row['end_date'] , "format_time");
        // var_dump($row); die;
        echo dl_view("end_comment", $txt['delegator_task_end_comment'], "description", $row['end_comment'], "");
    }

    echo '</dl><br />';

    if ($row['state'] < 2) {
        echo $claimButton, '&nbsp;
                    <a href="index.php?action=delegator;sa=edit_task;id_task=', $id_task, '" class="button_submit">', $txt['delegator_edit_task'], '</a>&nbsp;
                    <a href="index.php?action=delegator;sa=del_task;id_task=', $id_task, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_del_task'] ,'</a>';
     }

    if (isMemberWorker($id_task) and $row['state']==1) {
        echo '&nbsp';
        echo '<a href="index.php?action=delegator;sa=end_task;id_task=', $id_task, '" class="button_submit">', $txt['delegator_end_task'] ,'</a>';
    }
    if(isMemberCoordinator($row['id_proj']) and $row['state'] > 1) {
        echo '&nbsp';
        echo '<a href="index.php?action=delegator;sa=super_edit;id_task=', $id_task,';" class="button_submit">', $txt['delegator_super_edit'] ,'</a>';
}

    echo '
		</div>
    	<span class="botslice"><span></span></span>
    	</div>
    	</div><br />
    ';
}

//##############################//##############################
//##############################//##############################
//##############################//##############################


function template_view_project()
{

    global $scripturl, $context, $txt;
    global $smcFunc;

    $id_proj = (int) $_GET['id_proj'];

    $status = getStatus();

    $session_var = $context['session_var']; // we do not understand this ...
	$session_id = $context['session_id'];


    $row = project_info($id_proj);

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
    			<dl class="delegator_edit_task">';

     echo dl_view("name", $txt['delegator_project_name'], "title", $row['proj_name'] , "");
     echo dl_view("coordinator", $txt['delegator_project_coord'], "link", 'view_worker;id_member='.$row['id_coord'] , $row['coord_name']);
     echo dl_view("start", $txt['delegator_project_start'], "date", $row['start'] , 'format-date');
     echo dl_view("end", $txt['delegator_project_end'], "date", $row['end'] , 'format-date');
     echo dl_view("description", $txt['delegator_project_desc'], "description", $row['description'] , '""');

        echo	'
    			</dl>
                <br />
    			<a href="index.php?action=delegator;sa=add_task;id_proj=',$id_proj,'" class="button_submit">', $txt['delegator_task_add'] ,'</a>&nbsp;
                ';
                // preverim, ce se lahko narise gumbek za del project
                // check if the user has permission to delete project
                // @todo permission check
    // kako je s tem $scripturl
                echo '<a href="index.php?action=delegator;sa=del_project;id_proj=', $id_proj, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_delete_project'] ,'</a>';

                echo '
                <a href="index.php?action=delegator;sa=edit_project;id_proj=', $id_proj, '" class="button_submit">', $txt['delegator_edit_project'] ,'</a>&nbsp;
                <!-- <a href="'.$scripturl.'?action=delegator;sa=del_project;proj_id=', $id_proj, ';', $session_var, '=', $session_id, '" class="button_submit">', $txt['delegator_delete_project'] ,'</a> -->
    			</div>
    			<span class="botslice"><span></span></span>
    		</div>
    	</div><br />
    </div>
    ';

    // This part shows number of tasks in different state
    // @todo this part should be part of some upper div...
    // also upper div should be edited properly and nicely
    $states = count_states(array (0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0), "Project", $id_proj);
    foreach ($states as $status2 => $count){
         // FUXK ZA COUNT NE SME BIT PRESLEDKA!!!
         echo '<a href="'.$scripturl.'?action=delegator;sa=view_project;id_proj='.$id_proj.';status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp'.$states[$status2].'</br>';

    }
    echo '<hr><a href="' . $scripturl . '?action=delegator;sa=view_project;id_proj='.$id_proj.';status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.($states[0] + $states[1]).'</br>';
    echo '<a href="' . $scripturl . '?action=delegator;sa=view_project;id_proj='.$id_proj.';status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';


    template_show_list('list_tasks_of_proj');
}


//##############################//##############################
//##############################//##############################
//##############################//##############################


function template_view_worker()
{

    global $scripturl, $txt;

    $id_member = (int) $_GET['id_member'];
    $name = member_name($id_member);

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_worker'] .': '.$name. '</h2>';

    $states = count_states(array (1 => 0, 2 => 0, 3 => 0, 4 => 0), "Worker", $id_member);
    foreach ($states as $status2 => $count){
         echo '<a href="'.$scripturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status='.$status2.'">'.$txt['delegator_state_'.$status2].'</a>:&nbsp'.$states[$status2].'</br>';
    }
    echo '<hr><a href="'.$scripturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status=unfinished">'.$txt['delegator_state_unfinished'].'</a>:&nbsp;'.$states[1].'</br>';
    echo '<a href="'.$scripturl.'?action=delegator;sa=view_worker;id_member='.$id_member.';status=finished">'.$txt['delegator_state_finished'].'</a>:&nbsp;'.($states[2]+$states[3]+$states[4]).'</br><hr>';

    template_show_list('list_tasks_of_worker');
}

function template_my_tasks()
{
    // Tukaj mora biti privzeit status 1!!!
    global $scripturl, $txt, $context;

    $id_member = $context['user']['id'];
    $status = getStatus(true);

    $name = member_name($id_member);

    echo '<h2 style="font-size:1.5em" >'.$txt['delegator_my_tasks'].' </br>'. $txt['delegator_worker'] .': '.$name. '</br>'.$txt['delegator_state_'.$status].'</h2> <hr>';

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
    global $txt;

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_view_projects'] .' </h2>';

    template_show_list('list_of_projects');
}

function template_view_log()
{

    global $scripturl, $context, $txt;

    $session_var = $context['session_var'];
	$session_id = $context['session_id'];

    template_button_strip(array(array(
        'text' => 'delegator_del_log',
        'image' => 'to_do_add.gif',
        'lang' => true,
        'url' => "$scripturl?action=delegator;sa=del_log;$session_var=$session_id",
        'active'=> true
        )), 'right'
    );

    echo '<h2 style="font-size:1.5em" > '. $txt['delegator_view_log'] .' </h2><hr>';
    //echo '<a href="index.php?action=delegator;sa=del_log;'. $session_var . '=' . $session_id . '" class="button_submit">' . $txt['delegator_del_log'] . '</a>';

    template_show_list('log'); // ko bomo odkomentirali veliki del v Delegator.php, se odkomentira tudi to in vuala, bodo taski...
}



//##############################//##############################
//##############################//##############################
//##############################//##############################

function template_edit_task()
{

    global $scripturl, $context, $txt;

    $id_task = (int) $_GET['id_task'];

    $row_task = task_info($id_task);

    $workers = workers_on_task($id_task);
    $delegates = "";
    foreach ($workers as $id_worker => $real_name){
        $delegates .=
            '<div id="suggest_to-add_' . $id_worker . '">
                <input name="member_add[]" value="' . $id_worker . '" type="hidden">
                <a href="index.php?action=profile;u=' . $id_worker . '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">' . $real_name . '</a>
                <img src="Themes/default/images/pm_recipient_delete.gif" alt="Delete Item" title="Delete Item" onclick="return oAddMemberSuggest.deleteAddedItem(' . $id . ');">
            </div>';
    }

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=edit_task_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_edit_task">
        <input type="hidden" name="id_task" value ="'.$id_task.'" />
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
                <dl class="delegator_edit_task">';
    echo dl_form("name",$txt['delegator_task_name'], "input-text", $row_task['task_name'], "input_text", 50, 255 );
    echo dl_form("description", $txt['delegator_task_desc'], "textarea", $row_task['description'], "", 3, 30);
    echo dl_form("deadline", $txt['delegator_deadline'], "input-text", $row_task['deadline'], "input_text kalender", 8, 100);
    echo dl_form("user", $txt['delegator_task_delegates'], "delegates", $delegates, "div");
    echo dl_form("priority", $txt['delegator_priority'], "priority", getPriorities($row_task, $txt), "reset");
    echo dl_form("id_proj", $txt['delegator_project_name'], "projects", $row_task['id_proj'], "");
    echo '</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
        <a href="'.$_SERVER['HTTP_REFERER'].'">Back</a>
				<input type="submit" name="submit" value="', $txt['delegator_edit_task'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
		</div>

    ' . generateMemberSuggest("to-add", "user-list", "member_add") .  '
		</form>
	</div><br />';
    // @gismoe zamenjal sem vrstni red forme in memberSuggesta. Je to okej?
}

function template_end_task()
{
	global $scripturl, $context, $txt, $settings;

	//$session_var = $context['session_var'];
	//$session_id = $context['session_id'];

    $id_task = (int) $_GET['id_task'];
    $row_task = task_info($id_task);

    if ($row_task['priority'] == 0) {
		$pimage = 'warning_watch';
    } elseif ($row_task['priority'] == 1) {
		$pimage = 'warn';
    } elseif ($row_task['priority'] == 2) {
		$pimage = 'warning_mute';
    }

	// Imam task claiman?
	$member_id = (int) $context['user']['id'];
    $workers = workers_on_task($id_task);
	$delegates = "&nbsp;&nbsp;&nbsp;(\_/)<br />=(^.^)= &#268;upi<br />&nbsp;(\")_(\")";
    // var_dump($workers);die;

	if (count($workers)) {
		$delegates = implode(", ", $workers);
	}

    //////////////////////////////////////////////////////////////
    // kako podvojeno iz view_task
    //////////////////////////////////////////////////////////////

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
			<dl class="delegator_edit_task">';
    echo dl_view("name", $txt['delegator_task_name'], "title", $row_task['task_name'], "");
    echo dl_view("author", $txt['delegator_task_author'], "link", "view_worker;id_member=".$row_task['id_author'] , $row_task['author']);
    echo dl_view("project_name", $txt['delegator_project_name'], "link", 'view_project;id_proj='.$row_task['id_proj'] , $row_task['project_name']);
    echo dl_view("creation_date", $txt['delegator_creation_date'], "date", $row_task['creation_date'] , "format-time");
    echo dl_view("deadline", $txt['delegator_deadline'], "date", $row_task['deadline'] , "relative-time");
    echo dl_view("delegates", $txt['delegator_task_delegates'], "description", $delegates, "");
    echo dl_view("description", $txt['delegator_task_desc'], "description", $row_task['description'], "");
    echo dl_view("priority", $txt['delegator_priority'], "priority", $settings['images_url'].'/'.$pimage .'.gif', $txt['delegator_priority_' . $row_task['priority']]);
    echo dl_view("state", $txt['delegator_state'], "description", $row_task['state'] , "");

	echo	'
			 </dl>
			 <br />
			</div>
			<span class="botslice"><span></span></span>
		</div>
    	</div><br />
    ';

    /***************************************************************************
     ****************** Konec Podvojitve ***************************************
     **************************************************************************/

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=end_task_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_end_task">
        <input type="hidden" name="id_task" value ="'.$id_task.'" />
        <input type="hidden" name="id_proj" value ="'.$row_task['id_proj'].'" />
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_edit_task">';
    echo dl_form("end_comment", $txt['delegator_end_task_comment'], "textarea", "", "textarea", 3, 30);
    // echo dl_form("state", $txt['delegator_state'], "state", $row_task['state'], "");
    // echo dl_form("state", $txt['delegator_end_task_state'], "state", 2, 2 );
	echo '     <dt>
		            	<label for="state">'.$txt['delegator_end_task_state'].' </label>
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
        ' . generateMemberSuggest('to-add', 'user-list',  'member_add') . '
		</form>
	</div><br />';
}

/**
 * Templete for editing all tasks.
 *
 * Needs workers, project and task from database.
 */
function template_super_edit() {
    global $scripturl, $context, $txt;

    $id_task = (int) $_GET['id_task'];

    $row_task = task_info($id_task);

    // Delegirani uporabniki
    $workers = workers_on_task($id_task);

    $delegates = "";
    foreach ($workers as $id => $name){
        $delegates .=
            '<div id="suggest_to-add_' . $id . '">
                <input name="member_add[]" value="' . $id . '" type="hidden">
                <a href="index.php?action=profile;u=' . $id . '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">' . $name . '</a>
                <img src="Themes/default/images/pm_recipient_delete.gif" alt="Delete Item" title="Delete Item" onclick="return oAddMemberSuggest.deleteAddedItem(' . $id . ');">
            </div>';
    }

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=super_edit_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_edit_task">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl class="delegator_edit_task">';
    echo dl_form("name",$txt['delegator_task_name'], "input-text", $row_task['task_name'], "input_text", 50, 255 );
    echo dl_form("description", $txt['delegator_task_desc'], "textarea", $row_task['description'], "", 3, 30);
    echo dl_form("deadline", $txt['delegator_deadline'], "input-text", $row_task['deadline'], "input_text kalender", 8, 100);
    echo dl_form("user", $txt['delegator_task_delegates'], "delegates", $delegates, "div");
    echo dl_form("priority", $txt['delegator_priority'], "priority", getPriorities($row_task, $txt), "reset");
    echo dl_form("id_proj", $txt['delegator_project_name'], "projects", $row_task['id_proj'], "");
    echo dl_form("state", $txt['delegator_state'], "state", $row_task['state'], 0);
    echo dl_form("start_date", $txt['delegator_task_start_date'], "input-text", $row_task['start_date'], "kalender", 8, 100);
    echo dl_form("end_date", $txt['delegator_task_end_date'], "input-text", $row_task['end_date'], "kalender", 8, 100);
    echo dl_form("end_comment", $txt['delegator_task_end_comment'], "textarea", $row_task['end_comment'], "", 3, 30);
    echo '</dl>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
                <input type="hidden" name="id_proj" value ="'.$row_task['id_proj'].'" />
                <input type="hidden" name="id_task" value ="'.$id_task.'" />
				<br />
				<input type="submit" name="submit" value="', $txt['delegator_edit_task'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
		</div>
        ' . generateMemberSuggest('to-add', 'user-list', 'member_add') . '
		</form>
	</div><br />';
}

function template_edit_project()
{
    global $scripturl, $context, $txt;

    ///////////////////////////////////////////////////////////////
    //////// Copied mostly from add_project ///////////////////////
    ///////////////////////////////////////////////////////////////

    $id_proj = (int) $_GET['id_proj']; // here is the problem!
    $row_p = project_info($id_proj);

	echo '
	<div id="container">
		<div class="cat_bar">
			<h3 class="catbg"><span class="left"></span>
				', $context['page_title'], '
			</h3>
		</div>
		<form action="', $scripturl, '?action=delegator;sa=edit_project_save" method="post" accept-charset="', $context['character_set'], '" name="delegator_edit_project_save">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="delegator_edit_task">';
    echo dl_form("name", $txt['delegator_project_name'], "input-text", $row_p['proj_name'], "input_text", 50, 255 );
    echo dl_form("description", $txt['delegator_project_desc'], "textarea", $row_p['description'], "textarea", 3, 30);
    echo dl_form("start", $txt['delegator_project_start'], "input-text", $row_p['start'], "input_text kalender", 8, 100);
    echo dl_form("end", $txt['delegator_project_end'], "input-text", $row_p['end'], "input_text kalender", 8, 100);

    echo	'	</dl>
                <br />
                <input type="hidden" name="id_coord" value="'.$row_p['id_coord'].'" />
                <input type="hidden" name="id_proj" value="'.$id_proj.'" />
				<input type="submit" name="submit" value="', $txt['delegator_edit_project'], '" class="button_submit" />
			</div>
			<span class="botslice"><span></span></span>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
		</form>
	</div><br />';

}
