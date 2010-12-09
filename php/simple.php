<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" /></head>

<?php 
require_once 'page_top.php';

function isValidURL($url) { return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); }

$mode = $_GET['mode'];
if ($mode &&  !preg_match( '/(^full$|^lite$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 

$url = $_GET['url'];

if (!$url) {
  print "<form action=\"simple.php\" method=\"get\" name=\"checker\">\n";
  print "URL:<input type=\"text\" size=\"50\" name=\"url\" /><input type=\"submit\" name=\"go\"/>\n</form>";
  print "<small>examples: <a href=\"?url=http://localhost/pogo/Pogo/php/testcases/imdb/legend_guardians.cache\">legend_guardians</a> ";
  print "(<a href=\"?url=http://localhost/pogo/Pogo/php/testcases/imdb/legend_guardians.cache?mode=lite\">lite</a> | ";
  print "<a href=\"?url=http://localhost/pogo/Pogo/php/testcases/imdb/legend_guardians.cache?mode=full\">full</a>)";
  print "</small>";
  exit(1); # todo: show simple form here
}
if (!isValidURL($url)){ exit("Unsupported URL syntax."); }

?>
<body>

<?php 
require_once 'OGDataGraph.php'; 
$suite = 'testcases/viz.xml'; 
$tests = OGDataGraph::getTests($suite); 

print "URL: $url<br/>";

print "<p>Found ". sizeof($tests) .  " item(s).</p>";
$i = 0;
foreach ($tests as $test) {  
  $i++;
  print "<h3>Test: $test</h3></h3>";
  $og = new OGDataGraph();
  $og->readTest($test); # load JSON description of this test case
  $myrdf =  $og->arcParse();
  $table = $og->rdf2info(); # html table, fixed list of attribs
  print "<h4>Checking Status</h4>\n";
  try { 
  $og->checkfields();
  } catch (Exception $e) {
    print "Checker warning: ";
    # todo: move lang strings to localisation files
    if ($e->getMessage() == "BAD_TYPE_CHARS_FAIL") { print "Poor type name found. Please avoid capitals and punctuation except ':' and '_'.<br/>"; }
    if ($e->getMessage() == "NONDIGIT_APPID_CHARS_FAIL") { print "fb:app_id contains non-numeric characters (perhaps api key instead?)."; }
    print "<div>Error: ".$e->getMessage()."\n</div>"; # 
  }

$rdf = $og->getTriples();
print "Count: ". sizeof($rdf) . "<br/>\n";
print "<ul>";
$js = '';
foreach ($rdf as $key => $value) {
  $predicate = $value['p'];
  print "<li style='font-size: small;'>".  $value['s'] . " " . OGDataGraph::shortify( $predicate) . " " . $value['o'] . "</li>";
  $js .= "g.addEdge(\"". OGDataGraph::shortify( $value['s']). "\", \""
		. OGDataGraph::shortify( $value['o'])
		. "\", { directed:true, label: \"".  OGDataGraph::shortify( $predicate)."\"}  );\n";    

  $js .= "g.addNode(\"". OGDataGraph::shortify( $value['s']). "\", { label:\"label: ".$value['s']."\" } );\n";
  $js .= "g.addNode(\"". OGDataGraph::shortify( $value['o']). "\", { label:\"".OGDataGraph::shortify( $value['o'])."\" } );\n";
}
  
print "</ul>";
  $meta = $og->getmeta();
  $status = $meta['status'];
  print "<h4>Actual Status (from testcase repository metadata)</h4>\n";
  print "<a href=\"http://developers.facebook.com/tools/lint/?url=".$meta['url']."\">fb-lint</a><br/>";
  if ($status == "valid") { 
    print "VALID! &#x2714;";
  } else {
    print "INVALID! &#x2717; <br/>";
    print "FBLint warning: " . $meta['warning'];
  }  
  print "<h4>Info</h4>\n";
  print $table;
  print "<h4>Debug</h4>\n";
  print "<p>todo: show table of extracted fields here, as <a href='http://developers.facebook.com/tools/lint/?url=http://developers.facebook.com/tools/lint/examples/api_key'>fb linter</a>.</p>";
  print "<br /><br />\n";
} 
 # loop thru testcases
?>
<hr />
[pogo checker alpha0]
</body>
</html>
