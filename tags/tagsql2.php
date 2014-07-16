<?php
/*
Tagging System
Version 2.2
by:vbgamer45
http://www.smfhacks.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

  
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}tags
(id_tag mediumint(8) NOT NULL auto_increment,
tag tinytext NOT NULL,
approved tinyint(4) NOT NULL default '0',
PRIMARY KEY  (id_tag))");
  



 // Create the tags Log
$smcFunc['db_query']('', "
CREATE TABLE IF NOT EXISTS {db_prefix}tags_log
(id int(11) NOT NULL auto_increment,
id_tag mediumint(8) unsigned NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
id_member mediumint(8) unsigned NOT NULL default '0',
PRIMARY KEY  (id),
KEY id_tag (id_tag),
KEY id_topic (id_topic),
KEY id_member (id_member)
) Engine=MyISAM");

// Insert the settings
if (!isset($modSettings['smftags_set_mintaglength']))
{

	updateSettings(array(
		'smftags_set_mintaglength' => 3,
		'smftags_set_maxtaglength' =>30,
		'smftags_set_maxtags' => 10,
	));
}

if (!isset($modSettings['smftags_set_cloud_tags_per_row']))
{

	updateSettings(array(
		'smftags_set_cloud_tags_per_row' => 5,
		'smftags_set_cloud_tags_to_show' => 50,
		'smftags_set_cloud_max_font_size_precent' =>250,
		'smftags_set_cloud_min_font_size_precent' => 100,
	));
}

// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");

?>