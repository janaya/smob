<?php


require_once(dirname(__FILE__)."/../lib/smob/SMOBTools.php");
require_once(dirname(__FILE__)."/../lib/smob/SMOBStore.php");
require_once(dirname(__FILE__)."/../config/config.php");

$triples = $_POST['triples'];
error_log($triples,0);
$query = "DELETE FROM <".SMOB_ROOT."ppo> ";
$res = SMOBStore::query($query);
$query = "INSERT INTO <".SMOB_ROOT."ppo> { $triples }";

//$query = $_POST['query'];
//error_log($query, 0);

$res = SMOBStore::query($query);
error_log(print_r($res, 1), 0);
print "Your privacy preferences have been stored...\n";
error_log("privacy preferences stored");
