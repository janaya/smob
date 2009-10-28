<?php

require_once(dirname(__FILE__).'/../../config.php');

require_once(dirname(__FILE__).'/../../lib/foaf-ssl/libAuthentication.php');

$auth = getAuth();
$do_auth = $auth['certRSAKey'];
$is_auth = $auth['isAuthenticated'];
$auth_uri = $auth['subjectAltName'];

if($do_auth) {
	if ($is_auth != 1 || $auth_uri != $foaf_uri) {
		print "Wrong credentials, try again !";
		die();
	} else {
		print "Welcome home, $auth_uri !";
	}
}

?>

<!-- XXX hack to make browsers send the posting as utf-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<script type="text/javascript" src="http://jqueryui.com/jquery-1.3.2.js"></script>
<script type="text/javascript" src="http://jqueryui.com/ui/ui.core.js"></script>
<script type="text/javascript" src="publish.js"></script>
<script type="text/javascript">
$(function() {
	$("#publish").click(function () {
		publish();
	});
});
</script>

<h2>New content</h2>
<form>
<textarea name="content" id="content"></textarea>
<br/>
<fieldset><legend>Servers to ping</legend>
<?php
foreach($servers as $server => $key) {
	echo "<input type='checkbox' name='servers[]' value='$server' />$server<br/>";
}
if($twitter_user && $twitter_pass) {
  echo "<input type='checkbox' name='twitter' value='twit' />" .
       "Twitter as $twitter_user<br/>";
}
if($laconica) {
  foreach($laconica as $service => $user) {
    $username = $user['user'];
    echo "<input type='checkbox' name='laconica[$service]' value='twit' />" .
       "$service as $twitter_user<br/>";
  }
}

?>
</fieldset>
</form>

<button id="publish">SMOB it!</button>

<div id="smob-publish" style="display: none;">
	<em>Request sent...</em>
</div>
