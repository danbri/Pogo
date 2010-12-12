<html>
<head><title>Open Graph checker</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<?php 
require_once 'plugins/viz/header.php';   
?>
</head>

<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
$l = OGDataGraph::$nslist = loadNamespaceList();

#$suite = 'testcases/imdb_tests.xml';
#$suite = 'testcases/basic_tests.xml'; # todo: are these loading ok?
$suite = 'testcases/_all.xml'; # everything we got!
$suite = 'testcases/fb_tests.xml';
#$suite = 'testcases/viz.xml'; # just 1 for viz plugin, for now.

$tests = OGDataGraph::getTests($suite); 

print "<h2>Loading $suite</h2>";

print "<p>Found ". sizeof($tests) .  " item(s).</p>";

$i = 0;
foreach ($tests as $test) {  
  $i++;
  print "<h3>Test: $test</h3></h3>";
  #print "LOADED NAMESPACES: ". OGDataGraph::$nslist;
  $og = new OGDataGraph();
  #print "LOADED NAMESPACES: ". OGDataGraph::$nslist;

  $og->readTest($test); # load JSON description of this test case
  #print "<h4>rapperCheck</h4>";
  # print $og->rapperCheck();
  $myrdf =  $og->arcParse();
  #print "<h4>arcParse</h4>";


  $table = $og->rdf2info(); # html table, fixed list of attribs

  print "<h4>Checking Status</h4>\n";
  try { 
  $og->checkfields();
  } catch (Exception $e) {
    print "Checker warning: ";

    # todo: move lang strings to localisation files

    if ($e->getMessage() == "BAD_TYPE_CHARS_FAIL") { print "Poor type name found. Please avoid capitals and punctuation except ':' and '_'.<br/>"; }
    if ($e->getMessage() == "NONDIGIT_APPID_CHARS_FAIL") { print "fb:app_id contains non-numeric characters (perhaps api key instead?)."; }
    #FAILED_PAGEID_NUMBERSONLY_REGEX

    print "<div>Error: ".$e->getMessage()."\n</div>"; # 
  }

print "<h4>Graphing triples</h4>";
$rdf = $og->getTriples();

print "Count: ". sizeof($rdf) . "<br/>\n";

print "<ul>";

# loop through our RDF
$js = '';
foreach ($rdf as $key => $value) {
  #print "<li style='font-size: small;'>" . $value['s'] . " " . $value['p'] . " " . $value['o'] . "<br/>\n";
  $predicate = $value['p'];
  print "<li style='font-size: small;'>".  $value['s'] . " " . OGDataGraph::shortify( $predicate) . " " . $value['o'] . "</li>";
  $js .= "g.addEdge(\"". OGDataGraph::shortify( $value['s']). "\", \""
		. OGDataGraph::shortify( $value['o'])
		. "\", { directed:true, label: \"".  OGDataGraph::shortify( $predicate)."\"}  );\n";    

#  $js .= "g.addNode(\"". OGDataGraph::shortify( $value['s']). "\", { label:\"".OGDataGraph::shortify( $value['s'])."\" } );\n";
  $js .= "g.addNode(\"". OGDataGraph::shortify( $value['s']). "\", { label:\"label: ".$value['s']."\" } );\n";
  $js .= "g.addNode(\"". OGDataGraph::shortify( $value['o']). "\", { label:\"".OGDataGraph::shortify( $value['o'])."\" } );\n";

}
print "</ul>";

print "<h4>OG Viz plugin</h4>";
print "<div id=\"canvas_$i\"></div><button id=\"redraw\" onclick=\"redraw() || viz_$i();\">[re]draw!</button>";
print "<button id=\"redraw\" onclick=\"viz_$i();\">draw</button>";
?>



<script type="text/javascript">
<!--
var redraw;
var height = 350;
var width = 650;
//window.onload = 
function viz_<?php print $i ?>() {
    var g = new Graph();
<?php
print $js; ?>
    var layouter = new Graph.Layout.Spring(g);     /* layout the graph using the Spring layout implementation */
    layouter.layout();
    var renderer = new Graph.Renderer.Raphael('canvas_<?php print $i ?>', g, width, height);    /* draw the graph using the RaphaelJS draw implementation */
    renderer.draw();
    redraw = function() {
        layouter.layout();
        renderer.draw();
    };
};
-->
</script>


<?php
  $meta = $og->getmeta();
  # print "<div>Metadata: ". $meta['url']  . "</div>\n"; #todo: add check/exception when no item loaded
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
}  # loop thru testcases







/*
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/type movie 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/url http://www.imdb.com/title/tt0117500/ 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/image http://ia.media-imdb.com/images/rock.jpg 
*/
?>

<hr />
[pogo checker alpha0]
</body>
</html>
