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
if ($mode &&  !preg_match( '/(^full$|^lite$|^viz$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
$url = $_GET['url'];

print "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
print "URL:<input type=\"text\" size=\"50\" name=\"url\" /><input type=\"submit\" value=\"go\"/>\n</form>";

#print "<div>local example(s): legend_guardians ";
#print "(<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=lite\">lite</a> | ";
#print "<a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=full\">full</a>) <br/> ";

print "examples: imdb.com (<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=lite\">lite</a> | ";
print "<a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=full\">full</a>) ";


# Erm, the FB linter page doesn't parse with the full RDFa parser here, oops. Omitting for now!
#
# print "developers.facebook.com (<a href='?url=http://developers.facebook.com/tools/lint/&mode=lite'>lite</a> | ";
# print "<a href='?url=http://developers.facebook.com/tools/lint/&mode=full'>full</a>) ";

print "ogp.me (<a href='?url=http://ogp.me/&mode=lite'>lite</a> | <a href='?url=http://ogp.me/&mode=full'>full</a>) ";

print "rottentomatoes.com (<a href='?url=http://www.rottentomatoes.com/m/legend_of_the_guardians/&mode=lite'>lite</a> | ";
print "<a href='?url=http://www.rottentomatoes.com/m/legend_of_the_guardians/&mode=full'>full</a>)";

print "</div>";

print "<p>Note: <em>final version will hide lite/full distinction from end users.</p>";

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
print $og->simpleTable();
?>


<hr />
[pogo checker] status: v1.0, <em>experimental release.</em>
</body>
</html>
