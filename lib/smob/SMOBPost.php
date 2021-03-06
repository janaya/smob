<?php

/*
    Representing posts
*/
    
class SMOBPost {
    
    var $uri;
    var $data;
    var $ts;
    var $content;
    var $triples = array();
    
    public function __construct($uri = null, $data = null) {    
        if($uri) {
            $this->uri = $uri;
            if($data) {
                $this->data = $data;
                $this->process_content();
            } else {
                $this->process();
            }
        }
    }
    
    // Get the post data from the RDF store
    private function process() {
        $uri = $this->uri;
        $query = "
SELECT DISTINCT ?content ?author ?creator ?date ?presence ?reply_of ?reply_of_of ?depiction ?name ?location ?locname
WHERE {
<$uri> rdf:type sioct:MicroblogPost ;
    sioc:content ?content ;
    sioc:has_creator ?creator ;
    foaf:maker ?author ;
    dct:created ?date .
?presence opo:customMessage <$uri> .
    OPTIONAL { <$uri> sioc:reply_of ?reply_of. }
    OPTIONAL { ?reply_of_of sioc:reply_of <$uri> . }
    OPTIONAL { ?author foaf:depiction ?depiction. } 
    OPTIONAL { ?author foaf:img ?depiction . }
    OPTIONAL { ?author foaf:name ?name . }
    OPTIONAL {
        ?presence opo:currentLocation ?location .
        ?location rdfs:label ?locname .
    }
} ";
        $res = SMOBStore::query($query);
        $this->data = $res[0];
        $this->process_content();
    }
    
    // Process the content to get #tags and @replies and embeds sioc:topic in it
    private function process_content() {
        // Process hyperlinks
        $this->data['content'] = preg_replace( "`(http|ftp)+(s)?:(//)((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"\\0\" target=\"_blank\">http://\\4</a>", $this->data['content']); 
        // Process & and < symbols
        $this->data['content'] = str_replace('&', '&amp;', $this->data['content']);
        $this->data['content'] = str_replace('<', '&lt;', $this->data['content']);
        // Process users
        $users = $this->get_users();
        if($users) {
            foreach($users as $t) {
                $user = $t['user'];
                $name = $t['name'];
                $r = "<span class=\"topic\" rel=\"sioc:addressed_to\" href=\"$user\"><a href=\"$user\" target=\"_blank\">$name</a></span>";
                $this->data['content'] = str_replace("@$name", "@$r", $this->data['content']);
            }
        }
        // Process tags
        $tags = $this->get_tags();
        if($tags) {
            foreach($tags as $t) {
                $tag = $t['tag'];
                $resource = $t['uri'];
                $enc = SMOBTools::get_uri($resource, 'resource');
                $sigma = "http://sig.ma/search?q=$resource&raw=1";
                $r = "<span class=\"topic\" rel=\"moat:taggedWith sioc:topic ctag:isAbout\" href=\"$resource\"><a href=\"$enc\">$tag</a></span>";
                $this->data['content'] = str_replace("#$tag", "#$r", $this->data['content']);
                $this->data['content'] = str_replace("L:$tag", "L:$r", $this->data['content']);
            }
        }
        return;
        }
    
//     // Render the post as RSS 1.0 item
//     public function rss() {
//         $uri = $this->uri;
//         $graph = $this->graph();
//         $content = $this->data['content'];
//         $ocontent = strip_tags($content);
//         $date = $this->data['date'];
//         $name = $this->data['name'];
//         //Adding the RDF to content 
//         $turtle = $this->turtle();
//         //$content = "INSERT INTO <$graph> { $turtle }";
//         $content = $turtle;
//         $item = "    
// <item rdf:about=\"$uri\">
//     <title>$ocontent</title>
//     <link>$uri</link>
//     <description>$ocontent</description>
//     <dc:creator>$name</dc:creator>
//     <dc:date>$date</dc:date>
//     <content:encoded><![CDATA[$content]]></content:encoded>
// </item>
// ";
//         return $item;
//     }
    
//     public function create_access_space() {
        
//     }
    
//     // Render the post as RSS 1.0 item with RDF in content tag 
//     // Function not used now, as rss is is adding the RDF
//     public function rssrdf() {
//         $uri = $this->uri;
//         $graph = $this->graph();
//         $content = $this->data['content'];
//         $ocontent = strip_tags($content);
//         $date = $this->data['date'];
//         $name = $this->data['name'];

//         ////when user_agent is the Hub, delete the post marked to be deleted
//         //// Has the post been deleted?
//         //$query = "ASK { GRAPH <$graph> {<$uri> <http://smob.me/ns#Status> \"DELETED\"^^<http://www.w3.org/2001/XMLSchema#string> .}}";
//         //$res = SMOBStore::query($query, true);
//         //error_log($res,0);

//         //if ($res == 1) {
//         //    $content = "DELETE FROM <$graph>";
//         //    // If the Hub is getting the post to be deleted
//         //    if (isset($_SERVER['REMOTE_HOST']) && $_SERVER['REMOTE_HOST'] == HUB_URL) {

//         //        // Real delete
//         //        $res = SMOBStore::query($content);
//         //        error_log($res,0);
//         //    }
//         //} else {
//         //Adding the RDF to content 
//         $graph = $this->graph();
//         $turtle = $this->turtle();
//         //$content = "INSERT INTO <$graph> { $turtle }";
//         $content = $turtle;
        
//         $item = "    
// <item rdf:about=\"$uri\">
//     <title>$ocontent</title>
//     <link>$uri</link>
//     <description>$ocontent</description>
//     <dc:creator>$name</dc:creator>
//     <dc:date>$date</dc:date>
//     <content:encoded><![CDATA[$content]]></content:encoded>
// </item>
// ";
//         return $item;
//     }


    public function add_to_rss_file() {
        error_log("DEBUG: adding to rss file", 0);
        $uri = $this->uri;
        // the data array is only generated when the post is loaded from the triple store
        // but it is not generated when the post is generated from user interface
        // it has no sense to get it from the triple store if it is still in memory
        //$content = $this->data['content'];
        //$ocontent = strip_tags($content);
        //$date = $this->data['date'];
        //$name = $this->data['name'];
        
        $date = isset($this->ts) ? $this->ts: $this->data['date'];;
        $content = isset($this->content) ? $this->content : $this->data['content']; 
        $ocontent = strip_tags($content);
        // @FIXME: can not get the name from new post data model, although it is stored in the triples
        //$name = SMOBTools::uri(SMOBTools::user_uri());
        $name = "";
        $name = $this->data['name'];
        
        //Adding the RDF to content 
        $turtle = $this->turtle();
        $access_spaces = $this->get_access_spaces();
        $item = SMOBFeedRDF::create_rss_item($uri, $ocontent, $date, $name, $turtle, $access_spaces);
        SMOBFeedRDF::add_rss_item($item);
    }

    public function delete_from_rss_file() {
        $uri = $this->uri;
        SMOBFeedRDF::delete_rss_item($uri);
    }

    // Render the post in RDFa/XHTML
    public function render() {
        global $sioc_nick, $count;

        $uri = $this->uri;
        
        $content = $this->data['content'];
        $ocontent = strip_tags($content);
        $author = $this->data['author'];
        $creator = $this->data['creator'];
        $date = $this->data['date'];
        $name = $this->data['name'];
        $reply_of = $this->data['reply_of'];
        $reply_of_of = $this->data['reply_of_of'];
        $presence = $this->data['presence'];
        $location = $this->data['location'];
        $locname = $this->data['locname'];
        $star = $this->get_star();

        $pic = SMOBTools::either($this->data['depiction'], IMG_URL.'avatar-blank.jpg');
        $class = strpos($uri, SMOB_ROOT) !== FALSE ? "post internal" : "post external";
        $ht .= "<div about=\"$presence\" rel=\"opo:customMessage\">\n";

        $ht .= "<div class=\"$class\" typeof=\"sioct:MicroblogPost\" about=\"$uri\">\n";

        $ht .= "<span style=\"display:none;\" rel=\"sioc:has_container\" href=\"".SMOB_ROOT."\"></span>\n";

        $ht .= "<img about=\"$author\" rel=\"foaf:depiction\" href=\"$pic\" src=\"$pic\" class=\"depiction\" alt=\"Depiction for $name\"/>";
        $ht .= "  <span class=\"content\" property=\"content:encoded\">$content</span>\n";
        $ht .= "  <span style=\"display:none;\" property=\"sioc:content\">$ocontent</span>\n";
        $ht .= '  <div class="infos">';
        $ht .= "  by <a class=\"author\" rel=\"foaf:maker\" href=\"$author\"><span property=\"foaf:name\">$name</span></a> - \n";
        if($location) {
            $ht .= "  location: <span about=\"$presence\"><a rel=\"opo:currentLocation\" href=\"$location\"><span property=\"rdfs:label\">$locname</span></a></span><br/>\n";    
        } else {
            $ht .= "  location: <span about=\"$presence\">unspecified</span><br/>\n";    
        }
        $ht .= "  <div style=\"margin: 2px;\"></div> ";
        $ht .= "  <div id=\"star$count\" class=\"rating\">&nbsp;</div>";        
        $ht .= "  <span style=\"display:none;\" rel=\"sioc:has_creator\" href=\"$creator\"></span>\n";
        $ht .= "  <a href=\"$uri\" class=\"date\" property=\"dcterms:created\">$date</a>\n";
        if(strpos($uri, 'http://twitter.com/') !== FALSE) {
            $ex = explode('/', $uri);
            $data = TWITTER_URL. $ex[5];
        } else { 
            $data = str_replace('post', 'data', $uri);
        }
        $ht .= " [<a href=\"$data\">RDF</a>]\n";
        if(SMOBAuth::check()) {
            if(strpos($uri, SMOB_ROOT) !== FALSE) {
                $ex = explode('/', $uri);
                error_log("DEBUG: post delete path: ".join(' ', $ex),0);
                error_log("DEBUG: post uri: ".$uri,0);
                $action = DELETE_URL.$ex[5];
                // the previous line doesn't work as the post is in the position 4
                $action = str_replace('post', 'delete', $uri);
                error_log("DEBUG: is going to be run the action: ".$action,0);
                $ht .= " [<a href=\"$action\" onclick=\"javascript:return confirm('Are you sure ? This cannot be undone.')\">Delete post</a>]";            
            } 
            $action = $this->get_publish_uri();
            $ht .= " [<a href=\"$action\">Post a reply</a>]\n";
        }
        if ($reply_of) {
            $action = SMOBTools::get_uri($reply_of, 'post');
            $ht .= " [<a href=\"$action\">Replied message</a>]\n";
        }
        if ($reply_of_of) {
            $action = SMOBTools::get_uri($reply_of_of, 'post');
            $ht .= " [<a href=\"$action\">Replies</a>]\n";
        }        
        $ht .= '  </div>';
        $ht .= '</div>';
        $ht .= "</div>\n\n";
        $ht .= "<script type=\"text/javascript\">
$(document).ready(function(){
    $('#star$count').rating('ajax/star.php?u=$uri', {maxvalue: 1, curvalue: $star});
    });
</script>";        
        return $ht;
    }
    
    private function get_star() {
        $uri = $this->uri;
        $pattern = "{ <$uri> rev:rating \"1\"^^xsd:integer . }";
        $res = SMOBStore::query("ASK $pattern", true);
        return ($res==1) ? $res : 0;
    }
    
    // URI for publishing
    private function get_publish_uri() {
        return SMOB_ROOT.'?r=' . urlencode($this->uri);
    }
        
    // Get the users mentioned in that post    
    private function get_users() {
        $post = $this->uri;
        $query = "
SELECT ?user ?name
WHERE {
    <$post> sioc:addressed_to ?user .
    ?user sioc:name ?name .
}";
        return SMOBStore::query($query);
    }
    
    // Get the tags mentioned in that post    
    private    function get_tags() {
        $post = $this->uri;
        $query = "
SELECT ?tag ?uri
WHERE {
    ?tagging a tags:RestrictedTagging ;
        tags:taggedResource <$post> ;
        tags:associatedTag ?tag ;
        moat:tagMeaning ?uri .
}";
        return SMOBStore::query($query);
    }
    
    public function set_data($ts, $content, $reply_of, $location, $location_uri, $mappings) {

        $user_uri = SMOBTools::user_uri();
        $this->ts = $ts;
        $this->content = $content;
        $this->uri($ts);
        
        $triples[] = array(SMOBTools::uri($this->uri), "a", "sioct:MicroblogPost");
        $triples[] = array("sioc:has_container", SMOBTools::uri(SMOB_ROOT));
        $triples[] = array("sioc:has_creator", SMOBTools::uri($user_uri));
        $triples[] = array("foaf:maker", SMOBTools::uri(FOAF_URI));
        $triples[] = array("dct:created", SMOBTools::date($this->ts));
        $triples[] = array("dct:title", SMOBTools::literal("Update - ".$this->ts));
        $triples[] = array("sioc:content", SMOBTools::literal($content));
        if($reply_of) {
            $triples[] = array("sioc:reply_of", SMOBTools::uri($reply_of));            
        }

        $triples[] = array(SMOBTools::uri(SMOB_ROOT), "a", "smob:Hub");

        $opo_uri = $this->uri.'#presence';
        $triples[] = array(SMOBTools::uri($opo_uri), "a", "opo:OnlinePresence");
        $triples[] = array("opo:declaredOn", SMOBTools::uri($user_uri));
        $triples[] = array("opo:declaredBy", SMOBTools::uri(FOAF_URI));
        $triples[] = array("opo:StartTime", SMOBTools::date($this->ts));
        $triples[] = array("opo:customMessage", SMOBTools::uri($this->uri));
        if($location_uri) {
            $triples[] = array("opo:currentLocation", SMOBTools::uri($location_uri));
            $triples[] = array(SMOBTools::uri($location_uri), "rdfs:label", SMOBTools::literal($location));
            SMOBStore::query("LOAD <$location_uri>");
        }
        
        if($mappings) {
            $mp = explode(' ', $mappings);
            foreach($mp as $m) {
                $mapping = explode('--', $m);
                if($mapping[0] == 'user') {
                    $user = $mapping[1];
                    $uri = $mapping[2];
                    $triples[] = array(SMOBTools::uri($this->uri), "sioc:addressed_to", SMOBTools::uri($uri));
                    $triples[] = array(SMOBTools::uri($uri), "sioc:name", SMOBTools::literal($user));
                }
                elseif($mapping[0] == 'tag' || $mapping[0] == 'location') {
                    $tag = $mapping[1];
                    $uri = $mapping[2];
                    $tagging = TAGGING_URL.uniqid();
                    $triples[] = array(SMOBTools::uri($tagging), "a", "tags:RestrictedTagging");
                    $triples[] = array(SMOBTools::uri($tagging), "tags:taggedResource", SMOBTools::uri($this->uri));
                    $triples[] = array(SMOBTools::uri($tagging), "tags:associatedTag", SMOBTools::literal($tag));
                    $triples[] = array(SMOBTools::uri($tagging), "moat:tagMeaning", SMOBTools::uri($uri));
                    $triples[] = array(SMOBTools::uri($this->uri), "moat:taggedWith", SMOBTools::uri($uri));
                }
            }
        }

        $this->triples = $this->triples + $triples;
    
    }
    
    private function uri() {
        $this->uri = POST_URL.$this->ts;
    }
    
    private function graph() {
        //return str_replace('/post/', '/data/', $this->uri);
        return DATA_URL.$this->ts;
    }
    
    public function save() {
        $graph = $this->graph();
        $rdf = SMOBTools::render_sparql_triples($this->triples);    
        $query = "INSERT INTO <$graph> { $rdf }";
        error_log($query,0);
        SMOBStore::query($query);
        print '<li>Message saved locally !</li>';
    }
    
    public function delete() {
        $uri = $this->uri; 
        $graph = $this->graph(); 
        SMOBStore::query("DELETE FROM <$graph>");
        $this->delete_from_rss_file();
        $this->notify('DELETE FROM');
    }
    
    public function notify($action = 'LOAD') {
        error_log("in notify");
        $followers = SMOBTools::followers();
        $followers = "SELECT ?uri WHERE {graph <".FOLLOWINGS_URI."> {".ME_URL." sioc:follows ?uri .}}";
        error_log("followers: ",0);
        error_log(print_r($followers,1),0);
        if($followers) {
            // Publish new feed to the hub

            //@TODO: should the hub_url be stored somewhere?
            $hub_url = HUB_URL_PUBLISH;
            $topic_url = ME_FEED_URL;
            // Reusing do_curl function
            $feed = urlencode($topic_url);
            error_log("topic url: ".$feed,0);
            $result = SMOBTools::do_curl($hub_url, $postfields ="hub.mode=publish&hub.url=$feed&hub.foaf=".PRIVATE_PROFILE_URL);
            // all good -- anything in the 200 range
            error_log("post sent",0); 
            if (substr($result[2],0,1) == "2") {
                error_log("DEBUG: $topic_url was successfully published to hubsub $hub_url",0);
            }
            error_log("DEBUG: Server answer: ".join(' ', $result),0);
            
            if($action == 'LOAD') {
                print '<li>Notification sent to your followers !</li>';
            } elseif($action == 'DELETE FROM') {
                print '<li>Delete notification sent to your followers !</li>';
            } else {
                return;
            }
        }
    }

    public function sindice() {
        $client = new xmlrpc_client("http://sindice.com/xmlrpc/api");
        $payload = new xmlrpcmsg("weblogUpdates.ping");
   
        $payload->addParam(new xmlrpcval($this->content));
        $payload->addParam(new xmlrpcval($this->uri));
   
        $response = $client->send($payload);
        $xmlresponsestr = $response->serialize();
   
        $xml = simplexml_load_string($xmlresponsestr);
        $result = $xml->xpath("//value/boolean/text()");
        if($result) {
            if($result[0] == "0"){
                print '<li>Message sent to Sindice !</li>';
             }
        } else {
            $code = $response->faultCode();
            $err = $response->faultString();
            print '<li>Failed to submit to Sindice ($code: $err)</li>';
        }
    }
    
    public function tweet() {
        $dest = 'http://twitter.com/statuses/update.xml';
        $postfields = 'status='.urlencode($this->content).'&source=smob';
        $userpwd = TWITTER_USER.':'.TWITTER_PASS;
        SMOBTools::do_curl($dest, $postfields, $userpwd);
        print '<li>Notified on Twitter !</li>';
    }
    
    public function raw() {    
        $uri = $this->graph();
        $query = "
SELECT *
WHERE { 
    GRAPH <$uri> {
        ?s ?p ?o
    }
}";

        $data = SMOBStore::query($query);
        header('Content-Type: text/turtle; charset=utf-8'); 
        foreach($data as $triple) {
            $s = $triple['s'];
            $p = $triple['p'];
            $o = $triple['o'];    
            $ot = $triple['o type'];    
            $odt = in_array('o datatype', array_keys($triple)) ? '^^<'.$triple['o datatype'].'>' : '';
            echo "<$s> <$p> ";
            echo ($ot == 'uri') ? "<$o> " : "\"$o\"$odt ";
            echo ".\n" ;
        }
        exit();
    }
    
    public function turtle() {
        // Function similar to raw, but it returns the turtle triples as text instead of a new page
        $turtle = "";
        $uri = $this->graph();
        $query = "
SELECT *
WHERE { 
    GRAPH <$uri> {
        ?s ?p ?o
    }
}";

        $data = SMOBStore::query($query);
        foreach($data as $triple) {
            $s = $triple['s'];
            $p = $triple['p'];
            $o = $triple['o'];    
            $ot = $triple['o type'];    
            $odt = in_array('o datatype', array_keys($triple)) ? '^^<'.$triple['o datatype'].'>' : '';
            $turtle .= "<$s> <$p> ";
            $turtle .= ($ot == 'uri') ? "<$o> " : "\"$o\"$odt ";
            $turtle .= ". " ;
        }
        return $turtle;
    }    
    
    function get_access_spaces() {
        error_log("DEBUG: Post::get_access_spaces", 0);
        $post = $this->uri;
//         $query = "
//         SELECT ?accessquery WHERE {
//             <$post> a rdfs:MicroblogPost;
//             moat:taggedWith ?hashtag.
//             ?pp a ppo:PrivacyPreference;
//             ppo:appliesToResource rdfs:MicroblogPost;
//             ppo:hasCondition [
//             ppo:hasProperty moat:taggedWith ;
//             ppo:resourceAsObject ?hashtag .
//             ];
//             ppo:assignAccess acl:Read;
//             ppo:hasAccessSpace [ ppo:hasAccessQuery ?accessquery ] .
//         }";
//         $query = "
//         SELECT ?accessquery WHERE {
//             <$post> a rdfs:MicroblogPost;
//             moat:taggedWith ?hashtag.
//             ?pp a ppo:PrivacyPreference;
//                 ppo:appliesToResource rdfs:MicroblogPost;
//                 ppo:assignAccess acl:Read;
//                 ppo:hasCondition ?x;
//                 ppo:hasAccessSpace ?y.
//             ?x ppo:hasProperty moat:taggedWith ;
//                   ppo:resourceAsObject ?hashtag .
//             ?y ppo:hasAccessQuery ?accessquery .
//         }";
        $query = "SELECT ?accessquery WHERE {
  ?pp <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://vocab.deri.ie/ppo#PrivacyPreference>;
    <http://vocab.deri.ie/ppo#hasCondition> ?x;
    <http://vocab.deri.ie/ppo#hasAccessSpace> ?y.
  ?x <http://vocab.deri.ie/ppo#resourceAsObject> ?hashtag .
  ?y <http://vocab.deri.ie/ppo#hasAccessQuery> ?accessquery .
  <$post> <http://moat-project.org/ns#taggedWith> ?hashtag .
}";
        

//         SELECT * WHERE {
//             GRAPH <https://localhost/smob/ppo> { ?s ?p ?o . }
//         }
        $data = SMOBStore::query($query);
        print_r($data);
        error_log(print_r($data, 1), 0);
        $accessqueries = array();
        foreach($data as $t) {
            print_r($t);
            error_log(print_r($t,1),0);
            //$interests[$t['interest_label']] = $t['accessquery'];
            //$preferences[$t['resource']] = 
            $accessqueries[] = $t['accessquery'];
        }
        error_log(print_r($accessqueries, 1), 0);
        return $accessqueries;
    }
        
}
