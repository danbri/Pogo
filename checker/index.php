<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />

<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
$base = OGDataGraph::$my_base_uri;
$me = 'index.php';

function isValidURL($url) { return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); }
$mode = $_GET['mode'];
if (is_null($mode)) {$mode='lite';}
if ($mode &&  !preg_match( '/(^full$|^lite$|^auto$|^viz$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
$url = $_GET['url'];

print "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
print "Input URL:<input type=\"text\" size=\"70\" name=\"url\" value=\"$url\"/><input type=\"submit\" value=\"go\"/>";
print '<div style="float: right"><input type="radio" name="mode" value="auto" checked="true" /> auto ';
print '<input type="radio" name="mode" value="lite" /> lite';
print '<input type="radio" name="mode" value="full" /> full</div>';
print '</form>';
print "<small>cached example(s): legend_guardians ";
print "(<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=lite\">lite</a> | ";
print "<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=full\">full</a>) <br/> ";

print "live example(s): bladerunner (<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=lite\">lite</a> | ";
print "<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=full\">full</a>)";
print " fb developers: <a href='?url=http://developers.facebook.com/tools/lint/&mode=lite'>lite</a><br/>";
print "bad e.g.: http://developers.facebook.com/tools/lint/examples/bad_app_id<br/>";
print "</small>";

if (!$url) {  exit(1); }
if (!isValidURL($url)){ exit("Unsupported URL syntax."); }
?>

<body>
<?php 
print "<p>URL: $url   (mode: <b>" . $mode  ."</b>) </p>";

print "<h3>Info</h3></h3>";

$og = new OGDataGraph();

try {
$og->readFromURL($mode, $url); # mode defaults to lite
} catch (Exception $e) {
  print "Parsing failed: ".$e;
}

if ($mode == 'lite' && sizeof($og->triples)==0) {
  print "Lite parser gave us an empty graph.";
  
}

if ($mode == 'full' && sizeof($og->triples)==0) {
  print "Full parser gave us an empty graph.";
  
}


print $og->simpleTable();

print "<h3>Checks</h3>";
  try {
  $og->checkfields();
  } catch (Exception $e) {
    print "Checker warning: ";
#    if ($e->getMessage() == "BAD_TYPE_CHARS_FAIL") { print "Poor type name found. Please avoid capitals and punctuation except ':' and '_'.<b$
#    if ($e->getMessage() == "NONDIGIT_APPID_CHARS_FAIL") { print "fb:app_id contains non-numeric characters (perhaps api key instead?)."; }
    #FAILED_PAGEID_NUMBERSONLY_REGEX
    print "<div>Error: ".$e->getMessage()."\n</div>"; 
  }


?>


<hr />
[pogo checker] status: v1.0, <em>experimental release.</em>
</body>
</html>
