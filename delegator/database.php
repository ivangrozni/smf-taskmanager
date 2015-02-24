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
				'name' => 'id', // Task id
				'type' => 'int',
				'size' => '11',
				'auto' => true,
			),
			array(
				'name' => 'id_proj', // Parent project
				'type' => 'int',
				'size' => '11',
			),
			array(
				'name' => 'id_author', // Author (member)
				'type' => 'int',
				'size' => '11'
			),
			array(
				'name' => 'name', // Name of task
				'type' => 'varchar',
				'size' => '50',
			),
			array(
				'name' => 'description', // Description of task
				'type' => 'varchar',
				'size' => '250',
			),
			array(
				'name' => 'creation_date', // Date of creation
				'type' => 'date',
			),
			array(
				'name' => 'deadline', // Date when the task is due
				'type' => 'date',
			),
			array(
				'name' => 'priority', // Task priority
				'type' => 'tinyint',
				'size' => '2',
				'default' => 1,
			),
			array(
				'name' => 'state', // Current state of task
				'type' => 'tinyint',
				'size' => '2',
				'default' => 1,
			),
			array(
				'name' => 'start_date', // Date when work on the task was started
				'type' => 'date',
			),
			array(
				'name' => 'end_date', // Date when the task was finished
				'type' => 'date',
			),
			array(
				'name' => 'end_comment',
				'type' => 'varchar',
				'size' => '250'
			)
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id'),
			),
		),
	),
	'projects' => array(
		'columns' => array(
			array(
				'name' => 'id', // Project id
				'type' => 'int',
				'size' => '11',
				'auto' => true,
			),
			array(
				'name' => 'id_coord', // Project coordinator (member)
				'type' => 'int',
				'size' => '11',
			),
			array(
				'name' => 'name', // Name of the project
				'type' => 'varchar',
				'size' => '50',
			),
			array(
				'name' => 'description', // Description of the project
				'type' => 'varchar',
				'size' => '250',
			),
			array(
				'name' => 'start', // Project start
				'type' => 'date',
			),
			array(
				'name' => 'end', // Project end
				'type' => 'date',
			),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id'),
			),
		),
	),
	'workers' => array(
		'columns' => array(
			array(
				'name' => 'id', // Worker entry ID
				'type' => 'int',
				'size' => '11',
				'auto' => true,
			),
			array(
				'name' => 'id_member', // Member ID
				'type' => 'int',
				'size' => '11',
			),
			array(
				'name' => 'id_task', // Task ID
				'type' => 'int',
				'size' => '11',
			),
            array(
                'name' => 'status',
                'type' => 'int',
                'size' => '3',
                    ),
		),
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id'),
			),
		),
	)
);

foreach ($tables as $table => $data)
	$smcFunc['db_create_table']($db_prefix . $table, $data['columns'], $data['indexes']);

if (SMF == 'SSI')
	echo 'I created the table ! ;)';

?>