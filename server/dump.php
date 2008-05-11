<?php

include_once(dirname(__FILE__).'/config.php');

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
  $store->setUp();
}

$q = "
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX sioct: <http://rdfs.org/sioc/types#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dct: <http://purl.org/dc/terms/>
select distinct ?g ?s ?p ?o
where { 
  graph ?g {?s ?p ?o}
}
";

$rs = $store->query($q);

// var_dump($rs);

$graphs = array();

foreach ($rs['result']['rows'] as $r) {
  list($g, $gt, $s, $st, $p, $pt, $o, $ot) = array_values($r);
  $triple = array($s, $p, $o);
  if ($graphs[$g])
    $graphs[$g][] = $triple;
  else
    $graphs[$g] = array($triple);
}

print "<h1>HTML dump of the documents aggregated</h1>\n";

foreach ($graphs as $g => $triples) {
  print "<h2>$g</h2>\n<table border=1>\n";
  array_multisort($triples);

  foreach ($triples as $triple) {
    echo "<tr><td>$triple[0]</td><td>$triple[1]</td><td>$triple[2]</td></tr>\n";
  }
  print "</table>\n";
}
?>