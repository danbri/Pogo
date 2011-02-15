<html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />
<?php 
require_once 'page_top.php';
?>
<!-- mapping stuff -->
<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2"></script>
<script type="text/javascript">var map = null;function GetMap(lat,lon){map = new VEMap('myMap');map.LoadMap(new VELatLong(lat, lon), 10 ,'h' ,false);}</script>

<?php
require_once 'plugins/viz/header.php';


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


if (!$url) { 
#  print "<p>Learn more about the Open Graph protocol from <a href=\"http://ogp.me\">ogp.me</a>, <a href=\"http://en.wikipedia.org/wiki/Facebook_Platform#Open_Graph_protocol\">Wikipedia</a> or <a href=\"http://developers.facebook.com/docs/opengraph/\">Facebook</a>.</p>";
  print CheckUI::simpleForm($url);  
  print "<p class=\"learn\">Learn more about the Open Graph protocol from <a href=\"http://ogp.me\">ogp.me</a>,<a href=\"http://en.wikipedia.org/wiki/Facebook_Platform#Open_Graph_protocol\">Wikipedia</a> or <a href=\"http://developers.facebook.com/docs/opengraph/\">Facebook</a>.</p>";

  print "<br/><br/><div class=\"footer\"><hr/>Open Graph checker, 2011</div>\n";


exit(1); 

}

if (!OGDataGraph::isValidURL($url)){ exit("Unsupported URL syntax."); }
$success = 0;
?>
<body onload="javascript:hideSection()">

<?php 

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

print "<h2 class=\"results\">Results</h2>";
print "<p class=\"results\"><b>Overview:</b> lite parser found $tc_lite items, full parser found $tc_full. <span class=\"showmore\"><a href=\"#\" onclick=\"javascript:showSection();return false;\">Show more &gt;</a></span>";

print CheckUI::simpleForm($url);


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

print "<h3>Problems</h3>";

if ( count ($report_combi) > 0 ) {

  if ( count($report_combi) == 1 ) {
    print "1 problem found.";
  } else {
    print count($report_combi) . " problems found (combined results).";
  }

print '<p>'.CheckUI::tableFromReport($report_combi).'</p>';

print "   <div class=\"clearboth\"/><span class=\"hidedetail\"><a href=\"#\" onclick=\"javascript:hideSection();return false;\">&lt; Hide details</a></span>";

} else {
print "<p>No problems found.</p>";
print "   <div class=\"clearboth\"/><span class=\"hidedetail\"><a href=\"#\" onclick=\"javascript:hideSection();return false;\">&lt; Hide details</a></span>";
}

print "<div class=\"detail\">";
print "<br/><hr/>";
print "<p>This checker runs two parser against your content. Full details of any problems are shown here.</p>\n";

if ( count ($report_lite) > 0) {
  print '<h3>Problem Report (Lite parser)</h3><p class="liteissues">'.CheckUI::tableFromReport($report_lite).'</p>';
}

if ( count ($report_full) > 0) {
print '<h3>Problem Report (Full parser)</h3> <p class="fullissues">'.CheckUI::tableFromReport($report_full).'</p>';
}
print "</div>\n";

print "<h3>Results from 'Lite' parser</h3>\n";
print CheckUI::simpleTable($og_lite);

print "<br/>\n\n";
print "<table class=\"fulltriples\" class=\"detail\">\n";
print "<tr><th class=\"s\">entity ID</th><th class=\"p\">property</th><th class=\"p\">value</th></tr>\n";
foreach ($og_lite->triples as $t)  {
  echo "<tr><td>" .$t['s']. "</td><td>". $t['p'] ."</td><td>". $t['o'] ."</td></tr>\n";
} 
print "</table><br/ class=\"clearboth\">\n";


print "<div class=\"detail\"><h3>Results from 'Full' parser</h3>\n\n";
print CheckUI::simpleTable($og_full);

print "<br/>\n\n";
print "<table class=\"fulltriples\">\n";
print "<tr><th class=\"s\">entity ID</th><th class=\"p\">property</th><th class=\"p\">value</th></tr>\n";
foreach ($og_full->triples as $t)  {
  echo "<tr><td>" .$t['s']. "</td><td>". $t['p'] ."</td><td>". $t['o'] ."</td></tr>\n";
} 
print "</table><br/>\n";

print "<div class=\"clearboth\"/><p><br/><span class=\"hidedetail\"><a href=\"#\" onclick=\"javascript:hideSection();return false;\">&lt; Hide details</a></span></p>";
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


 # VIZ TESTS

if ($mode == 'viz') { 
  require_once 'plugins/viz/ns_prefix.php';
  OGDataGraph::$nslist = loadNamespaceList();
  $js = '';
  foreach ($og_lite->triples as $key => $value) {
    #print "<li style='font-size: small;'>" . $value['s'] . " " . $value['p'] . " " . $value['o'] . "<br/>\n";
    $predicate = $value['p'];
    # print "<li style='font-size: small;'>s:".  $value['s'] . " p: " . OGDataGraph::shortify( $predicate) . " o: " . $value['o'] . " <br/><br/></li>";
    $js .= "g.addEdge(\"". OGDataGraph::shortify( $value['s']). "\", \""
                . OGDataGraph::shortify( $value['o'])
                . "\", { directed:true, label: \"".  OGDataGraph::shortify( $predicate)."\"}  );\n";
    $js .= "g.addNode(\"". OGDataGraph::shortify( $value['s']). "\", { label:\"".OGDataGraph::shortify( $value['s'])."\" } );\n";
    $js .= "g.addNode(\"". OGDataGraph::shortify( $value['o']). "\", { label:\"".OGDataGraph::shortify( $value['o'])."\" } );\n";
  }

  print "<h4>Viz plugin</h4>";
  print "<p><div id=\"canvas_lite\"></div><button id=\"redraw\" onclick=\"redraw()\">redraw</button></p>";
}


?>


<script type="text/javascript"> 
<!--
var redraw;
var height = 650;
var width = 750;
window.onload = function viz_lite() {
    var g = new Graph();
<?php
print $js; ?>
    var layouter = new Graph.Layout.Spring(g);     /* layout the graph using the Spring layout implementation */
    layouter.layout();
    var renderer = new Graph.Renderer.Raphael('canvas_lite', g, width, height);    /* draw the graph using the RaphaelJS draw implementation */
    renderer.draw();
    redraw = function() {
        layouter.layout();
        renderer.draw();
    };
};
-->


hideSection(); // otherwise we over-write this from body element.
</script>


<br/><br/><div class="footer"><hr/>Open Graph checker, 2011</div>
</body>
</html>
