<?php

/* 
	Helper methods for the different SMOB Classes
*/

class SMOBTools {
	
	// Get version
	public function version() {
		return trim(file_get_contents(dirname(__FILE__).'/../../VERSION'));
	}
	
	// Remove posts older than X days
	public function purge($purge) {
		$date = date('c', time()-$purge*24*3600);
		$query = "
SELECT DISTINCT ?graph
WHERE {
	GRAPH ?graph {
		?post a sioct:MicroblogPost ;
			dct:created ?date ;
			foaf:maker ?author .
		FILTER (?date < '$date') .
		FILTER (?author != <".FOAF_URI.">) .
	} 
	OPTIONAL { ?post rev:rating ?star }
	FILTER (!bound(?star))
}";
		$res = SMOBStore::query($query);
		if($res) {
			foreach($res as $r) {
				$g = $r['graph'];
				$query = "DELETE FROM <$g> ";
				SMOBStore::query($query);
			}
		}		
	}
	
	// Name of the Hub owner
	public function ownername() {
		$query = "SELECT ?name WHERE { <".FOAF_URI."> foaf:name ?name } LIMIT 1";
		$res = SMOBSTore::query($query);
		return $res[0]['name'];
	}
	
	// check if the FOAF URI is correct
	public function checkFoaf($foaf) {
		SMOBStore::query("LOAD <$foaf>");
		$name = "SELECT DISTINCT ?o WHERE { <$foaf> ?p ?o } LIMIT 1";
		$res = SMOBStore::query($name);
		return sizeof($res) == 1;
	}
	
	// Generate arc config file
	function arc_config() {
		return array(
			'db_host' => DB_HOST, 
			'db_name' => DB_NAME,
			'db_user' => DB_USER,
			'db_pwd' => DB_PASS,
			'store_name' => DB_STORE,

			'store_triggers_path' => dirname(__FILE__).'/../',
			'store_triggers' => array(
				'insert' => array('foafLoad'),
				'load' => array('foafLoad'),
			),
			'endpoint_features' => array(
		    	'select', 'construct', 'ask', 'describe', 'load'
			),
			'sem_html_formats' => 'rdfa',
		);
	}
	
	// Check if allowed to LOAD / DELETE
	function checkAccess($_POST) {
		if($query = trim($_POST['query'])) {
			// update to regexp
			$action = substr_count($query, 'LOAD');
			if(!$action) {
				$action = substr_count($query, 'DELETE FROM');
			}
			$first = substr_count($query, '<');
			$last = substr_count($query, '>');
			if($action == 1 && $first == 1 && $last == 1) {
				preg_match('/<(.*)>/', $query, $matches);
				$uri = $matches[1];
				$followers = SMOBTools::followings();
				if($followers) {
					foreach($followers as $f) {
						$f = $f['uri'];
						if(strpos($f, $uri) == 0) {
							return true;
						}
					}
				}
			}
		}
		print "Operation not allowed";
		die();		
	}
	
	// Get current location
	public function location() {
		$query = "
SELECT DISTINCT ?time ?location ?name WHERE {
	GRAPH ?g {
		?presence opo:currentLocation ?location ;
			opo:StartTime ?time ;
			opo:declaredBy <".FOAF_URI."> .
		?location rdfs:label ?name
		}
	}
ORDER BY DESC(?time)
LIMIT 1";		
		$res = SMOBStore::query($query);
		$loc = $res[0]['location'];
		$locname = $res[0]['name'];
		return array($loc, $locname);
	}
	
	// List of followers
	function followers() {
		$pattern = '?uri sioc:follows <' . SMOBTools::user_uri() . '>';
		return SMOBTools::people('followers', $pattern);
	}

	function followings() {
		$pattern = '<' . SMOBTools::user_uri() . '> sioc:follows ?uri';
		return SMOBTools::people('followings', $pattern);
	}
	
	function people($type, $pattern) {
		$query = "SELECT * WHERE { $pattern }";
		return SMOBStore::query($query);
	}
	
	function &either() {
		$arg_list = func_get_args();
		foreach($arg_list as $i => $arg) {
			if ($arg) {
				return $arg_list[$i];
			}
		}
		return null;
	}
	
	function do_curl($url, $postfields = null, $userpwd = null, $type='POST') {
		if($type == 'POST') {
			$ch = curl_init(POSTURL);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		} else {
			$ch = curl_init();
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($userpwd) {
			curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
		}
		$response = curl_exec($ch);
		if ($error = curl_error($ch)) {
			return array("$error.", "", 0);
		}

		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$status_line = substr($response, 0, strcspn($response, "\n\r"));
		curl_close($ch);

		return array($response, $status_line, $status_code);
	}
	

	function get_uri_if_found($uri) {
		list ($resp, $status, $code) = SMOBTools::do_curl($uri);
		if ($code == 200)
			return $uri;
		else if ($code == 404)
			return "";
		else {
			print "Error fetching $uri: ";
			if ($status)
				print $status;
			else
				print $resp;
	    	return "";
		}
	}

	// TODO : A Dedicated SMOBAuth module
	function is_auth() {
		require_once(dirname(__FILE__).'/../foaf-ssl/libAuthentication.php');
		// need cookies here 
		global $foaf_ssl;
		if($foaf_ssl) {
			session_start();
			if($_COOKIE['auth']==1) {
				return true;
			}
			$auth = getAuth();
			$do_auth = $auth['certRSAKey'];
			$is_auth = $auth['isAuthenticated'];
			$auth_uri = $auth['subjectAltName'];
			if ($is_auth == 1) {
				setcookie("uri", "$auth_uri");
				setcookie("auth", "1");
				if ($auth_uri == FOAF_URI) {
					return true;
				}			
			}
		}
		return false;
	}

	// Find the user URI from the loaded page (in case it's loaded from a /resource/xxx)
	function remote_user($u) {
		// LOAD the page and fine the Hub
		$u = str_replace(' ', '+', $u);
		$res = SMOBStore::query("LOAD <$u>");
		$hubs = "SELECT DISTINCT ?s WHERE { GRAPH <$u> { ?s a smob:Hub } } LIMIT 1";
		$res = SMOBStore::query($hubs);
		SMOBStore::query("DROP <$u>");
		return (sizeof($res) == 1) ? $res[0]['s'].'me' : '';
	}
	
	function user_uri() {
		return SMOB_ROOT.'me';
	}
	
	function get_uri($uri, $type) {
		$uri = urlencode($uri);
		$uri = str_replace("%2F", "/", $uri);
		return SMOB_ROOT."$type/$uri";
	}
	
	function get_post_uri($uri) {
		$uri = str_replace(' ', '+', $uri);
		return SMOB_ROOT."post/$uri";
	}
	
	
	function optionals($subj, $props) {
		$r = "";
		foreach ($props as $p) {
			$name = substr($p, stripos($p, ":")+1);
			$r .= "UNION { <$subj> $p ?$name . }\n";
		}
		return $r;
	}

	function optionals_to_array_of_arrays($all, $rs) {
		$r = array();
		foreach ($all as $name) {
			$name = substr($name, stripos($name, ":")+1);
			$r[$name] = array();
		}
		foreach ($rs as $row) {
			foreach ($all as $name) {
				$name = substr($name, stripos($name, ":")+1);
				if ($row[$name])
					$r[$name][] = $row[$name];
			}
		}
		return $r;
	}

	function choose_optional($names, $rs) {
		foreach ($names as $name) {
			$name = substr($name, stripos($name, ":")+1);
			if ($rs[$name])
				return $rs[$name];
		}
		return array();
	}
	
	function check_config() {
		return file_exists(dirname(__FILE__)."/../../config/config.php");
	}
	
	function render_sparql_triple($triple) {
		return implode(" ", $triple);
	}

	function render_sparql_triples($triples) {
		if (!$triples)
			return "";
		$r = SMOBTools::render_sparql_triple($triples[0]);
		$i = 1;
		while ($i < count($triples)) {
			if (count($triples[$i]) == 1)
				$r .= " ,\n";
			else if (count($triples[$i]) == 2)
				$r .= " ;\n";
			else
				$r .= " .\n";
			$r .= SMOBTools::render_sparql_triple($triples[$i]);
			$i += 1;
		}
		$r .= " .";
		return $r;
	}
	
	# XXX maybe one day, someone writes the proper escaping functions for PHP...
	function uri($uri) {
		return "<" . $uri . ">";
	}

	function literal($literal) {
		return '"' . addslashes($literal) . '"^^xsd:string';
	}
	
	function date($date) {
		return '"' . addslashes($date) . '"^^xsd:dateTime';
	}

	function rss2rdf($post_data) {
        //@FIXME: this solution is a bit hackish
        $post_data = str_replace('dc:date', 'dc_date', $post_data);
        
        // Parsing the new feeds to load in the triple store
        $xml = simplexml_load_string($post_data);
        //if(count($xml) == 0)
        //    return;
        foreach($xml->item as $item) {
            error_log($item,0);
            $link = (string) $item->link;
            error_log($link,0);
            $date = (string) $item->dc_date;
            error_log($date,0);
            $description = (string) $item->description;
            error_log($description,0);
            $site = $this->host($link);
            error_log($site,0);
            $author = $site . "/me";
            error_log($author,0);

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
            error_log($query);
            SMOBStore::query($query);
        }
	}
	
	function get_rdf_from_rss($post_data) {
        //@FIXME: this solution is a bit hackish
        $post_data = str_replace('dc:date', 'dc_date', $post_data);
        $post_data = str_replace('content:encoded', 'content_encoded', $post_data);
        
        // Parsing the new feeds to load in the triple store
        $xml = simplexml_load_string($post_data);
        //if(count($xml) == 0)
        //    return;
        error_log("in getrdffromrss");
        error_log(print_r($xml,1),0);
        foreach($xml->item as $item) {
            error_log($item,0);
            $link = (string) $item->link;
            error_log($link,0);
            $content_encoded = (string) $item->content_encoded;
            error_log($content_encoded,0);
            $content = html_entity_decode((string) $item->content_encoded, ENT_COMPAT, "UTF-8");
            error_log($content,0);
            $query = $content;
            error_log("query: ".$query);
            SMOBStore::query($query);
        }
	}
	
	function host($url) {
	    $host = parse_url($url, PHP_URL_SCHEME) . "://" .  parse_url($url, PHP_URL_HOST) ;
	    return $host;
	}
}

?>
