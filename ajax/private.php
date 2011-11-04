<?php


require_once(dirname(__FILE__)."/../lib/smob/SMOBTools.php");
require_once(dirname(__FILE__)."/../lib/smob/SMOBStore.php");
require_once(dirname(__FILE__)."/../config/config.php");

$triples = $_POST['triples'];
error_log($triples,0);
error_log(SMOB_ROOT,0);
$query = "DELETE FROM <".ME_URL_PATH."> ";
$res = SMOBStore::query($query);
//error_log(var_dump($res, 1), 0);
$query = "INSERT INTO <".ME_URL_PATH."> { $triples }";
error_log($query, 0);
$res = SMOBStore::query($query);
//error_log(var_dump($res, 1), 0);
print "Your private profile has been stored...\n";
error_log("private profile stored");
