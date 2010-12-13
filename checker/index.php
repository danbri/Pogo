<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />

<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
require_once 'OG_L18N.php';
?>
<!-- mapping stuff -->
<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2"></script>
<script type="text/javascript">
         var map = null;
         function GetMap(lat,lon){
            map = new VEMap('myMap');
            map.LoadMap(new VELatLong(lat, lon), 10 ,'h' ,false); //http://www.microsoft.com/maps/isdk/ajax/ 'b' birdseye
         }   
</script>

<?php
$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;
$me = basename($_SERVER['SCRIPT_FILENAME']); # index.php by default

$mode = $_GET['mode'];
if (is_null($mode)) {$mode='auto';}
if ($mode &&  !preg_match( '/(^full$|^lite$|^auto$|^viz$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
$url = $_GET['url'];

print "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
print "Input URL:<input type=\"text\" size=\"70\" name=\"url\" value=\"$url\"/><input type=\"submit\" value=\"go\"/>";

#print '<div style="float: right"><input type="radio" name="mode" value="auto" checked="true" /> auto ';
#print '<input type="radio" name="mode" value="lite" /> lite';
#print '<input type="radio" name="mode" value="full" /> full</div>';

print '</form>';
print "<small>cached examples: <a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=auto\">legend_guardians</a> <br/>";

print "live examples: <a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=auto\">bladerunner</a> | ";
print " <a href='?url=http://developers.facebook.com/tools/lint/&mode=auto'>developers.facebook.com</a><br/>";
print "bad examples: <a href='?url=http://developers.facebook.com/tools/lint/examples/bad_app_id'>bad_app_id</a><br/>";
print 'geo: <a href="?url=http://localhost/pogo/Pogo/checker/testcases/ogp/geo1.cache#">california</a>';
print "</small>";

if (!$url) {  exit(1); }
if (!OGDataGraph::isValidURL($url)){ exit("Unsupported URL syntax."); }
$success = 0;
?>

<body>
<?php 
print "<p>URL: $url   (mode: <b>" . $mode  ."</b>) </p>";
print "<h3>Checker</h3>";

$og = new OGDataGraph();

#print "<hr/>";
try {
if  ($mode=='auto') { $mode='lite'; } # messy but reasonable 
$og->readFromURL($mode, $url); # mode defaults to lite
} catch (Exception $e) {
  print "Parsing failed: ".$e;
}

if ($mode == 'lite' && sizeof($og->triples)==0) {
  print "Basic OG parsing failed. Retrying with a full RDFa parser; sometimes this finds more.";
  $og2 = new OGDataGraph();
  try {
    $og2->readFromURL('full', $url); 
    $success = 1;
  } catch (Exception $e) {
    print "Full parsing failed: ".$e;
  }
  print "Reparsing result was: ". sizeof($og2->triples) . "...graph entries.";
  if ( sizeof($og2->triples)>0) { $success=1; }
  try {
  $og2->checkfields();
  } catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
    $success = 0;
  }
  
}

if ($mode == 'full' && sizeof($og->triples)==0) {
  print "Full parser gave us an empty graph. Retrying with simple OG parser.:";
  $og2 = new OGDataGraph();
  try {
    $og2->readFromURL('lite', $url); 
  } catch (Exception $e) {
    print "<p>Full parsing failed: ".$e."</p>";
  }
  print "Reparsing result was: ". sizeof($og2->triples) . "...graph entries.";
  if ( sizeof($og2->triples)>0) { $success=1; }
  try {
  $og2->checkfields();
  } catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
    $success = 0;
  }
}


#if ($success != 0) { 
  print "<h3>Info</h3>";
  print $og->simpleTable();
#}

  try {
  $og->checkfields();
  } catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
  }

  # http://www.microsoft.com/maps/isdk/ajax/
  $lat = $og->og_latitude;
  $lon = $og->og_longitude;
  if ($lat) {
    print '<h4>Geo</h4>';
    print "<p>Lat: $lat Long: $lon</p>";
    print "<div id='myMap' style='position:relative; width:550px; height:400px; padding: 10px;'></div>";
    print "<script type='text/javascript'>GetMap($lat, $lon);</script>";
  }

?>
<hr />
[pogo checker] status: v1.0, <em>experimental release.</em>
</body>
</html>
