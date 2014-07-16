<?php
//SMFHacks.com
//Table SQL

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
        include_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Create Tags Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}tags
(ID_TAG mediumint(8) NOT NULL auto_increment,
tag tinytext NOT NULL,
approved tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID_TAG))", __FILE__, __LINE__);

//Create Tags Log
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}tags_log
(ID int(11) NOT NULL auto_increment,
ID_TAG mediumint(8) unsigned NOT NULL default '0',
ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
PRIMARY KEY  (ID),
KEY id_tag (id_tag),
KEY id_topic (id_topic),
KEY id_member (id_member)

)", __FILE__, __LINE__);


//Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_mintaglength', '3')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_maxtaglength', '30')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_maxtags', '10')", __FILE__, __LINE__);

// Tags Cloud settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_tags_per_row', '5')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_tags_to_show', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_max_font_size_precent', '250')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_min_font_size_precent', '100')", __FILE__, __LINE__);

db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

?>