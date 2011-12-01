<?php


require_once(dirname(__FILE__)."/../lib/smob/SMOBTools.php");
require_once(dirname(__FILE__)."/../lib/smob/SMOBStore.php");
//require_once(dirname(__FILE__)."/../config/config.php");
require_once(dirname(__FILE__)."/../lib/smob/PrivacyPreferences.php");

$graph = $_POST['graph'];
error_log("privace_del.php?graph=",0);
error_log($graph,0);
PrivacyPreferences::delete($graph);
print "Your privacy preference have been removed...\n";
