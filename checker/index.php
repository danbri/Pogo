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
<body onload="javascript:hideSection()">

<?php 
print "<p>URL: $url</p>\n <h3>Results</h3>";
#   (mode: <b>" . $mode  ."</b>)

$og_lite = new OGDataGraph();
$og_full = new OGDataGraph();

# LITE PARSER
try { 
  $og_lite->readFromURL('lite', $url); 
} catch (Exception $e) 
  { print "Lite Parsing failed: ".$e; 
}

# FULL PARSER
try {
$og_full->readFromURL('full', $url); 
} catch (Exception $e) {
    print "Full parsing failed: ".$e;
}

$tc_lite = sizeof($og_lite->triples);
$tc_full = sizeof($og_full->triples);

print "<div><b>Count:</b> <em>lite parser found <b>$tc_lite</b> properties, full parser found <b>$tc_full</b></em></div>\n\n";

#print 'LITEContent: '. $og_lite->content;
#print 'FULLContent: '. $og_full->content;

# sizeof($og->triples)
# if ( sizeof($og->triples)>0) { $success=1; }


#### RUN THE CHECKLIST

# CHECK LITE GRAPH
try {
$report_lite =  Checker::checkall($og_lite);
#print '<p>Checked lite graph.</p>';
} catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
}
  
# CHECK FULL GRAPH
try {
$report_full =  Checker::checkall($og_full);
# print '<p>Checked full graph.</p>';
} catch (Exception $e) {
    print "<p>".$e->getMessage().": ". $msg[ $e->getMessage() ]."</p>" ;
}

$report_combi = array();
foreach ($report_lite as $k => $v ) { $report_combi[$k] = $v; } 
foreach ($report_full as $k => $v ) { $report_combi[$k] = $v; } 

# default to showing merged table
print '<small>'.CheckUI::tableFromReport($report_combi).'</small>';


print '<p><b>More details...?</b> [<big><b><a href="#" onclick="javascript:showSection();return false;">+</a></b></big] | [<big><b><a href="#" onclick="javascript:hideSection();return false;">-</a></b></big>]';

print '<div class="detail"><b>Lite parser issues:</br><br/><small>'.CheckUI::tableFromReport($report_lite).'</small>';
print '<b>Full parser issues:</b><br/><small>'.CheckUI::tableFromReport($report_full).'</small></div>';
print "</p>\n";

print "<b>Lite OGP parser</b>:\n";
print CheckUI::simpleTable($og_lite);


print '<p><b>More details...?</b> [<big><b><a href="#" onclick="javascript:showSection();return false;">+</a></b></big] | [<big><b><a href="#" onclick="javascript:hideSection();return false;">-</a></b></big>]</p>';

print "<div class=\"detail\">Full RDFa parser results:\n";
print CheckUI::simpleTable($og_full);
print "</div>\n";



if ($og_lite->og_latitude) { $og = $og_lite; } # pick an OGP instance
if ($og_full->og_latitude) { $og = $og_full; } # default to lite; upgrade to full if still got geo
if ($og) {
  $lat = $og->og_latitude;
  $lon = $og->og_longitude;
  if ($lat) {
    print '<h4>Geo</h4>';				# http://www.microsoft.com/maps/isdk/ajax/
    print "<p>Lat: $lat Long: $lon</p>";
    print "<div id='myMap' style='position:relative; width:550px; height:400px; padding: 10px;'></div>";
    print "<script type='text/javascript'>GetMap($lat, $lon);</script>";
  }
}

?>
<hr />
[pogo checker] status: v1.0, <em>pre-release.</em>
</body>
</html>
