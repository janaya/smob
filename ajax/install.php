<?php

require_once(dirname(__FILE__).'/../lib/smob/SMOB.php');

// TODO: insert the allowed hubs, insert the privacy settings for the profile

if(file_exists($arc)) {
    include_once($arc);
}

if(isset($_GET['cmd'])){
    $cmd = $_GET['cmd'];

    if($cmd =="create-db") {
        echo createDB();
    }
    elseif($cmd =="setup-smob") {
        setupSMOB();
    }

    elseif($cmd =="setup-user") {
        setupUser();
    }

    else echo "<p>Sorry, I didn't understand the command ...</p>";
}

function createDB(){
    $host =  urldecode($_GET['host']);
    $name =  $_GET['name'];
    $user = $_GET['user'];
    $pwd = $_GET['pwd'];
    $store = $_GET['store'];

    $ret = "<p>";
    $dbExists = false;

    $con = mysql_connect($host, $user, $pwd); // try to connect
    if (!$con){
      die('Could not connect: ' . mysql_error());
    }

    $dblist = mysql_list_dbs($con); // check if the database already exists
    while ($row = mysql_fetch_object($dblist)) {
         $db = $row->Database;
         if ($db == $name) $dbExists = true;
    }

    if(!$dbExists) {
        if (mysql_query("CREATE DATABASE " . $name, $con)) {
            return createStore($host, $name, $user, $pwd, $store);
        }
        else {
            $ret .= "Error creating database: " . mysql_error() . "</p>";
        }
    }
    else $ret .= "The database '$name' already exists. We are ready to create an RDF store.</p>";

    mysql_close($con);

    return createStore($host, $name, $user, $pwd, $store);

}

function createStore($host, $name, $user, $pwd, $store_name){

    include_once(dirname(__FILE__).'/../lib/arc/ARC2.php');

    $config = array(
      'db_host' => $host,
      'db_name' => $name,
      'db_user' => $user,
      'db_pwd' => $pwd,
      'store_name' => $store_name,
    );

    $store = ARC2::getStore($config);

    if (!$store->isSetUp()) {
        $store->setUp();
        print "<p>Database correctly set-up.</p>";
    } else {
        print "<p>The store was already set up.</p>";
    }

    // write databsed information in the config file
    $config = "<?php

include_once(dirname(__FILE__).'/../lib/arc/ARC2.php');
include_once(dirname(__FILE__).'/../lib/xmlrpc/lib/xmlrpc.inc');

define('DB_HOST', '$host');
define('DB_NAME', '$name');
define('DB_USER', '$user');
define('DB_PASS', '$pwd');
define('DB_STORE', '$store_name');

";
    $f = fopen(dirname(__FILE__).'/../config/config.php', 'w');
    fwrite($f, $config);
    fclose($f);

}

function setupSMOB() {
    $smob_root = $_GET['smob_root'];
    if(substr($smob_root, -1) != '/') {
        $smob_root = "$smob_root/";
    }
    $smob_hub = (isset($_GET['smob-hub']) ? $_GET['smob-hub'] : 'http://localhost:8080');
    $smob_hub_publish = (isset($_GET['smob-hub']) ? $_GET['smob-hub']."/publish" : 'http://localhost:8080/publish');
    $smob_hub_subscribe = (isset($_GET['smob-hub']) ? $_GET['smob-hub']."/subscribe" : 'http://localhost:8080/subscribe');
    $smob_websocket_host = (isset($_GET['smob-websocket-host']) ? $_GET['smob-websocket-host'] : 'localhost');
    $smob_websocket_port = (isset($_GET['smob-websocket-port']) ? $_GET['smob-websocket-port'] : '8081');
    $purge = $_GET['purge'];
    $feed_path = realpath('./../rss/rss.xml'); //'/var/www/smob/rss/rss.xml'
    error_log($feed_path,0);
    $me_url = $smob_root.'me';
    $me_feed_url = $smob_root.'me/rss';
    $q = "INSERT
        { <$smob_hub> a push:SemanticHub . 
        <$me_feed_url>  push:has_hub <$smob_hub>
        <$me_feed_url>  push:has_owner <$me_url>}";
    
    $config = "

define('PURGE', '$purge');

define('HUB_URL', '$smob_hub');
define('HUB_URL_PUBLISH', '$smob_hub_publish');
define('HUB_URL_SUBSCRIBE', '$smob_hub_subscribe');

define('FEED_FILE_PATH', $feed_path);

define('WSSERVER_HOST', '$smob_websocket_host');
define('WSSERVER_PORT', '$smob_websocket_port');

define('SMOB_ROOT', '$smob_root');

define('JS_URL', SMOB_ROOT.'js/');
define('IMG_URL', SMOB_ROOT.'img/');
define('CSS_URL', SMOB_ROOT.'css/');
define('AJAX_URL', SMOB_ROOT.'ajax/');
define('TWITTER_URL', SMOB_ROOT.'data/twitter/');
define('TAGGING_URL', SMOB_ROOT.'tagging/');

define('STARS_URI', SMOB_ROOT.'data/stars');

define('ME_URL', SMOB_ROOT.'me');

define('ME_URI', SMOB_ROOT.'data/me');

define('ME_FEED_URL', SMOB_ROOT.'me/rss');
define('ME_FEEDRDF_URL', SMOB_ROOT.'me/rssrdf');

define('AUTH_URL', SMOB_ROOT.'auth');
define('POST_URL', SMOB_ROOT.'post/');

define('POST_URI', SMOB_ROOT.'data/post/');

define('DATA_URL', SMOB_ROOT.'data/');
define('DELETE_URL', SMOB_ROOT.'delete/');
define('USER_URL', SMOB_ROOT.'user/');
define('RESOURCE_URL', SMOB_ROOT.'resource/');
define('REPLIES_URL', SMOB_ROOT.'replies');
define('MAP_URL', SMOB_ROOT.'map');
define('SPARQL_URL', SMOB_ROOT.'sparql');
define('REMOVE_URL', SMOB_ROOT.'remove/');

define('FOLLOWINGS_URI', SMOB_ROOT.'data/followings');

define('FOLLOWINGS_URL', SMOB_ROOT.'followings');
define('FOLLOWERS_URL', SMOB_ROOT.'followers');

define('FOLLOWING_ADD_URL', SMOB_ROOT.'add/following/');
define('FOLLOWER_ADD_URL', SMOB_ROOT.'add/follower/');

define('FOLLOWER_REMOVE_URL', SMOB_ROOT.'remove/follower/');
define('FOLLOWING_REMOVE_URL', SMOB_ROOT.'remove/following/');

define('FOLLOWING_PING', SMOB_ROOT.'ping/following/');
define('FOLLOWER_PING_URL', SMOB_ROOT.'ping/follower/');

define('CALLBACK_URL', SMOB_ROOT.'callback');
define('CALLBACKRDF_URL', SMOB_ROOT.'callbackrdf');

define('PRIVATE_PROFILE_EDIT_URL', SMOB_ROOT.'private/edit/');
define('PRIVATE_PROFILE_URL', SMOB_ROOT.'private');

define('PRIVACY_PREFERENCES_ADD_URL', SMOB_ROOT.'privacy/add/');
define('PRIVACY_PREFERENCES_EDIT_URL', SMOB_ROOT.'privacy/edit/');
define('PRIVACY_PREFERENCES_URL', SMOB_ROOT.'privacy');

define('PRIVACY_PREFERENCES_URI', SMOB_ROOT.'data/privacy_preferences');
define('PRIVACY_PREFERENCE_URI', SMOB_ROOT.'data/privacy_preference/');

define('LOGOUT_URL', SMOB_ROOT.'logout');
";

    $f = fopen(dirname(__FILE__).'/../config/config.php', 'a');
    fwrite($f, $config);
    fclose($f);

    print "<p>Settings saved.</p>";
}


function setupUser() {

    include_once(dirname(__FILE__).'/../config/config.php');

    $foaf_uri = $_GET['foaf_uri'];

    $twitter_read = ($_GET['twitter_read'] == 'on') ? 1 : 0;
    $twitter_post = ($_GET['twitter_post'] == 'on') ? 1 : 0;

    $twitter_login = $_GET['twitter_login'];
    $twitter_pass = $_GET['twitter_pass'];

    $auth = $_GET['auth'];

    if($foaf_uri) {
        if(!SMOBTools::checkFoaf($foaf_uri)) {
            print "<p>An error occurred with your FOAF URI. <b>Please ensure that it dereferences to an RDF file and that this file contains information about your URI.<b><br/>You will have to <a href='$smob_root'>restart the install process<a/></p>";
            unlink(dirname(__FILE__).'/../config/config.php');
            die();
        }
    } else {
        if(!$foaf_uri) {
            $foaf_uri = ME_URL.'#id';
            $username = $_GET['username'];
            $depiction = $_GET['depiction'];
            $profile = "
INSERT INTO <".ME_URL."> {
<$foaf_uri> a foaf:Person ;
    foaf:name \"$username\" ;
    foaf:depiction <$depiction> .
}";
            SMOBStore::query($profile);
        }
    }

    $config = "
define('FOAF_URI', '$foaf_uri');

define('TWITTER_READ', '$twitter_read');
define('TWITTER_POST', '$twitter_post');

define('TWITTER_USER', '$twitter_login');
define('TWITTER_PASS', '$twitter_pass');

define('AUTH', '$auth');

?>";

    $f = fopen(dirname(__FILE__).'/../config/config.php', 'a');
    fwrite($f, $config);
    fclose($f);

    print "<p>Enjoy, you can now access your <a href='.'>SMOB Hub</a> !<br/>
    Log-in using the 'Authenticate' link and start writing microblog po.<br/>
    Also, be sure to restrict access to the <code>config/</code> directory.</p>";
}

?>
