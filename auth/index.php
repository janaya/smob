<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOBAuth.php'); 
require_once(dirname(__FILE__).'/../lib/smob/SMOBStore.php');
require_once(dirname(__FILE__).'/../lib/smob/SMOBTools.php'); 
require_once(dirname(__FILE__).'/../config/config.php'); 

//error_log(print_r($_FILES,1),0);
//error_log(print_r($_POST,1),0);
//error_log(print_r($_GET,1),0);

error_log("/auth going to authenticate");
$requested_url = trim(($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'/');
$requested_url = (isset($_GET["redirect"]) ? $_GET["redirect"] : false);
error_log('requested_url '. $requested_url,0);

if (SMOBAuth::grant()) {
    error_log("/auth authentication done");
    
    if($requested_url) {
      error_log('redirecting to '. $requested_url,0);
      header("Location: ". $requested_url);
      exit;
    } else {
      header("Location: ".SMOB_ROOT);
      exit;
    }
} else {
    header("Location: ".SMOB_ROOT);
    exit;
}


?>
