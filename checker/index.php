<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" /></head>

<?php 
# 

require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
$base = OGDataGraph::$my_base_uri;
$me = 'index.php';
function isValidURL($url) { return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); }
$mode = $_GET['mode'];
if (is_null($mode)) {$mode='lite';}
if ($mode &&  !preg_match( '/(^full$|^lite$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
$url = $_GET['url'];

print "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
print "URL:<input type=\"text\" size=\"50\" name=\"url\" /><input type=\"submit\" name=\"go\"/>\n</form>";
print "<small>cached example(s): legend_guardians ";
print "(<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=lite\">lite</a> | ";
print "<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=full\">full</a>) <br/> ";
print "live example(s): bladerunner (<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=lite\">lite</a> | ";
print "<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=full\">full</a>)</small>";
if (!$url) {  exit(1); }
if (!isValidURL($url)){ exit("Unsupported URL syntax."); }
?>

<body>
<?php 
print "<p>URL: $url   <b>" . $mode  ."</b> </p>";

print "<h3>Info</h3></h3>";
verbose("Fetching $url");
$og = new OGDataGraph();

try {
$og->readFromURL($mode, $url); # mode defaults to lite
} catch (Exception $e) {
  print "Parsing failed: ".$e;
}
print $og->simpleTable();
?>


<hr />
[pogo checker alpha0]
</body>
</html>
