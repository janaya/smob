<?php

parse_str($_SERVER['QUERY_STRING']);

require_once(dirname(__FILE__).'/lib/smob/SMOB.php');

if(!SMOBTools::check_config()) {
    //$installer = new SMOBInstaller();
    //$installer->go();
    echo SMOBInstaller::view();

} else {
    require_once(dirname(__FILE__)."/config/config.php");
    
    if (isset($t)) {

        // followings
        if($t == 'following') {

            if(isset($a)) {

                // Add a new following
                if($a == 'add') {
                
                    //TODO: all the logic here should go in another file...
                    error_log("adding following",0);
                    if(!SMOBAuth::check()) die();
                    // FIXME: remote_user check deactivated, as can't load https://localhost...
                    //$remote_user = SMOBTools::remote_user($u);
                    error_log("remote user: ".$remote_user,0);
                    $remote_user = $u;
                    if(!$remote_user) {
                        SMOBTemplate::header('');
                        print "<a href='$u'>$u</a> is not a valid Hub, user cannot be added";
                        SMOBTemplate::footer();
                    } else {
                        // @TODO: check that the user were not already a following?
                        // Store the new relationship in local repository
                        //$local_user = SMOBTools::user_uri();
                        $local_user = ME_URL_PATH;
                        $follow = "<$local_user> sioc:follows <$remote_user> . ";
                        $local = "INSERT INTO <".FOLLOWINGS_GRAPH_URL."> { $follow }";
                        SMOBStore::query($local);
                        error_log("DEBUG: Added following $remote_user with the query: $local",0);
                        SMOBTemplate::header('');

                        // Subscribe to the hub

                        // Get the Publisher (following) Hub
                        $remote_user_feed = $remote_user.FEED_URL_PATH;
                        //FIXME: simplexml_load_file(): I/O warning : failed to load external entity
                        //$xml = simplexml_load_file($remote_user_feed);
                        //if(count($xml) == 0)
                        //    return;
                        //$link_attributes = $xml->channel->link->attributes();
                        //if($link_attributes['rel'] == 'hub') {
                        //    $hub_url = $link_attributes['href'];
                        //}
                        $hub_url = HUB_URL_SUBSCRIBE;
                        $callback_url = urlencode(CALLBACK_URL_PATH);
                        error_log("callback url: ".$callback_url,0);
                        $feed = urlencode($remote_user_feed);
                        error_log("topic url: ".$feed,0);

                        // Not using subscriber library as it does not allow async verify
                        // Reusing do_curl function
                        $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=subscribe&hub.verify=sync&hub.callback=$callback_url&hub.topic=$feed&hub.foaf=".PRIVATE_PROFILE_URL_PATH);
                        // all good -- anything in the 200 range
                        if (substr($result[2],0,1) == "2") {
                            error_log("DEBUG: Successfully subscribed to topic $remote_user_feed using hubsub $hub_url",0);
                        }
                        error_log("DEBUG: Server answer: ".join(' ', $result),0);

                        print "<a href='$remote_user'>$remote_user</a> was added to your following list and was notified about your subscription";
                        SMOBTemplate::footer();

                        // And ping to update the followers list remotely
                        // @TODO: This will work only if $u doesn't have /me or something in the end
                        //$ping = str_replace("me", "add", $ping)."/follower/$local_user";
                        $ping = SMOBTools::host($remote_user)."/add/follower/$local_user";
                        $result = SMOBTools::do_curl($ping);
                        error_log("DEBUG: Sent $ping",0);
                        error_log("DEBUG: Server answer: ".join(' ', $result),0);
                     }

                }elseif($a == 'remove') {
                    // Remove a follower
                    if(!SMOBAuth::check()) die();
                    $u = str_replace('http:/', 'http://', $u);
                    // @TODO: has it sense that the user remove a follower?. Then the follower should also be notified to remove the current user as following
                    // Instead, when the request comes from another user removing a following, the action will not be run as there is not authentication
                    $remote_user = $u;
                    $local_user = ME_URL_PATH;
                    $follow = "<$remote_user> sioc:follows <$local_user> . ";
                    $local = "DELETE FROM <".FOLLOWINGS_GRAPH_URL."> { $follow }";
                    SMOBStore::query($local);
                    error_log("DEBUG: Removed follower $remote_user with the query: $local",0);
                }

            // $a not set
            } else {
                error_log("followings?",0);
            }

        } elseif($t == 'follower') {

            if(isset($a)) {

                if($a == 'add') {
                    // Add a new follower
                    $u = str_replace('http:/', 'http://', $u);
                    // @TODO: has it sense that the user add a follower?. Then the follower should also be notified to add the current user as following
                    // When the request comes from another user adding a following, the action is ran as there authentication is not needed

                    $remote_user = SMOBTools::remote_user($u);
                    if(!$remote_user) die();
                    $local_user = SMOBTools::user_uri();
                    $follow = "<$remote_user> sioc:follows <$local_user> . ";
                    $local = "INSERT INTO <".SMOB_ROOT."data/followers> { $follow }";
                    error_log("DEBUG: Added follower $remote_user with the query $local", 0);
                    SMOBStore::query($local);
                } elseif($a == 'remove') {
                    // Remove a following
                    if(!SMOBAuth::check()) die();
                    $u = str_replace('http:/', 'http://', $u);
                    $remote_user = $u;
                    $local_user = SMOBTools::user_uri();
                    $follow = "<$local_user> sioc:follows <$remote_user> . ";
                    $local = "DELETE FROM <".SMOB_ROOT."data/followings> { $follow }";
                    SMOBStore::query($local);
                    error_log("DEBUG: Removed following $remote_user with the query: $local",0);

                     // And ping to update the followers list remotely
                    //$ping = str_replace("me","remove", $u)."/follower/$local_user";
                    $ping = SMOBTools::host($u)."/remove/follower/$local_user";
                    error_log("DEBUG: Sent $ping",0);
                    $result = SMOBTools::do_curl($ping);
                    error_log("DEBUG: Server answer: ".join(' ', $result),0);

                    // Unsubscribe to the Hub

                    //@TODO: following Hub should be stored?,
                    $remote_user_feed = $remote_user.FEED_URL_PATH;
                    $xml = simplexml_load_file($remote_user_feed);
                    if(count($xml) == 0)
                        return;
                    $link_attributes = $xml->channel->link->attributes();
                    if($link_attributes['rel'] == 'hub') {
                        $hub_url = $link_attributes['href'];
                    }
                    $callback_url = urlencode(SMOB_ROOT."callback");
                    $feed = urlencode($remote_user_feed);
                    $result = SMOBTools::do_curl($hub_url, $postfields = "hub.mode=unsubscribe&hub.verify=async&hub.callback=$callback_url&hub.topic=$feed");
                    // all good -- anything in the 200 range
                    if (substr($result[2],0,1) == "2") {
                            error_log("DEBUG: Successfully unsubscribed to topic $remote_user_feed using hubsub $hub_url",0);
                    }
                    error_log("DEBUG: Server answer: ".join(' ', $result),0);

                    //print "<a href='$remote_user'>$remote_user</a> was deleted from your following list and your subscription was removed";
                    SMOBTemplate::footer();
                }

            } else {
                //$a is not set
            }
        
        // disable authentication as webid not working in sempush
        // FIXME: insecure
        } elseif($t == 'rss_owner') {
            SMOBFeedRDF::rss();
        }

        // function to server RDF inside item content
        // is not being used for now
    //     elseif($t == 'rssrdf_owner') {
    //         header ("Content-type: text/xml");
    //         $tweet = new SMOBFeed();
    //         $tweet->rssrdf();
    //     }

        elseif($t == 'sparql') {
            if($_POST) {
                SMOBTools::checkAccess($_POST);
            }
            $ep = ARC2::getStoreEndpoint(SMOBTools::arc_config());
            $ep->go();

        // callback script to process the incoming hub POSTs
        } elseif($t == 'callback') {
            if (array_key_exists('REMOTE_HOST',$_SERVER)) {//&& ($_SERVER['REMOTE_HOST'] == HUB_URL_SUBSCRIBE)) {
                error_log("DEBUG: request from host: ".$_SERVER['REMOTE_HOST']);
            }
            if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
                error_log("DEBUG: request from user_agent: ".$_SERVER['REMOTE_HOST']);
            }
            // Getting hub_challenge from hub after sending it post subscription
            if(isset($_GET["hub_challenge"])) {
                    // send confirmation to the hub
                    echo $_GET["hub_challenge"];
                    error_log("DEBUG: received and sent back hub challenge:".$_GET["hub_challenge"],0);
            }
            // Getting feed updates from hub
            elseif(isset($_POST)) {
                    $post_data = file_get_contents("php://input");
                    error_log("DEBUG: received POST with content: $post_data",0);
                    SMOBFeedRDF::get_rdf_from_rss($post_data) ;
            }
            elseif(isset($_DELETE)) {
                $post_data = file_get_contents("php://input");
                    error_log("DEBUG: received DELETE with content: $post_data",0);
            }
            elseif(isset($_PUT)) {
                $post_data = file_get_contents("php://input");
                    error_log("DEBUG: received PUT with content: $post_data",0);
            }
        // same as callback funcion, just to check subscriptions with a different callback URL
    //    } elseif($t == 'callbackrdf') {
    //        if (array_key_exists('REMOTE_HOST',$_SERVER)) {//&& ($_SERVER['REMOTE_HOST'] == HUB_URL_SUBSCRIBE)) {
    //            error_log("DEBUG: request from host: ".$_SERVER['REMOTE_HOST']);
    //        }
    //        if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
    //            error_log("DEBUG: request from user_agent: ".$_SERVER['REMOTE_HOST']);
    //        }
    //        // Getting hub_challenge from hub after sending it post subscription
    //        if(isset($_GET["hub_challenge"])) {
    //                // send confirmation to the hub
    //                echo $_GET["hub_challenge"];
    //                error_log("DEBUG: received and sent back hub challenge:".$_GET["hub_challenge"],0);
    //        }
    //        // Getting feed updates from hub
    //        elseif(isset($_POST)) {
    //                $post_data = file_get_contents("php://input");
    //                error_log("DEBUG: received POST with content: $post_data",0);
    //                SMOBFeedRDF::get_rdf_from_rss($post_data) ;
    //        }
    //        elseif(isset($_DELETE)) {
    //            $post_data = file_get_contents("php://input");
    //                error_log("DEBUG: received DELETE with content: $post_data",0);
    //        }
    //        elseif(isset($_PUT)) {
    //            $post_data = file_get_contents("php://input");
    //                error_log("DEBUG: received PUT with content: $post_data",0);
    //        }
      } elseif($t == 'private') {
          error_log("private",0);
          if(isset($a)) {
              if($a == 'edit'){
                echo PrivateProfile::edit();
              }
          } else {
              echo PrivateProfile::view();
          }

      // FIXME: remove
      //} elseif($t == 'test') {
      //    echo PrivacyPreferences::test_delete();

      //} elseif($t == 'foaf') {
      //  error_log("/foaf not authenticated");
      //  header ("Content-type: text/xml");
      //  //FIXME: change by the user public FOAF
      //  $rssfile = fopen('/var/www/smob/julia_smob.rdf', 'r');
      //  $rss = fread($rssfile, filesize('/var/www/smob/julia_smob.rdf'));
      //  fclose($rssfile);
      //  echo($rss);

      } elseif($t == 'privacy') {
          //if(isset($a)) {
          //    if($a == 'edit') {
          //      echo PrivacyPreferences::edit();
          //    } elseif($a == 'add') {
          //      echo PrivacyPreferences::add();
          //    }
          //} else {
              echo PrivacyPreferences::view();
          //}
      } elseif($t == 'logout'){
          session_start();
          session_destroy();
          header( 'Location: '.SMOB_ROOT) ;
      } else {
          error_log("default action, calling SMOB",0);
          $smob = new SMOB($t, $u, $p);
          $smob->reply_of($r);
          $smob->go();
      }

    // $t is not set
    } else {
          error_log("default action, calling SMOB",0);
          $smob = new SMOB('posts', $u, $p);
          $smob->reply_of($r);
          $smob->go();
    }
}
