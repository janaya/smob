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
			error_log($local, 0);
			SMOBStore::query($local);
		} 
		// Add a new following
		elseif($t == 'following') {
			if(!SMOBAuth::check()) die();
			$remote_user = SMOBTools::remote_user($u);
		    error_log("u: ".$u,0);
		    error_log("remote_user: ".$remote_user,0);
			if(!$remote_user) {
				SMOBTemplate::header('');
				print "<a href='$u'>$u</a> is not a valid Hub, user cannot be added";
				SMOBTemplate::footer();	
			} else {
		        // @TODO: check that the user were not already a following? 
			    // Store the new relationship in local repository
			    error_log("storing to local repository");
			    $local_user = SMOBTools::user_uri();			
			    $follow = "<$local_user> sioc:follows <$remote_user> . ";
			    $local = "INSERT INTO <".SMOB_ROOT."data/followings> { $follow }";
			    SMOBStore::query($local);
			    SMOBTemplate::header('');

			    // Subscribe to the hub

                // Get the Publisher (following) Hub
			    $remote_user_feed = $remote_user.FEED_PATH;
			    $xml = simplexml_load_file($remote_user_feed);
                if(count($xml) == 0)
                    return;
                $link_attributes = $xml->channel->link->attributes();
                if($link_attributes['rel'] == 'hub') {
                    $hub_url = $link_attributes['href'];
			        error_log("hub url:",0);
                    error_log($hub_url,0);
                }
                $callback_url = urlencode(SMOB_ROOT."callback");
                $feed = urlencode($remote_user_feed);
                error_log($callback_url,0);
                error_log($feed,0);
                
                // Not using subscriber library as it does not allow async verify
                // Reusing do_curl function
                $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=subscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                // all good -- anything in the 200 range 
                if (substr($result[2],0,1) == "2") {
                    error_log("Succesfullyl subscribed",0);
                }
                error_log(join(' ', $result),0);

			    print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
			    SMOBTemplate::footer();	
			    
			    // And ping to update the followers list remotely
			    // @TODO: This will work only if $u doesn't have /me or something in the end
			    //$ping = str_replace("me", "add", $ping)."/follower/$local_user";
			    $ping = SMOBTools::host($remote_user)."/add/follower/$local_user";
			    error_log($ping,0);
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
		    error_log("u: ".$u,0);
		    error_log("remote_user: ".$remote_user,0);
			$local_user = SMOBTools::user_uri();
			$follow = "<$remote_user> sioc:follows <$local_user> . ";	
			$local = "DELETE FROM <".SMOB_ROOT."data/followers> { $follow }";
			SMOBStore::query($local);
			error_log($local, 0);
		} 
		// Remove a following
		elseif($t == 'following') {
			$remote_user = $u;
			$local_user = SMOBTools::user_uri();
			$follow = "<$local_user> sioc:follows <$remote_user> . ";			
			$local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
			SMOBStore::query($local);
			
			 // And ping to update the followers list remotely
		    //$ping = str_replace("me","remove", $u)."/follower/$local_user";
		    $ping = SMOBTools::host($u)."/remove/follower/$local_user";
			error_log($ping,0);
		    $result = SMOBTools::do_curl($ping);
		    error_log(join(' ', $result),0);
		    
		    // Unsubscribe to the Hub

            //@TODO: following Hub should be stored?, 
		    $remote_user_feed = $remote_user.FEED_PATH;
		    $xml = simplexml_load_file($remote_user_feed);
            if(count($xml) == 0)
                return;
            $link_attributes = $xml->channel->link->attributes();
            if($link_attributes['rel'] == 'hub') {
                $hub_url = $link_attributes['href'];
		        error_log("hub url:",0);
                error_log($hub_url,0);
            }
            $callback_url = urlencode(SMOB_ROOT."callback");
            $feed = urlencode($remote_user_feed);
            error_log($callback_url,0);
            error_log($feed,0);
            $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=unsubscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
            // all good -- anything in the 200 range 
            if (substr($result[2],0,1) == "2") {
                error_log("Sucesfully unsubscribed",0);
            }
            error_log(join(' ', $result),0);

	        //print "<a href='$remote_user'>$remote_user</a> was deleted from your following list and your subscription was removed";
	        SMOBTemplate::footer();	
            
		}
		header("Location: ".SMOB_ROOT."${t}s");
	}	
	elseif($t == 'rss_owner') {
		header ("Content-type: text/xml");
		$tweet = new SMOBFeed();
		$tweet->rss();
	}
	elseif($t == 'rssrdf_owner') {
		header ("Content-type: text/xml");
		$tweet = new SMOBFeed();
		$tweet->rssrdf();
	}
	elseif($t == 'sparql') {
		if($_POST) {
			SMOBTools::checkAccess($_POST);
		}
		$ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
		$ep->go();

	// callback script to process the incoming hub POSTs
	} elseif($t == 'callback') {
	    error_log("in callback",0);
	    if (array_key_exists('REMOTE_HOST',$_SERVER)) {//&& ($_SERVER['REMOTE_HOST'] == HUB_URL_SUBSCRIBE)) {
	        error_log($_SERVER['REMOTE_HOST']);
	    }
        // Getting hub_challenge from hub after sending it post subscription
        if(isset($_GET["hub_challenge"])) {
                // send confirmation to the hub
                echo $_GET["hub_challenge"];
                error_log("hub challenge:".$_GET["hub_challenge"],0);
        }
        // Getting feed updates from hub
        elseif(isset($_POST)) {
	            error_log("in callback POST",0);
                $post_data = file_get_contents("php://input");
                error_log($post_data,0);
                //SMOBTools::rss2rdf($post_data);
                SMOBTools::get_rdf_from_rss($post_data) ;
        }
        elseif(isset($_DELETE)) {
            $post_data = file_get_contents("php://input");
            error_log($post_data,0);
        }
        else(isset($_PUT)) {
            $post_data = file_get_contents("php://input");
            error_log($post_data,0);
        }
	} else {
		$smob = new SMOB($t, $u, $p);
		$smob->reply_of($r);
		$smob->go();
	}
}
