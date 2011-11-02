<?php

//require_once(dirname(__FILE__)."/../../config/config.php");
//require_once(dirname(__FILE__)."SMOBTools.php");
//require_once(dirname(__FILE__)."SMOBStore.php");
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

class PrivacyPreferences {
  // TODO: The private profile graph is the same as the profile graph, privacy preferences will decide what is visible
  function view_privacy_preferences() {
    $turtle = SMOBTools::triples_from_graph(SMOB_ROOT."ppo");
    header('Content-Type: text/turtle; charset=utf-8'); 
    return $turtle;
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
#          $label = $json[$rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          $label = $relarray['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          error_log("position using with",0);
          error_log(strpos($label, "Using With"),0);
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

  function get_relationships($user_uri) {
    $rel_persons = array();
    //"<" + user_uri + "> <" + rel_types[i] + "> <" + persons[i] + "> . ";
    $query = "SELECT ?person ?rel_type ?rel_label FROM <$user_uri> WHERE {
      <$user_uri> ?rel_type ?person . 
      ?person a foaf:person . 
      ?rel_type rdfs:isDefinedBy <http://purl.org/vocab/relationship/> . 
      ?rel_type rdfs:label ?rel_label . }";
      // rdfs:subPropertyOf foaf:knows
    $query = "SELECT ?person ?rel_type ?rel_label FROM <$user_uri> WHERE {
       <$user_uri> ?rel_type ?person . 
       FILTER(REGEX(?rel_type, 'http://purl.org/vocab/relationship/', 'i')).
       OPTIONAL { ?rel_type rdfs:label ?rel_label  } 
       }";
    $data = SMOBStore::query($query);
    error_log("rels",0);
    error_log(print_r($data, 1), 0);
    if($data) {
      foreach($data as $i=>$t) {
        $rel_persons[$t['rel_type']]=$t['person'];
        //$persons[$i] = $t['person'];
      }
    };
    return $rel_persons;
  }
  
  function get_privacy_preferences() {
    $graph = SMOB_ROOT."ppo";
    $ppo = SMOB_ROOT."preferences";

    $query = "SELECT * FROM <$graph> WHERE {
      <$ppo> a ppo:PrivacyPreference;
          ppo:appliesToResource rdfs:MicroblogPost;
          ppo:hasCondition [
                    ppo:hasProperty moat:taggedWith ;
                    ppo:resourceAsObject ?hashtag .
                    ];
          ppo:assignAccess acl:Read;
          ppo:hasAccessSpace [ ppo:hasAccessQuery ?accessquery ] .
    }";

    $data = SMOBStore::query($query);
    error_log("pp queried",0);
    error_log(print_r($data, 1),0);
    return $data;
  	
  }
  
  function get_access_spaces_hashtags() {
	$data = this.get_privacy_preferences();
    $hashtags = array();
    $accessqueries = array();
    if($data) {
      foreach($data as $i=>$t) {
        //$interests[$t['interest_label']] = $t['accessquery'];
        //$preferences[$t['resource']] = 
        $accessqueries[$i] = $t['accessquery'];
        $hashtags[$i] = $t['hashtag'];
      }
    };
    error_log(print_r($accessqueries, 1),0);
    error_log(print_r($hashtags, 1),0);
    return array('access_spaces'=>$accessqueries, 'hashtags'=>$hashtags);
  }
  
  function get_interests() {
  	$data = this.get_access_spaces_hashtags();
  	$access_spaces = $data['access_spaces'];
  	$hashtags = $data['hashtags'];
    $interests = array();
    if($accessqueries) {
      foreach($accessqueries as $i=>$t) {
        $starturi = strpos($t, "<")+1;
        $enduri = strpos($t, ">")-$starturi;
        $interests[$i] = substr($t, $starturi, $enduri);
        error_log($interests[$i], 0);
      }
    }
    error_log(print_r($interests, 1),0);
    return array('interests'=>$interests, 'hashtags'=>$hashtags);
    //return array($accessquery);
  }

  function get_initial_private_form_data() {
    $rel_types = PrivateProfile::get_rel_types();
    $rel_type_options = PrivateProfile::set_rel_type_options($rel_types);


    $initial_data = PrivacyPreferences::get_interests();
    $interests = $initial_data['interests'];
    error_log("interests: ", 0);
    error_log(print_r($interests, 1), 0);
    $interest_fieldsets = array();
    $index = 0;
    error_log("interests", 0);
    foreach($interests as $interest_label=>$interest) {
      error_log($interest, 0);
      error_log($interest_label,0);
      $interest_fieldset = "
        <div id='interest_fieldset$index'>
          <input type='text' id='interest_label$index' name='interest_label$index' value='$interest_label' class='url required' size='20' readonly />
          (<input type='text' id='interest$index' name='interest$index' value='$interest' class='url required' size='40' readonly />)
          <a id='del_rel$index' href='' onClick='del(\"#interest_fieldset$index\"); return false;'>[-]</a>
        </div>
        </br>";
      $interest_fieldsets[$index] = $interest_fieldset;
      $index++;
    }
    error_log(print_r($interest_fieldsets, 1), 0);
    error_log($index);
    $interest_counter = $index;

    $hashtags = $initial_data['hashtags'];
    $hashtag_fieldsets = array();
    $index = 0;
    error_log("hashtags", 0);
    foreach($hashtags as $hashtag_label=>$hashtag) {
      error_log($hashtag, 0);
      error_log($hashtag_label,0);
      $hashtag_fieldset = "
        <div id='hashtag_fieldset$index'>
          <input type='text' id='hashtag_label$index' name='hashtag_label$index' value='$hashtag_label' class='url required' size='20' readonly />
          (<input type='text' id='hashtag$index' name='hashtag$index' value='$hashtag' class='url required' size='40' readonly />)
          <a id='del_rel$index' href='' onClick='del(\"#hashtag_fieldset$index\"); return false;'>[-]</a>
        </div>
        </br>";
      $hashtag_fieldsets[$index] = $hashtag_fieldset;
      $index++;
    }
    error_log(print_r($hashtag_fieldsets, 1), 0);
    error_log($index);
    $hashtag_counter = $index;

    $params = array("rel_type_options"=>$rel_type_options,
                    "rel_fieldsets"=>array(),
                    "rel_counter"=>0,
                    "hashtag_fieldsets"=>$hashtag_fieldsets,
                    "hashtag_counter"=>$hashtag_counter,
                    "interest_fieldsets"=>$interest_fieldsets,
                    "interest_counter"=>$interest_counter
                    );
    return $params;
  }

  function view_privacy_preferences_form() {
    $file = 'privacy_preferences_template.php';
    // if(IS_AJAX) {
    // } else {
    $params = PrivacyPreferences::get_initial_private_form_data();
    extract($params);
    ob_start();
    include($file);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }

  function insert_pp() {
    $resource_object = "http://dbpedia.org/resource/Resource\_Description\_Framework";
    $conditions = " ppo:hasProperty moat:taggedWith ;
                    ppo:resourceAsObject <$resource_object> . ";
                    //ppo:resourceAsObject <RDF>
                    //ppo:resourceAsObject <db./semweb>
                    //filter?
    $interest = "http://dbpedia.org/resource/Semantic_Web";
    $accessspace = "?user foaf:topic_interest <$interest> .";
                  //foaf:knows
                  //filter
                  // ?topic dcterms:subject category:Semantic_Web
    $accessquery = "SELECT ?user WHERE { $accessspace }";

    $triples = "
      <$ppo> a ppo:PrivacyPreference;
          ppo:appliesToResource rdfs:MicroblogPost;
          ppo:assignAccess acl:Read;
          ppo:hasCondition [ $conditions ];
          ppo:hasAccessSpace [
                             ppo:hasAccessQuery \"$accessquery\"^^xsd:string
                             ] . ";

    $query = "INSERT INTO <$graph> { $triples }";
    error_log($query, 0);
    $data = SMOBStore::query($query);
    error_log("pp inserted",0);
    error_log(print_r($data, 1),0);
  }

}
