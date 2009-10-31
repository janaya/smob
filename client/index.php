<?php 

// SCRIPT_URI isn't present on all servers, so we do this instead:
$authority = "http://" . $_SERVER['HTTP_HOST'];
$root = $authority . dirname(dirname($_SERVER['SCRIPT_NAME'])); 

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 

if(!file_exists(dirname(__FILE__)."/../config.php")) {
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'../install';
	header("Location: $url");
} 

require_once(dirname(__FILE__)."/../config.php");

is_auth();

parse_str($_SERVER['QUERY_STRING']);
$u = str_replace('http:/', 'http://', $u);

if($u) {
	if($t == 'post') {
		$content = show_uri();	
	} elseif($t == 'resource') {
		$content = show_resource($u);
	} elseif($t == 'user') {
		$p = get_person($u);
		$content = do_person($p, $u);
	} elseif($t == 'replies') {
	//TODO
	}
}

else {
	$page = $_GET['page'];
	if (!$page) {
		$page = 0;
	}
	$content = show_posts($page);
}

smob_go($content);

?>
