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
        if(!isset($res[0]['name'])) {
            $query = "SELECT ?name WHERE { <".FOAF_URI."> foaf:nick ?name } LIMIT 1";
            $res = SMOBSTore::query($query);
        }
        return $res[0]['name'];
    }
    
    // check if the FOAF URI is correct
    public function checkFoaf($foaf) {
        SMOBStore::query("LOAD <$foaf>");
        error_log("checkFoaf: loaded $foaf",0);
        $name = "SELECT DISTINCT ?o WHERE { <$foaf> ?p ?o } LIMIT 1";
        $res = SMOBStore::query($name);
        error_log("checkFoaf, select",0);
        error_log(print_r($res,1),0);
        return sizeof($res) == 1;
    }
    
    // Generate arc config file
    function arc_config() {
        //error_log("setting up arc",0);
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
            'endpoint_max_limit' => 1000,
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
        $pattern = '?uri sioc:follows <' . ME_URL . '>';
        return SMOBTools::people('followers', $pattern);
    }

    function followings() {
        $pattern = '<' . ME_URL . '> sioc:follows ?uri';
        return SMOBTools::people('followings', $pattern);
    }
    
    function people($type, $pattern) {
        //from  <https://localhost/smob/data/followings> 
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
        error_log("user ".$u, 0);
        $res = SMOBStore::query("LOAD <$u>");
        $hubs = "SELECT DISTINCT ?s WHERE { GRAPH <$u> { ?s a smob:Hub } } LIMIT 1";
        $res = SMOBStore::query($hubs);
        SMOBStore::query("DROP <$u>");
        return (sizeof($res) == 1) ? $res[0]['s'].'me' : '';
    }
    
    function user_uri() {
        return ME_URL;
    }
    
    function get_uri($uri, $type) {
        $uri = urlencode($uri);
        $uri = str_replace("%2F", "/", $uri);
        return SMOB_ROOT."$type/$uri";
    }
    
    function get_post_uri($uri) {
        $uri = str_replace(' ', '+', $uri);
        return POST_URL."$uri";
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

    
    // Function to get the scheme and domain host URL
    function host($url) {
        $host = parse_url($url, PHP_URL_SCHEME) . "://" .  parse_url($url, PHP_URL_HOST) ;
        return $host;
    }
    
  function select_all_from_graph($graph) {
    $query = "
SELECT *
WHERE { 
  GRAPH <$graph> {
    ?s ?p ?o
  }
}";
    $query = "SELECT * FROM <$graph> WHERE {?s ?p ?o}";
    $data = SMOBStore::query($query);
    return $data;
  }

  function triples_array2turtle($data) {
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

  public function triples_from_graph($graph) {
    $turtle = "";
    $query = "
SELECT *
WHERE { 
  GRAPH <$graph> {
    ?s ?p ?o
  }
}";
    $data = SMOBStore::query($query);
    $turtle = SMOBTools::triples_array2turtle($data);
    return $turtle;
  }

  public function rdfxml_from_graph($graph) {
    $query = "
SELECT *
WHERE { 
  GRAPH <$graph> {
    ?s ?p ?o
  }
}";
    $data = SMOBStore::query($query);
    $ser = ARC2::getRDFXMLSerializer();
    $rdfxml = $ser->getSerializedTriples($data);
    return $rdfxml;
  }

  public function get_profile() {
    $query = "
    SELECT DISTINCT *
    WHERE { 
      GRAPH <".ME_URL."> {
        ?s ?p ?o
      }
    }";
    $me = SMOBStore::query($query);
    error_log("Tools::get_profile data",0);
    error_log(print_r($me,1),0);
    
    if(FOAF_URI != ME_URL) {
        $query = "
        SELECT *
        WHERE { 
          GRAPH <".FOAF_URI."> {
            ?s ?p ?o
          }
        }";
        $foaf = SMOBStore::query($query);
        error_log("Tools::get_profile data",0);
        error_log(print_r($foaf,1),0);
        $me += $foaf;
    }
    $ser = ARC2::getRDFXMLSerializer($conf);
    $rdfxml = $ser->getSerializedTriples($me);
    return $rdfxml;
  }

  function get_rel_types() {
    $rels = array();
    $rels[''] = '';
    $filename = 'relationship.json';
    $jsonfile = fopen($filename,'r');
    $jsontext = fread($jsonfile,filesize($filename));
    fclose($jsonfile);
    $json = json_decode($jsontext, true);
    foreach ($json as $rel=>$relarray) {
      if (strpos($rel, "http://purl.org/vocab/relationship/") === 0) {
        if (array_key_exists('http://www.w3.org/2000/01/rdf-schema#label', $relarray)) {
//          $label = $json[$rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          $label = $relarray['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          //error_log("position using with",0);
          //error_log(strpos($label, "Using With"),0);
          if (strpos($label, "Using With") === FALSE) {
            $rels[$label] = $rel;
          };
        };
      };
    };
    return $rels;
  }

  function set_rel_type_options($rels) {
    $options = "";
    foreach($rels as $label=>$rel) {
      $options .=  "        <option name='$label' value='$rel' >$label</option>\n";
    };
    return $options;
  }

  function delete_graph() {
    $query = "DELETE FROM <$graph>";
    $data = SMOBStore::query($query);
    error_log("graph deleted",0);
    error_log(print_r($data, 1),0);
  }

}

?>
