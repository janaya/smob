<?php

/*
    The class responsible of all the actions towards the local triple-store
*/

class SMOBStore {
    
    var $store;
    
    function ask($query) {
        return SMOBStore::query("ASK { $query }", true);
    }
    
    function query($query, $ask=false) {
        global $arc_config;
        
        if(!$arc_config) {
            include_once(dirname(__FILE__).'/../arc/ARC2.php');
            include_once(dirname(__FILE__).'/../../config/config.php');
        }                
        
        $store = ARC2::getStore(SMOBTools::arc_config());
        if (!$store->isSetUp()) {
            $store->setUp();
        }
        //FIXME: add as globals
        //$ns = array(
        //'foaf' => 'http://xmlns.com/foaf/0.1/',
        //'rel' => 'http://purl.org/vocab/relationship/',
        //'cert'  => "http://www.w3.org/ns/auth/cert#",
        //'rsa' => "http://www.w3.org/ns/auth/rsa#",
        //'rdfs' => "http://www.w3.org/2000/01/rdf-schema#",
        //'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'
        //);
        //$conf = array('ns' => $ns);
  
        $query = "
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX sioct: <http://rdfs.org/sioc/types#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX tags: <http://www.holygoat.co.uk/owl/redwood/0.1/tags/>
PREFIX moat: <http://moat-project.org/ns#>
PREFIX opo: <http://online-presence.net/opo/ns#>
PREFIX opo-actions: <http://online-presence.net/opo-actions/ns#>
PREFIX ctag: <http://commontag.org/ns#>
PREFIX smob: <http://smob.me/ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rev: <http://purl.org/stuff/rev#>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX rel: <http://purl.org/vocab/relationship/>
PREFIX ppo: <http://vocab.deri.ie/ppo#>
PREFIX acl: <http://www.w3.org/ns/auth/acl#>
PREFIX rsa: <http://www.w3.org/ns/auth/rsa#> 
PREFIX cert: <http://www.w3.org/ns/auth/cert#>

        $query";        
        $rs = $store->query($query);
        
        if ($errors = $store->getErrors()) {
            // Log errors.
           error_log("Store::query errors: ",0);
           error_log(print_r($errors,1),0);
           foreach ($errors as $error) {
             trigger_error($error, E_USER_ERROR);
           }
           return NULL;
//             error_log("SMOB SPARQL Error:\n" . join("\n", $errors));
//             return array();
        }
//          error_log(print_r($rs, 1),0);
        if($ask) {
            return $rs['result'];
        } else {
//             return $rs['result']['rows'];
            if ($rs['query_type'] == 'insert') {
                return $rs['result'];
            } else {
                return $rs['result']['rows'];
            }
//             if (array_key_exists('rows', $rs)) {
//                 return $rs['result']['rows'];
//             } else {
//                 return $rs['result'];
//             }
        }
    }
    
    
}

?>
