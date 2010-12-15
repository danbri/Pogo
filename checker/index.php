<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />

<?php 
require_once 'page_top.php';
?>
<!-- mapping stuff -->
<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2"></script>
<script type="text/javascript">var map = null;function GetMap(lat,lon){map = new VEMap('myMap');map.LoadMap(new VELatLong(lat, lon), 10 ,'h' ,false);}</script>

<?php
require_once 'OGDataGraph.php'; 
require_once 'OG_Checklist.php';
require_once 'CheckUI.php';
$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;
$me = basename($_SERVER['SCRIPT_FILENAME']); # index.php by default
$mode = $_GET['mode'];
if (is_null($mode)) {$mode='auto';}
if ((!is_null($mode)) &&  !preg_match( '/(^full$|^lite$|^auto$|^viz$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
$url = $_GET['url'];

print CheckUI::simpleForm($url);
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
  $rep2 =  Checker::checkall($og2);
  print 'Report2: '.CheckUI::tableFromReport($rep2);
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
   $report = Checker::checkall($og2);
   print "Report: ". CheckUI::tableFromReport($report);
  } catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
    $success = 0;
  }

}

  print "<h3>Info</h3>";
  print CheckUI::simpleTable($og);

  try {
  Checker::checkall($og);
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
