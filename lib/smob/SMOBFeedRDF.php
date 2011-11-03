<?php

/* 
	Helper methods for the different SMOB Classes
*/

class SMOBFeedRDF {
	
// 	//$tweet = new SMOBFeed();
// 	//$tweet->rss();
// 	error_log("DEBUG: rssfilepath: ".FEED_FILE_PATH,0);
// 	if (!file_exists(FEED_FILE_PATH)) {
// 	error_log("DEBUG: initial RSS file does not exists", 0);
// 			    SMOBTools::initial_rss_file();
// 	}
// 	$rssfile = fopen(FEED_FILE_PATH, 'r');
// 	$rss = fread($rssfile, filesize(FEED_FILE_PATH));
// 	fclose($rssfile);
// 	echo($rss);

// 	var $posts;
	
// 	public function __construct() {
// 		$r = new SMOBPostListUser(FOAF_URI, 1);
// 		$this->posts = $r->posts;
// 	}
	
	
	public function rss() {
		error_log("DEBUG: rssfilepath: ".FEED_FILE_PATH,0);
		if (!file_exists(FEED_FILE_PATH)) {
			error_log("DEBUG: initial RSS file does not exists", 0);
			SMOBFeedRDF::initial_rss_file();
		}
		$rssfile = fopen(FEED_FILE_PATH, 'r');
		$rss = fread($rssfile, filesize(FEED_FILE_PATH));
		fclose($rssfile);
		echo($rss);
	}
	function create_rss_doc() {
		error_log("DEBUG: SMOBFeeDRDF::create_rss_doc");
		$version = SMOBTools::version();
		$owner = SMOBTools::ownername();
		$title = "SMOB Hub of $owner";
		$ts = date('c');
		
		$rss = "<?xml version='1.0' encoding='utf-8'?>
		<rdf:RDF
			xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'
			xmlns:dc='http://purl.org/dc/elements/1.1/'
			xmlns='http://purl.org/rss/1.0/'
			xmlns:dcterms='http://purl.org/dc/terms/'
			xmlns:cc='http://web.resource.org/cc/'
			xmlns:content='http://purl.org/rss/1.0/modules/content/'
			xmlns:admin='http://webns.net/mvcb/'
			xmlns:atom='http://www.w3.org/2005/Atom'
		> 
		
		<channel rdf:about='".SMOB_ROOT."'>
			<title>$title</title>
			<link>".SMOB_ROOT."</link>
			<atom:link rel='hub' href='".HUB_URL_SUBSCRIBE."'/>
			<description>$title</description>
			<dc:creator>$owner</dc:creator>
			<dc:date>$ts</dc:date>
			<admin:generatorAgent rdf:resource='http://smob.me/#smob?v=$version' />
			<items>
				<rdf:Seq>
				</rdf:Seq>
			</items>
		</channel>
		</rdf:RDF>
		";		
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;
		
// 		$root = $xml->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdf:RDF");
		$root = $xml->createElementNS('http://purl.org/rss/1.0/',"rdf:RDF");
		//'http://www.w3.org/2000/xmlns/'
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:rdf", 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:dc", 'http://purl.org/dc/elements/1.1/'); 
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:dcterms", 'http://purl.org/dc/terms/'); 
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:cc", 'http://web.resource.org/cc/'); 
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:content", 'http://purl.org/rss/1.0/modules/content/'); 
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:admin", 'http://webns.net/mvcb/'); 
	    $root->setAttributeNS('http://www.w3.org/2000/xmlns/',"xmlns:atom", 'http://www.w3.org/2005/Atom'); 
 	    $root->setAttribute("xmlns", 'http://purl.org/rss/1.0/'); 
		$xml->appendChild($root);
	    		
		$channel = $xml->createElement("channel");
		$channel->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdf:about", SMOB_ROOT);
		$root->appendChild($channel);
		
		$title_elem = $xml->createElement("title");
		$title_elem->appendChild($xml->createTextNode($title));
		$channel->appendChild($title_elem);
		
		$link = $xml->createElement("link");
		$link->appendChild($xml->createTextNode(SMOB_ROOT));
		$channel->appendChild($link);
		
		$atomlink = $xml->createElementNS('http://www.w3.org/2005/Atom',"atom:link");
		$atomlink->setAttribute("rel", 'hub');
		$atomlink->setAttribute("href", HUB_URL_SUBSCRIBE);		
// 		$title = $xml->createElement("atom:link");
// 		$tittle->appendChild($xml->createTextNode());
		$channel->appendChild($atomlink);

		$description = $xml->createElement("description");
		$description->appendChild($xml->createTextNode($title));
		$channel->appendChild($description);
		
		$creator = $xml->createElementNS('http://purl.org/dc/elements/1.1/',"dc:creator");
		$creator->appendChild($xml->createTextNode($owner));
		$channel->appendChild($creator);
		
		$date = $xml->createElementNS('http://purl.org/dc/elements/1.1/',"dc:date");
		$date->appendChild($xml->createTextNode($ts));
		$channel->appendChild($date);
		
		$admin = $xml->createElementNS('http://webns.net/mvcb/',"admin:generatorAgent");
		$admin->setAttributeNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#',"rdf:resource", 'http://smob.me/#smob?v=$version');
		$channel->appendChild($admin);
		
		$items = $xml->createElement("items");
		$seq = $xml->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdf:Seq");
		$items->appendChild($seq);
		$channel->appendChild($items);		
		
		error_log("DEBUG: created RSS header: ".$xml->saveXML($item),0);
		
		file_put_contents(FEED_FILE_PATH,  print_r($xml->saveXML(),1));
		error_log("DEBUG: Created initial RSS file",0);
		return $xml;
	}
	
	public function get_posts() {
		error_log("DEBUG: SMOBFeeDRDF::get_posts",0);
		$uri = FOAF_URI;
		// Weird ARC2 bug iw adding ?creator or ?star in the following varlist !
		// Bug as well for the /resource/XXX if adding the ?depiction and ?name in the query
		$query = "
		SELECT DISTINCT ?post
		WHERE {
			?post rdf:type sioct:MicroblogPost ;
				foaf:maker <$uri>;
		} 
		";
		$posts = array();
		$result = SMOBStore::query($query);
		foreach($result as $post) {
			$uri = $post['post'];
			$posts[] = new SMOBPost($uri);
		}
		error_log("DEBUG: posts: ",0);
		error_log(print_r($posts,1),0);
		return $posts;
	}
	
	function initial_rss_file() {
		error_log("DEBUG: SMOBFeeDRDF::initial_rss_file",0);
		$header = SMOBFeedRDF::create_rss_doc();
		$posts = SMOBFeedRDF::get_posts();
		foreach($posts as $post) {
			$post->add_to_rss_file();
		}
	}	
	
	function create_rss_item($uri, $ocontent, $date, $name, $turtle, $access_spaces) {
		error_log("DEBUG: SMOBFeeDRDF::create_rss_item");
		
		$xml = new DOMDocument();
		
		$item = $xml->createElement("item");
		$item->setAttribute("rdf:about", $uri);
		
		$title = $xml->createElement("title");
		$title->appendChild($xml->createTextNode($ocontent));
		$item->appendChild($title);
		
		$description = $xml->createElement("description");
		$description->appendChild($xml->createTextNode($ocontent));
		$item->appendChild($description);
		
		$dc_creator = $xml->createElement("dc:creator");
		$dc_creator->appendChild($xml->createTextNode($name));
		$item->appendChild($dc_creator);
		
		$dc_date = $xml->createElement("dc:date");
		$dc_date->appendChild($xml->createTextNode($date));
		$item->appendChild($dc_date);
		
		$link = $xml->createElement("link");
		$link->appendChild($xml->createTextNode($uri));
		$item->appendChild($link);
		
		$content_encoded = $xml->createElement("content:encoded");
		$content_encoded->appendChild($xml->createCDATASection($turtle));
		$item->appendChild($content_encoded);
		
		$privacy = $xml->createElement("privacy");
      	foreach($access_spaces as $as) {
			$access_space = $xml->createElement("access_space");
			$access_space ->appendChild($xml->createCDATASection($as));
			$privacy->appendChild($access_space);
      	}
		$item->appendChild($privacy);
		
		$xml->appendChild($item);
		
		$xml->formatOutput = true;
// 		error_log("DEBUG: created new RSS item: ".$xml->saveXML($item),0);
		return $item;
	}
		
	function add_rss_item($item) {
		if (!file_exists(FEED_FILE_PATH)) {
			error_log("DEBUG: initial RSS file does not exists", 0);
			this.initial_rss_file();
		}
		$xml = new DOMDocument();
		$xml->formatOutput = true;
		$xml->load(FEED_FILE_PATH);
		
		$seq = $xml->getElementsByTagNameNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","Seq")->item(0);
		error_log("DEBUG: seq:",0);
		error_log(print_r($seq,1),0);
	    $link = $item->getElementsByTagName("link")->item(0)->nodeValue;
	    error_log("DEBUG: link:",0);
		error_log(print_r($link,1),0);
		
		// create li element
	    $li = $xml->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdf:li");
	    $li->setAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#","rdf:resource", $link); 
		$seq->insertBefore($li, $seq->firstChild);
		
		// create item element
		$root = $xml->documentElement;
		$item = $xml->importNode($item, true);
		//$lastitem = $item->getElementsByTagName("item")->last_child;
		$lastitem = $item->getElementsByTagName("item")->item(0);
		$root->insertBefore($item, $lastitem);
		
		// save the file formated
		//$filesaved = $xml->save(FEED_FILE_PATH);
		$rssfile = fopen(FEED_FILE_PATH,'w');
		fwrite($rssfile, print_r($xml->saveXML(),1));
		fclose($rssfile);
		
// 		error_log("DEBUG: saved RSS file : ".$xml->saveXML(),0);
	}
	
// 	function additemstring2rssfile($itemstring) {
	
// 	$newxml = new DOMDocument();
// 	$newxml->loadXML($itemstring);
// 	$newitem = $newxml->getElementsByTagName("item")->item(0);
// 	error_log("DEBUG: new item to add to RSS file: ".$newitem->nodeValue);
	
// 	SMOBTools::additem2rssfile($newitem);
// 	}
	
	function delete_rss_item($uri) {
		
		if (!file_exists(FEED_FILE_PATH)) {
			error_log("DEBUG: initial RSS file does not exists", 0);
			this.initial_rss_file();
		}
        $xml = new DOMDocument();
		$xml->load(FEED_FILE_PATH);

        $links = $xml->getElementsByTagName("link");
		foreach($links as $link) {
			if ($link->nodeValue == $uri) {

				$item = $link->parentNode;
				$content_encoded = $item->getElementsByTagNameNS("http://purl.org/rss/1.0/modules/content/","encoded")->item(0);
				error_log("DEBUG: deleting content: ".$content_encoded->nodeValue, 0);
			
				$empty_content_encoded = $xml->createElement("content:encoded");
				$empty_content_encoded->appendChild(
				//$xml->createCDATASection("")
                	$xml->createTextNode("")
				);
				$item->replaceChild($empty_content_encoded, $content_encoded);

			}
		}
		error_log("DEBUG: saved RSS file : ".$xml->saveXML(),0);
		$filesaved = $xml->save(FEED_FILE_PATH);
	}

	function get_rdf_from_rss($rssstring) {
		$xml = new DOMDocument();
		$xml->loadXML($rssstring);
	
		$items = $xml->getElementsByTagName("item");
		foreach( $items as $item )   {
			$content_encoded = $item->getElementsByTagNameNS("http://purl.org/rss/1.0/modules/content/","encoded")->item(0);
			//utf8_decode
			$content = html_entity_decode(htmlentities($content_encoded->nodeValue, ENT_COMPAT, 'UTF-8'),
			ENT_COMPAT,'ISO-8859-15');
			error_log("DEBUG: RSS item content".$content_encoded->nodeValue);
			$link = $item->getElementsByTagName("link")->item(0)->nodeValue;
			if (empty($content)) {
				$query = "DELETE FROM <$link>";
				//SMOBTools::deletefromrssfile($link);
		    } else {
		        $query = "INSERT INTO <$link> { $content }";
				//SMOBTools::additem2rssfile($item);
			}
			SMOBStore::query($query);
			error_log("DEBUG: Query executed: $query",0);
		}
	}


	function rss2rdf($post_data) {
	    // Function to convert RSS to RDF, some elements as tags will be missing
        //@FIXME: this solution is a bit hackish
        $post_data = str_replace('dc:date', 'dc_date', $post_data);
        
        // Parsing the new feeds to load in the triple store
        $xml = simplexml_load_string($post_data);
        if(count($xml) == 0)
            return;
        error_log("DEBUG: xml received from publisher: ".print_r($xml,1),0);
        foreach($xml->item as $item) {
            $link = (string) $item->link;
            $date = (string) $item->dc_date;
            $description = (string) $item->description;
            $site = SMOBTools::host($link);
            $author = $site . "/me";

            $query = "INSERT INTO <$link> {
            <$site> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://smob.me/ns#Hub> .
            <$link> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rdfs.org/sioc/types#MicroblogPost> .
            <$link> <http://rdfs.org/sioc/ns#has_container> <$site> .
            <$link> <http://rdfs.org/sioc/ns#has_creator> <$author> .
            <$link> <http://xmlns.com/foaf/0.1/maker> <$author#id> .
            <$link> <http://purl.org/dc/terms/created> \"$date\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .
            <$link> <http://purl.org/dc/terms/title> \"Update - $date\"^^<http://www.w3.org/2001/XMLSchema#string> .
            <$link> <http://rdfs.org/sioc/ns#content> \"$description\"^^<http://www.w3.org/2001/XMLSchema#string> .
            <$link#presence> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://online-presence.net/opo/ns#OnlinePresence> .
            <$link#presence> <http://online-presence.net/opo/ns#declaredOn> <$author> .
            <$link#presence> <http://online-presence.net/opo/ns#declaredBy> <$author#id> .
            <$link#presence> <http://online-presence.net/opo/ns#StartTime> \"$date\"^^<http://www.w3.org/2001/XMLSchema#dateTime> .
            <$link#presence> <http://online-presence.net/opo/ns#customMessage> <$link> . }";
            SMOBStore::query($query);
			error_log("DEBUG: Added the triples: $query",0);
        }
	}
		
}
