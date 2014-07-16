<?php
/*
Tagging System
Version 2.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2009 http://www.samsonsoftware.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');

// Show the iframe with the uninstall
$modName = base64_encode("Tagging System");
echo '<iframe src ="http://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<b>Other Helpful Mods</b><br />
<a href="http://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a> - A fully featured gallery for SMF<br />
<a href="http://www.smfhacks.com/download-system-pro.php" target="_blank">Downloads System Pro</a><br />
<a href="http://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a><br />
<a href="http://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a><br />

<a href="http://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a><br />

';


?>