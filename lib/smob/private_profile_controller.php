<?php 
require_once(dirname(__FILE__)."/../../config/config.php");
require_once(dirname(__FILE__)."SMOBTools.php");
require_once(dirname(__FILE__)."SMOBStore.php");

  function private_profile() {
    $turtle = '
    <https://localhost/smob/private#me> a foaf:Person ;
        foaf:name "smob" ;
        foaf:topic_interest <http://dbpedia.org/resource/Semantic_Web> ;
        foaf:workplaceHomepage <http://deri.ie/> ;
        foaf:knows <http://localhost/smob2/me> ;
        rel:worksWith <http://localhost/smob2/me> .
    <http://localhost/smob2/me> a foaf:Person ;
        foaf:name "smob2" ;
        foaf:workplaceHomepage <http://deri.ie/> .
    ';
    //$query = "SELECT * FROM <".SMOB_ROOT."me/private> WHERE {?s ?p ?o}";
    //$res = SMOBStore::query($query);
    $turtle = SMOBTools::triples_from_graph(SMOB_ROOT."me/private");
    header('Content-Type: text/turtle; charset=utf-8'); 
    echo $turtle;
    exit();
  }

  function get_rel_types() {
    $rels = array();
    $jsonfile = fopen('../../relationship.json');
    $jsontext = fread($jsonfile);
    fclose($jsonfile);
    $json = json_decode($jsontext, true);
    foreach ($json as $rel) {
      error_log(var_dump($rel, 1), 0);
      if (strpos($rel, "http://purl.org/vocab/relationship/") === 0) {
        if (array_key_exists('http://www.w3.org/2000/01/rdf-schema#label', $rel)) {
          $label = $json[$rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          error_log($label);
          $rels[$label] = $rel;
        };
      };
    };
    error_log(var_dump($rels, 1), 0);
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
    $fieldset_rels    = '
      <fieldset id="rel_fieldset0"><legend>Relationship</legend> 
        <select id="rel_type0" name="rel_type0"> 
        <option value=""></option> 
        </select> 
        <input id="person0" name="person0" type="text" />
      </fieldset>';
    $lod = "";
    $rels = array();
    $persons = array();
    $i = 0;
    foreach($data as $triple) {
      $s = $triple['s'];
      $p = $triple['p'];
      $o = $triple['o']; 
      if ($p == "<http://xmlns.com/foaf/0.1/topic_interest>") {
        $lod = $lod + " " + $o;
      } elseif (strpos($p, "http://purl.org/vocab/relationship/") === 0) {
        $rels['rel_type'+$i] = $p;
        $persons['person'+$i] = $o;
      }
      $i++;
    }
    $params = "";
  }


}
