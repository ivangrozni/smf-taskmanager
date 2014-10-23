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

// First of all, we make sure we are accessing the source file via SMF so that people can not directly access the file. 
if (!defined('SMF'))
    die('Hack Attempt...');

function ToDo()
{
    global $context, $txt, $scripturl; // potrebne variable
    
    //isAllowedTo('view_todo'); // kaj bomo s tem?
    
    loadTemplate('Delegator');
    //Fourth, Come up with a page title for the main page
    $context['page_title'] = $txt['delegator'];
    
    $subActions = array(
        'delegator' => 'delegator_main', //tukaj bo pregled nad projekti in nedokoncanimi zadolzitvami
        'personal_view' => 'personal_view', //zadolzitve uporabnika
        'add_proj' => 'add_proj',
        'add_task' => 'add_task',
        'acc_task' => 'acc_task',
        'end_task' => 'end_task',
        'edit_task' => 'edit_task',
        'edit_proj' => 'edit_proj',
        'view_task' => 'view_task',
        'view_proj' => 'view_proj',
            // Kasneje bomo dodali se razlicne view-je - prikaz casovnice...
            //'ToDo' => 'ToDoMain',
            //'add' => 'add',
            //'add2' => 'add2',
            //'delete' => 'delete',
            //'did' => 'didChange',
    );
    
    if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
        $sub_action = 'delegator';
    else
        $sub_action = $_REQUEST['sa'];
    
    //Fifth, define the navigational link tree to be shown at the top of the page.
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=delegator',
        'name' => $txt['delegator']
    );

    $subActions[$sub_action]();
//Sixth, begin doing all the stuff that we want this action to display 
// Store the results of this stuff in the $context array. 
// This action's template(s) will display the contents of $context.

}


function delegator_main() 
{
    // tukaj bi rad prikazal projekte in zadolzitve - mogoce je pomembnejse najprej zadolzitve
    global $context, $scripturl, $sourcedir, $smcFunc, $txt;
    
    //isAllowedTo('view_todo'); // kaj bomo s tem?
    
    $list_options = array(
        //'id' => 'list_todos',
        'id' => 'list_tasks',
        'items_per_page' => 30,
        'base_href' => $scripturl . '?action=delegator',
        'default_sort_col' => 'duedate',
        'get_items' => array(
            // FUNKCIJE !!! uredi querry
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

				return $tasks;
                                '), 
            'params' => array(
                'id_member' => $context['user']['id'],
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
            'name' => array(
                'header' => array(
                    'value' => $txt['name'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						if (strtolower($row[\'subject\']) == \'i love grafitus\')
							return parse_bbc($row[\'subject\']) . \' <br /><em>grafitus said: "Me too you... :)))"</em>\';

						return parse_bbc($row[\'subject\']);
					'),
                ),
                'sort' =>  array(
                    'default' => 'subject',
                    'reverse' => 'subject DESC',
                ),
            ),
            'duedate' => array(
                'header' => array(
                    'value' => $txt['task_due_time'],
                ),
				'data' => array(
                                    'function' => create_function('$row', '
						$row[\'duedate\'] = strtotime($row[\'duedate\']);
						return timeformat($row[\'duedate\'], \'%d %B %Y, %A\');
					'),
                                    'style' => 'width: 20%; text-align: center;',
				),
                'sort' =>  array(
					'default' => 'duedate',
######################################
# Subs-List.php 64. sat�r is wrong!!!
######################################
					'reverse' => 'duedate DESC',
                ),
            ),
            'priority' => array(
                'header' => array(
                    'value' => $txt['priority'],
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

						return \'<img src="\'. $settings[\'images_url\']. \'/\'. $image. \'.gif" alt="" /> \' . $txt[\'to_do_priority\' . $row[\'priority\']];
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
            'actions' => array(
                'header' => array(
                    'value' => $txt['to_do_actions'],
                ),
                'data' => array(
                    'function' => create_function('$row', '
						global $context, $settings, $scripturl;

						return \'<a href="\'. $scripturl. \'?action=delegator;sa=did;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/\'. ($row[\'is_did\'] ? \'package_old\' : \'package_installed\'). \'.gif" alt="" /></a><a href="\'. $scripturl. \'?action=delegator;sa=delete;id=\'. $row[\'id\']. \';\' . $context[\'session_var\'] . \'=\' . $context[\'session_id\'] . \'"><img src="\'. $settings[\'images_url\']. \'/icons/quick_remove.gif" alt="" /></a>\';
					'),
                    'style' => 'width: 10%; text-align: center;',
                ),
            ),
        ),
    );
    
    require_once($sourcedir . '/Subs-List.php');
    
    createList($list_options);
}

function add()
{
    global $smcFunc, $scripturl, $context, $txt;

    //isAllowedTo('add_new_todo');
    
    $context['sub_template'] = 'add';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=todo;sa=add',
        'name' => $txt['to_do_add']
    );
    $context['html_headers'] .= '
	<style type="text/css">
		dl.todo_add
		{
			margin: 0;
			clear: right;
			overflow: auto;
		}
		dl.todo_add dt
		{
			float: left;
			clear: both;
			width: 30%;
			margin: 0.5em 0 0 0;
		}
		dl.todo_add label
		{
			font-weight: bold;
		}
		dl.todo_add dd
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

function add2()
{
    global $smcFunc, $context;
    
    //isAllowedTo('add_new_todo');
    
    checkSession();
    
    $id_member = $context['user']['id'];
    
    $subject = strtr($smcFunc['htmlspecialchars']($_POST['subject']), array("\r" => '', "\n" => '', "\t" => ''));
    $due_date = $smcFunc['htmlspecialchars']($_POST['duet3'] . '-' . $_POST['duet1'] . '-' . $_POST['duet2']);

    if ($smcFunc['htmltrim']($_POST['subject']) === '' || $smcFunc['htmltrim']($_POST['duet2']) === '')
        fatal_lang_error('to_do_empty_fields', false);

    $smcFunc['db_insert']('', '{db_prefix}tasks',
    array(
        'id_member' => 'int', 'subject' => 'string', 'duedate' => 'date', 'priority' => 'int', 'is_did' => 'int',
    ),
    array(
        $id_member, $subject, $due_date, $_POST['priority'], 0,
    ),
    array('id_todo')
    );
    
    redirectexit('action=delegator');
}

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