<html>
<head><title>Pogo Open Graph Protocol checker: demo</title></head>
<style type="text/css">
body { margin: 9em; }
h2 { background: #ffb;  }
h3 { background: #ffd; border-style: solid; border-width: thin;  }
h4 { background: #ffe; border-style: solid; border-width: thin; }
</style>

<body>


<h1>OpenGraph checker</h1>

<p>See also Facebook's <a href="http://developers.facebook.com/tools/lint/?url=http://developers.facebook.com/tools/lint/examples/good">linter</a>.</p>


<!-- 
messy output ...
meta: { "url": "http://developers.facebook.com/tools/lint/examples/bad_type", "testid": "bad_type", "testgroup": "fb/examples", "cache_date": "Sun 14 Nov 2010 13:46:47 CET", "valid_html": false, "uses_rdfa": true, "uses_og": true, "extended": true, "triple_count": 0, "warning": "Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+", "home_site": "http://www.facebook.com/" }
 Expected triples: 0 Actual triples: TODO
-->
<?php 

require_once 'OGDataItem.php'; 
$suite = 'testcases/fb_tests.xml';
$tests = OGDataItem::getTests($suite); 

print "<h2>Loading $suite</h2>";

print "<p>Found ". sizeof($tests) .  " item(s).</p> <hr />";

# todo: what's the url?

foreach ($tests as $test) {  
  print "<h3>Test: $test</h3></h3>";
  $og = new OGDataItem();
  $og->readTest($test); # load JSON description of this test case

  print "<h4>rapperCheck</h4>". $og->rapperCheck();
  print "<h4>arcParse</h4>". $og->arcParse();
  print "<h4>rdf2info</h4>". $og->rdf2info(); # html table, fixed list of attribs

}  # loop thru testcases

/*
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/type movie 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/url http://www.imdb.com/title/tt0117500/ 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/image http://ia.media-imdb.com/images/rock.jpg 
*/
?>


</body>
</html>
