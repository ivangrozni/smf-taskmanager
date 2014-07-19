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
	'to_dos' => array(
		'columns' => array(
			array(
				'name' => 'id_todo', // To-Do ID
				'type' => 'int',
				'size' => '10',
				'auto' => true,
			),
			array(
				'name' => 'id_member', // Who owns?
				'type' => 'int',
				'size' => '10',
			),
			array(
				'name' => 'subject', // Subject
				'type' => 'text',
			),
			array(
				'name' => 'duedate', // Due date
				'type' => 'date',
			),
			array(
				'name' => 'priority', // To-Do Priority (0: Low, 1: Normal, 2: High)
				'type' => 'tinyint',
				'size' => '2',
				'default' => 1,
			),
			array(
				'name' => 'is_did', // Hrr.. I did this To-Do, didn't I?
				'type' => 'tinyint',
				'size' => '2',
				'default' => 0,
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