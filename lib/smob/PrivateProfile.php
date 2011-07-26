<?php

//require_once(dirname(__FILE__)."/../../config/config.php");
//require_once(dirname(__FILE__)."SMOBTools.php");
//require_once(dirname(__FILE__)."SMOBStore.php");

class PrivateProfile {

  function view_private_profile() {
    $turtle = SMOBTools::triples_from_graph(SMOB_ROOT."me/private");
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
          $rels[$label] = $rel;
        };
      };
    };
    return $rels;
  }

  function set_rel_type_options() {
    $rels = get_rel_types();
    $options = "";
    foreach($rels as $label=>$rel) {
      $options = $select + "<option value='" + $rel + "' >" + $label + "</option>";
    };
    return $options;
  }

  function get_initial_private_form_data() {
    $graph = SMOB_ROOT."me/private";
    $query = "
SELECT *
WHERE { 
  GRAPH <$graph> {
    ?s ?p ?o
  }
}";
    $data = SMOBStore::query($query);
    $lod = "";
    $rels = array();
    $persons = array();
    $i = 0;
    foreach($data as $triple) {
      error_log(print_r($triple, 1), 0);
      $s = $triple['s'];
      $p = $triple['p'];
      $o = $triple['o']; 
      if ($p == "http://xmlns.com/foaf/0.1/topic_interest") {
        $lod = $lod + " " + $o;
      } elseif (strpos($p, "http://purl.org/vocab/relationship/") === 0) {
        $rels[$i] = $p;
        $persons[$i] = $o;
      }
      $i++;
    }
    error_log(print_r($persons, 1), 0);
    error_log(print_r($rels, 1), 0);
    $rel_types = PrivateProfile::get_rel_types();
    error_log(print_r($rel_types, 1), 0);
    $fieldsets = array();
    $fieldset = '';
    foreach($persons as $index=>$person) {
      error_log($person,0);
      error_log($index, 0);
      $fieldset = '
      <fieldset id="rel_fieldset'.$index.'"><legend>Relationship</legend> 
        <select id="rel_type'.$index.'" name="rel_type'.$index.'">';
      error_log($fieldset, 0);
      foreach($rel_types as $label=>$rel) {
        if($rels[$index]==$rel) {
          $option = '<option value="'.$rel.'" selected="'.$rel.'">'.$label.'</option>';
        } else {
          $option = '<option value="'.$rel.'">'.$label.'</option>';
        }
        $fieldset = $fieldset.$option;
      };
      error_log($fieldset, 0);
      $fieldset = $fieldset.'
        </select> 
        <input id="person'.$index.'" name="person'.$index.'" type="text" value="'.$person.'"/>
      </fieldset>';
      error_log($fieldset, 0);
      $fieldsets[$index] = $fieldset;
    }
    error_log(print_r($fieldsets, 1), 0);
//    return array('fieldsets'=>$fieldsets);
    return $fieldsets;
  }
  
  function view_private_profile_form() {
    $file = 'private_profile_template.php';
    $fieldsets = PrivateProfile::get_initial_private_form_data();
    extract($fieldsets);
    ob_start();
    include($file);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }
}
