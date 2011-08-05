<?php

//require_once(dirname(__FILE__)."/../../config/config.php");
//require_once(dirname(__FILE__)."SMOBTools.php");
//require_once(dirname(__FILE__)."SMOBStore.php");
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

class PrivacyPreferences {
  // TODO: The private profile graph is the same as the profile graph, privacy preferences will decide what is visible
  function view_private_profile() {
    $turtle = SMOBTools::triples_from_graph(SMOB_ROOT."me");
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

  function get_interests() {
  $graph = SMOB_ROOT."ppo";
  $ppo = SMOB_ROOT."preferences";
  $resource = "http://dbpedia.org/resource/Resource\_Description\_Framework";
  $interest = "http://dbpedia.org/resource/Semantic_Web";
  $accessspace = "?user foaf:topic_interest <$interest>.
  foaf:knows
  foaf:intent
  filter
  ";
  // ?topic dcterms:subject category:Semantic_Web
  $accessquery = "SELECT ?user WHERE { $accessspace .}";
  
  
  $triples = " INSERT
  <$ppo> a ppo:PrivacyPreference;
          ppo:appliesToResource <http://rdfs.org/sioc/ns#MicroblogPost>;
          ppo:hasCondition [
                            ppo:hasProperty tag:Tag;
                            ppo:resourceAsObject <$resource>
                            ppo:resourceAsObject <RDF>
                            ppo:resourceAsObject <db./semweb>
                            filter?
                          ];
          ppo:assignAccess acl:Read;
          ppo:hasAccessSpace [
                            ppo:hasAccessQuery \"$accessquery\"
                              ] . ";




    $query = "INSERT INTO <$graph> { $triples }";
    $query = "SELECT ?accessquery FROM <$graph> WHERE {
      <$ppo> ppo:hasAccessSpace [ ppo:hasAccessQuery ?accessquery ] .
    }";
    //FILTER(REGEX(?rel_type, 'http://purl.org/vocab/relationship/', 'i')).

    $data = SMOBStore::query($query);
    $interests = array();
    error_log("interests queried",0);
    error_log(print_r($data, 1),0);
    if($data) {
      foreach($data as $t) {
        //$interests[$t['interest_label']] = $t['accessquery'];
        $accessquery = $t['accessquery'];
      }
    };
    //return $interests;
    return array($accessquery);
  }

  function get_initial_private_form_data() {
    $rel_types = PrivateProfile::get_rel_types();
    $rel_type_options = PrivateProfile::set_rel_type_options($rel_types);
    $interest_fieldsets = PrivacyPreferences::get_interests();
    $params = array("rel_type_options"=>$rel_type_options,
                    "rel_fieldsets"=>array(),
                    "rel_counter"=>0,
                    "interest_fieldsets"=>$interest_fieldsets,
                    "interest_counter"=>0
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
}
