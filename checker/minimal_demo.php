<html><head><title>minimal demo</title></head>
<body>
<?php 
require_once 'OGDataGraph.php'; 
require_once 'CheckUI.php';

$url = 'http://www.rottentomatoes.com/m/blade_runner/';
$og = new OGDataGraph();

try {
#$og->readFromURL('full', $url); # real RDFa parser
$og->readFromURL('lite', $url);  # just look for OG markup
} catch (Exception $e) {
  print "Parsing failed: ".$e;
}

print "<p>Title is " . $og->og_title ."</p>";
print "<p>RDFa triple count: " . sizeof( $og->triples ) . "</p>";
print CheckUI::simpleTable($og);

?>
</body>
</html>
