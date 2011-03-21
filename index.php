<?php 

parse_str($_SERVER['QUERY_STRING']);

require_once(dirname(__FILE__).'/lib/smob/SMOB.php'); 
require_once(dirname(__FILE__).'/lib/subscriber.php');

if(!SMOBTools::check_config()) {
	$installer = new SMOBInstaller();
	$installer->go();
} else {
	require_once(dirname(__FILE__)."/config/config.php");	
	// Follower / followings
	if($a && $a == 'add') {
		$u = str_replace('http:/', 'http://', $u);
		// Add a new follower
		if($t == 'follower') {
			$remote_user = SMOBTools::remote_user($u);
			if(!$remote_user) die();
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "INSERT INTO <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
		} 
		// Add a new following
		elseif($t == 'following') {
			if(!SMOBAuth::check()) die();
			$remote_user = SMOBTools::remote_user($u);
			if(!$remote_user) {
				SMOBTemplate::header('');
				print "<a href='$u'>$u</a> is not a valid Hub, user cannot be added";
				SMOBTemplate::footer();	
			} else {
		        // @TODO: check that the user were not already a following? 
			    // Store the new relationship in local repository
			    $local_user = SMOBTools::user_uri();			
			    $follow = "<$local_user> sioc:follows <$remote_user> . ";
			    $local = "INSERT INTO <".SMOB_ROOT."data/followings> { $follow }";
			    SMOBStore::query($local);
			    SMOBTemplate::header('');

			    // Add subscription to the hub

                $hub_url = "http://pubsubhubbub.appspot.com";
                $callback_url = urlencode(SMOB_ROOT."callback");
                $feed = urlencode($remote_user.'/rss');
                error_log($callback_url,0);
                error_log($feed,0);

    //                // create a new subscriber
    //                $s = new Subscriber($hub_url, $callback_url);
    //                // subscribe to a feed
    //                $s->subscribe($feed);

                $ch = curl_init($hub_url);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"hub.mode=subscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                $response = curl_exec($ch);
                $info = curl_getinfo($ch);
        
                // all good -- anything in the 200 range 
                if (substr($info['http_code'],0,1) == "2") {
                    error_log($response,0);
                }

			    print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
			    SMOBTemplate::footer();	
			    // And ping to update the followers list remotely
			    $ping = "$u/add/follower/$local_user";
			    $result = SMOBTools::do_curl($ping);
			    error_log(join(' ', $result),0);
			 }
		}
	}
	elseif($a && $a == 'remove') {
		if(!SMOBAuth::check()) die();
		$u = str_replace('http:/', 'http://', $u);
		// Remove a follower
		if($t == 'follower') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "DELETE FROM <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
			//@TODO: notify the following?
		} 
		// Remove a following
		elseif($t == 'following') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";			
			$local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
			SMOBStore::query($local);
			//@TODO: notify the follower?
		}
		header("Location: ".SMOB_ROOT."${t}s");
	}	
	elseif($t == 'rss_owner') {
		header ("Content-type: text/xml");
		$tweet = new SMOBFeed();
		$tweet->rss();
	}
	elseif($t == 'sparql') {
		if($_POST) {
			SMOBTools::checkAccess($_POST);
		}
		$ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
		$ep->go();

	// callback script to process the incoming hub POSTs
	} elseif($t == 'callback') {
	            // Getting hub_challenge from hub after sending it post subscription
                if(isset($_GET["hub_challenge"])) {
                        // send confirmation to the hub
                        echo $_GET["hub_challenge"];
                        error_log($_GET["hub_challenge"],0);
                }
                // Getting feed updates from hub
                if(isset($_POST)) {
                        //@TODO: parse feed
#                        error_log($HTTP_RAW_POST_DATA,0);
#                        error_log(join(' ', $_POST),0);
                        $post_data = file_get_contents("php://input");
                        error_log($post_data,0);
                        
                        // Parsing the new feeds to load in the triple store
                        // post data will contain something like:
                        // <item rdf:about="http://smob.rhizomatik.net/post/2011-03-21T18:33:21+01:00">
                        // and this subscriber must store the rdf in a url like:
                        // http://smob.rhizomatik.net/data/2011-03-21T18:33:21+01:00
                        $xml = simplexml_load_string($post_data);
                        error_log($xml,0);
                        if(count($xml) == 0)
                            return;

                        foreach($xml->item as $item) {
                            error_log($item,0);
                            $link = (string) $item->link;
                            error_log($link,0);
                            $link = str_replace("post", "data", $link);
                            error_log($link,0);
                            $result = SMOBStore::query("LOAD <$link>");
                            error_log(join(' ', $result),0);
                        }
                }
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
