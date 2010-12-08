<html>
<head><title>Pogo Open Graph Protocol checker: demo</title>
<style type="text/css">
body { margin: 9em; }
h2 { background: #ffb;  }
h3 { background: #ffd; border-style: solid; border-width: normal; padding: 3px; }
h4 { background: #ffe; border-style: groove; border-width: thin; margin-left: .5em; margin-right: 15em; }
.ogfield { font-weight: bold; background: #CCF;}
</style>
<?php 
require_once 'plugins/viz/header.php';   ?>
</head>
<body><h1>OpenGraph checker</h1>
<p>See also Facebook's <a href="http://developers.facebook.com/tools/lint/?url=http://developers.facebook.com/tools/lint/examples/good">linter</a>.</p>

<!-- driven by testcases with json metadata:
meta: { "url": "http://developers.facebook.com/tools/lint/examples/bad_type", "testid": "bad_type", "testgroup": "fb/examples", "cache_date": "Sun 14 Nov 2010 13:46:47 CET", 
# "valid_html": false, "uses_rdfa": true, "uses_og": true, "extended": true, "triple_count": 0, "warning": "Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+", "home_site": "http://www.facebook.com/" } -->
<?php 
require_once 'OGDataGraph.php'; 
$l = OGDataGraph::$nslist = loadNamespaceList();

print "LOADED NAMESPACES: ". OGDataGraph::$nslist;

#$suite = 'testcases/imdb_tests.xml';
#$suite = 'testcases/basic_tests.xml'; # todo: are these loading ok?
#$suite = 'testcases/_all.xml'; # everything we got!
$suite = 'testcases/fb_tests.xml';
$suite = 'testcases/viz.xml'; # just 1 for viz plugin, for now.

$tests = OGDataGraph::getTests($suite); 

print "<h2>Loading $suite</h2>";

print "<h3>OG Viz plugin</h3>";
print '<div id="canvas"></div><button id="redraw" onclick="redraw();">redraw</button>';


print "<p>Found ". sizeof($tests) .  " item(s).</p>";


foreach ($tests as $test) {  
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

  }

print "<h4>Graphing triples</h4>";
$rdf = $og->getTriples();

print "Count: ". sizeof($rdf) . "<br/>\n";

$viznodes = array();
$vizedges = array();
$vizlabels = array();

function shorter($p) {
  print "Shortening '$p' using ". sizeof($vizlabels);
  if ($vizlabels[$p]) { print "YUP"; return ($vizlabels[$p]); } else { print "NAH"; return $p; }
}


print "<ul>";

# loop through our RDF
foreach ($rdf as $key => $value) {
         print "<li style='font-size: small;'>" . $value['s'] . " " . $value['p'] . " " . $value['o'] . "<br/>\n";
         $predicate = $value['p'];

#        print "<li>NEW: ".  $value['s'] . " " . $value['p'] . " " . $value['o'] . "</li>";




	 # this loop thru predicates belongs elsewhere! todo: push into class methods

         foreach (OGDataGraph::$nslist as $prefix => $uri) {
#           print "DOES $predicate CONTAIN $uri ? <br/>";
           if(strstr($predicate , $uri ) ) { 
		# print '!!';
		# php.net/manual/en/function.str-replace.php 
		# mixed str_replace ( mixed $search , mixed $replace , mixed $subject [, int &$count ] )
             $short = str_replace( $uri, $prefix . ':', $predicate ); # abbreviate
		print "Replacing $uri with $prefix in $predicate : result is $short<br/>";
             $vizlabels[$predicate] = $short;
	     print "labels: ".sizeof($vizlabels) ."\n"; 
             $pred_label = '';
             if ($vizlabels[$p]) { 
               $pred_label = $vizlabels[$p];
             } else { 
               $pred_label = $p;
             }
             print "TADA: $pred_label</br>";
          }
         } # end loop thru namespaces; todo: cache elsewhere!





        print "<li>shorter: ".  $value['s'] . " " . shorter( $predicate) . " " . $value['o'] . "</li>";


 	print "</li>\n";
}
print "</ul>";
?>

<script type="text/javascript">
<!--
var redraw;
var height = 300;
var width = 600;
window.onload = function() {
    var g = new Graph();
    st1 = {directed:true, label : "seeAlso"};
    st2 = {directed:true, label : "location"};
    g.addEdge("cinema", "cineplex1",st2);
    g.addEdge("kiwi", "34",st1);
    g.addEdge("34", "cherry", { directed : true, label: "fruity" } );    /* a directed connection, using an arrow */
    //    g.addEdge("id35", "apple", { stroke : "#bfa" , fill : "#56f", label : "Meat-to-Apple" });
    var layouter = new Graph.Layout.Spring(g);     /* layout the graph using the Spring layout implementation */
    layouter.layout();
    var renderer = new Graph.Renderer.Raphael('canvas', g, width, height);    /* draw the graph using the RaphaelJS draw implementation */
    renderer.draw();
    redraw = function() {
        layouter.layout();
        renderer.draw();
    };
};
-->
</script>




<?php

  # meta: { "url": "http://developers.facebook.com/tools/lint/examples/bad_type", "testid": "bad_type", "testgroup": "fb/examples", "cache_date": "Sun 14 Nov 2010 13:46:47 CET", "valid_html": false, "uses_rdfa": true, "uses_og": true, "extended": true, "triple_count": 0, "warning": "Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+", "home_site": "http://www.facebook.com/" } 
  $meta = $og->getmeta();

  # print "<div>Metadata: ". $meta['url']  . "</div>\n"; #todo: add check/exception when no item loaded
  $status = $meta['status'];
  print "<h4>Actual Status (from testcase repository metadata)</h4>\n";
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
