<?php

/* 
    The main class - controls the action, launches all the stuff
*/

require_once(dirname(__FILE__).'/SMOBAuth.php');
// require_once(dirname(__FILE__).'/SMOBFeed.php');
require_once(dirname(__FILE__).'/SMOBFeedRDF.php');
require_once(dirname(__FILE__).'/SMOBInstaller.php');
require_once(dirname(__FILE__).'/SMOBPost.php');
require_once(dirname(__FILE__).'/SMOBPostList.php');
require_once(dirname(__FILE__).'/SMOBStore.php');
require_once(dirname(__FILE__).'/SMOBTemplate.php');
require_once(dirname(__FILE__).'/SMOBTools.php');
require_once(dirname(__FILE__).'/SMOBTweet.php');
require_once(dirname(__FILE__).'/SMOBURIWrapper.php');
require_once(dirname(__FILE__).'/PrivateProfile.php');
require_once(dirname(__FILE__).'/PrivacyPreferences.php');
require_once(dirname(__FILE__).'/User.php');

class SMOB {
    
    var $type = 'posts';
    var $page = 1;
    var $uri;
    var $publisher;
    var $reply_of;
    var $commands = array('data', 'delete', 'followings', 'followers', 'map', 'post', 'posts', 'replies', 'resource', 'user', 'userReplies');
    
    // Construct - save parameters and setup the RDF store
    public function __construct($type, $uri, $page) {
        error_log("SMOB::construct",0);
        error_log("type: $type",0);
        error_log("uri: $uri",0);
        error_log("page: $page",0);
        if($type) {
            $this->type = $type;
        }
        if($uri) {
            $uri = str_replace('http:/', 'http://', $uri);
            $this->uri = $uri;    
        }
        if($page) {
            $this->page = $page;
        }
        $this->publish = SMOBAuth::check();
        error_log("publisher: $publisher",0);
    }
    
    // Setup the reply_of elemnents
    public function reply_of($reply_of) {
        error_log("SMOB::reply_of",0);
        $this->reply_of = $reply_of;
    }
    
    // Main method - analyse the query type, get the content and render it
    public function go() {
        error_log("SMOB::go",0);
        if(in_array($this->type, $this->commands)) {
            $func = $this->type;
            $content = $this->$func();
            //error_log("SMOB::go content:",0);
            //error_log($content,0);
        } else {
            $content = "Cannot interpret that command";
        }
        // Passer ce publish parametre dans une list particuliere
        SMOBTemplate::header($this->publish, $this->reply_of, $this->type == 'map');
        print $content;
        SMOBTemplate::footer();
    }
                
    // Browsing a single post
    private function post() {
        error_log("SMOB::post",0);
        $post = new SMOBPost(SMOBTools::get_post_uri($this->uri, 'post'));
        return $post->render();
    }
    
    // Delete a post
    private function delete() {
        error_log("SMOB::delete",0);
        if(!SMOBAuth::check()) die();
        $post = new SMOBPost(SMOBTools::get_post_uri($this->uri, 'post'));
        $post->delete();
        header("Location: ".SMOB_ROOT);
    }
    
    // RDF data for a single post
    private function data() {
        error_log("SMOB::data",0);
        $post = new SMOBPost(SMOBTools::get_post_uri($this->uri, 'post'));
        return $post->raw();
    }

    // Browsing a list of posts from a user
    private function user() { 
        error_log("SMOB::user",0);
        if(!$this->uri) {
            $this->uri = FOAF_URI;
        } 
        return $this->posts(); 
    }
    
    // Browsing a list of posts
    private function userreplies() {
        error_log("SMOB::userreplies",0); 
        return $this->posts(); 
    }
    private function resource() {
        error_log("SMOB::resource",0); 
        return $this->posts(); 
    }
    private function map() {
        error_log("SMOB::map",0);
        return $this->posts(); 
    }
    private function posts() {
        error_log("SMOB::posts",0);
        $class = 'SMOBPostList'.ucfirst($this->type);
        $list = new $class($this->uri, $this->page);
        return $list->render();        
    }
        
    private function followings() {
        error_log("SMOB::followings",0);
        return SMOBTemplate::users($this->type, SMOBTools::followings()); 
    }

    private function followers() {
        error_log("SMOB::followers",0);
        return SMOBTemplate::users($this->type, SMOBTools::followers());
    }

}

