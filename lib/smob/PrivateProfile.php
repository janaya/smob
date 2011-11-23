<?php

//require_once(dirname(__FILE__)."/../../config/config.php");
//require_once(dirname(__FILE__)."SMOBTools.php");
//require_once(dirname(__FILE__)."SMOBStore.php");

class PrivateProfile {
  // TODO: The private profile graph is the same as the profile graph, privacy preferences will decide what is visible
  function view_private_profile() {
    // as sempush can't parse n3, return profile as rdf/xml
    //$turtle = SMOBTools::triples_from_graph(ME_URL);
    //error_log("PrivateProfile::view_private_profile turtle",0);
    //error_log($turtle,0);
    //header('Content-Type: text/turtle; charset=utf-8'); 
    //return $turtle;

    //FIXME: the profile should be all in the same graph, and serve it depending
    // on the privacy settings
    //$rdfxml = SMOBTools::rdfxml_from_graph(ME_URL);
    $rdfxml = SMOBTools::get_profile();
    error_log("PrivateProfile::view_private_profile rdfxml",0);
    error_log($rdfxml,0);
    header('Content-Type: application/rdf+xml; charset=utf-8'); 
    return $rdfxml;
  }

  function get_interests($user_uri) {
    // cleaning from previous code errors
    //$query = "DELETE FROM <$user_uri> {
    //  <$user_uri> foaf:topic_interest <http://localhost:443/smob/ajax/undefined> .
    //  <http://localhost:443/smob/ajax/undefined> rdfs:label ?label . 
    //} WHERE {
    //  <$user_uri> foaf:topic_interest <http://localhost:443/smob/ajax/undefined> .
    //  <http://localhost:443/smob/ajax/undefined> rdfs:label ?label . 
    //}";
    //$query = "DELETE {
    //  <$user_uri> foaf:topic_interest <http://localhost:443/smob/ajax/undefined> .
    //  <http://localhost:443/smob/ajax/undefined> rdfs:label 'null' . 
    //}";
    //$data = SMOBStore::query($query);
    $interests = array();
    $query = "SELECT ?interest ?interest_label FROM <$user_uri> WHERE {
      <$user_uri> foaf:topic_interest ?interest .
      ?interest rdfs:label ?interest_label . }";
    $data = SMOBStore::query($query);
    error_log("interests queried",0);
    error_log(print_r($data, 1),0);
    if($data) {
      foreach($data as $t) {
        $interests[$t['interest_label']] = $t['interest'];
      }
    };
    return $interests;
  }

  function get_relationships($user_uri) {
    error_log("PrivateProfile::get_relationships",0);
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

  function get_initial_private_form_data() {
    $user_uri = ME_URL;
    $rel_fieldsets = array();
    $rel_persons = PrivateProfile::get_relationships($user_uri);
    error_log(print_r($rel_persons, 1), 0);
    $rel_types = SMOBTools::get_rel_types();
    $rel_type_options = SMOBTools::set_rel_type_options($rel_types);
    $index = 0;
    foreach($rel_persons as $rel_type=>$person) {
      $rel_fieldset = "
        <div id='rel_fieldset$index'>
           <select id='rel_type$index' name='rel_type$index' class='required'>";
      foreach($rel_types as $label=>$rel) {
        if($rel_type==$rel) {
          $option = "             <option name='$label' value='$rel' selected='$rel'>$label</option>";
        } else {
          $option = "             <option name='$label' value='$rel' >$label</option>";
        }
        $rel_fieldset .= $option;
      };
      $rel_fieldset .= "
           </select>
           <input type='text' name='person$index' id='person$index' value='$person' class='url required' size='50' />
           <a id='del_rel$index' href='' onClick='del(\"#rel_fieldset$index\"); return false;'>[-]</a>
        </div>
        </br>";
      $rel_fieldsets[$index] = $rel_fieldset;
      $index++;
    }
    error_log(print_r($rel_fieldsets, 1), 0);
    error_log($index);
    $rel_counter = $index;
    $interest_fieldsets = array();
    $interests = PrivateProfile::get_interests($user_uri);
    $index = 0;
    error_log("interests", 0);
    foreach($interests as $interest_label=>$interest) {
      error_log($interest, 0);
      error_log($interest_label,0);
      $interest_fieldset = "
        <div id='interest_fieldset$index'>
          <input type='text' id='interest_label$index' name='interest_label$index' value='$interest_label' class='url required' size='20' readonly />
          (<input type='text' id='interest$index' name='interest$index' value='$interest' class='url required' size='50' readonly />)
          <a id='del_rel$index' href='' onClick='del(\"#interest_fieldset$index\"); return false;'>[-]</a>
        </div>
        </br>";
      $interest_fieldsets[$index] = $interest_fieldset;
      $index++;
    }
    error_log(print_r($interest_fieldsets, 1), 0);
    error_log($index);
    $interest_counter = $index;
    $params = array("rel_type_options"=>$rel_type_options,
                    "rel_fieldsets"=>$rel_fieldsets,
                    "rel_counter"=>$rel_counter,
                    "interest_fieldsets"=>$interest_fieldsets,
                    "interest_counter"=>$interest_counter
                    );
    return $params;
  }
  
  function view_private_profile_form() {
    $file = 'private_profile_template.php';
    // if(IS_AJAX) {
    // } else {
    $params = PrivateProfile::get_initial_private_form_data();
    extract($params);
    ob_start();
    include($file);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }
  
  function save() {
    $query = "DELETE FROM <".ME_URL."> ";
    $res = SMOBStore::query($query);
    //error_log(var_dump($res, 1), 0);
    $query = "INSERT INTO <".ME_URL."> { $triples }";
    error_log($query, 0);
    $res = SMOBStore::query($query);
    //error_log(var_dump($res, 1), 0);
  }
  
}
