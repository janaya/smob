<?php


require_once(dirname(__FILE__)."/../lib/smob/SMOBTools.php");
require_once(dirname(__FILE__)."/../lib/smob/SMOBStore.php");
//require_once(dirname(__FILE__)."/../config/config.php");
require_once(dirname(__FILE__)."/../lib/smob/PrivacyPreferences.php");

$triples = $_POST['triples'];
$graph = $_POST['graph'];
error_log($triples,0);

////$query = "DELETE FROM <".PRIVACY_PREFERENCE_URI_PATH.date('c')."> ";
////$res = SMOBStore::query($query);
//$query = "INSERT INTO <".$graph."> { $triples }";
////$query = $_POST['query'];
////error_log($query, 0);
//$res = SMOBStore::query($query);
//error_log(print_r($res, 1), 0);
//error_log("privacy preferences stored");
PrivacyPreferences::save($graph, $triples);
print "Your privacy preferences have been stored...\n";
