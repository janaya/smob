<?php


require_once(dirname(__FILE__)."/../lib/smob/SMOBTools.php");
require_once(dirname(__FILE__)."/../lib/smob/SMOBStore.php");
//require_once(dirname(__FILE__)."/../config/config.php");
require_once(dirname(__FILE__)."/../lib/smob/PrivateProfile.php");

$triples = $_POST['triples'];
error_log($triples,0);

//error_log(SMOB_ROOT,0);
//$query = "DELETE FROM <".ME_URL."> ";
//$res = SMOBStore::query($query);
////error_log(var_dump($res, 1), 0);
//$query = "INSERT INTO <".ME_URL."> { $triples }";
//error_log($query, 0);
//$res = SMOBStore::query($query);
////error_log(var_dump($res, 1), 0);

PrivateProfile::save($triples);
print "Your private profile has been stored...\n";
error_log("private profile stored");
