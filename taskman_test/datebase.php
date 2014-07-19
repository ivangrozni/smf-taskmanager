<?php
// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $db_prefix;

// Make sure that we have the package database functions.
if (!array_key_exists('db_create_table', $smcFunc))
	db_extend('packages');

$tables = array(
	'tasks' => array(
		'columns' => array(
			array(
				'name' => 'id_task', // task ID
				'type' => 'int',
				'size' => '10',
				'auto' => true,
			),
            array(
				'name' => 'id_sec', // sec ID of task for diferent users
				'type' => 'int',
				'size' => '10',
			),
			array(
				'name' => 'id_autor', // Autor of task
				'type' => 'int',
				'size' => '10',
			),
			array(
				'name' => 'task', // Name of task
				'type' => 'char',
                'size' => '50',
			),
            array(
				'name' => 'descript', // Description of task
				'type' => 'char',
                'size' => '250',
			),
			array(
				'name' => 'start_date', // When the task should start
				'type' => 'date',
			),
            array(
				'name' => 'end_date', // When the task should end
				'type' => 'date',
			),
			array(
				'name' => 'priority', // Task Priority (0: Low, 1: Normal, 2: High)
				'type' => 'tinyint',
				'size' => '2',
				'default' => 1,
			),
			array(
				'name' => 'is_did', // Hrr.. I did this Task, didn't I?
				'type' => 'tinyint',
				'size' => '2',  // (0: empty, 1: in progress, 2: finished, 3: canceled)
				'default' => 0,
			),
            array(
				'name' => 'member', // executor
				'type' => 'int',
                'size' => '10',
			),
            array(
				'name' => 'finish_date', // Date of completion of task
				'type' => 'date',
			),
            array(
				'name' => 'comment', // Comment of executor after finishing task
				'type' => 'char',
                'size' => '50',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_todo'),
			),
		),
	),
);

// Mmmm. Okay. Let's go!
foreach ($tables as $table => $data)
	$smcFunc['db_create_table']($db_prefix . $table, $data['columns'], $data['indexes']);
	
if (SMF == 'SSI')
	echo 'I created the table ! ;)';

?>