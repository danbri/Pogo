<html>
<head><title>Pogo Open Graph Protocol checker: demo</title></head>
<style type="text/css">
body { margin: 9em; }
h2 { background: #ffb;  }
h3 { background: #ffd; border-style: solid; border-width: normal; padding: 3px; }
h4 { background: #ffe; border-style: solid; border-width: thin; }
.ogfield { font-weight: bold; background: #CCF;}
</style>

<body>


<h1>OpenGraph checker</h1>

<p>See also Facebook's <a href="http://developers.facebook.com/tools/lint/?url=http://developers.facebook.com/tools/lint/examples/good">linter</a>.</p>


<!-- 
messy output ...
meta: { "url": "http://developers.facebook.com/tools/lint/examples/bad_type", "testid": "bad_type", "testgroup": "fb/examples", "cache_date": "Sun 14 Nov 2010 13:46:47 CET", 
# "valid_html": false, "uses_rdfa": true, "uses_og": true, "extended": true, "triple_count": 0, "warning": "Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+", "home_site": "http://www.facebook.com/" }
 Expected triples: 0 Actual triples: TODO
-->
<?php 

require_once 'OGDataItem.php'; 
$suite = 'testcases/fb_tests.xml';
#$suite = 'testcases/imdb_tests.xml';
#$suite = 'testcases/basic_tests.xml'; # todo: are these loading ok?
#$suite = 'testcases/_all.xml'; # everything we got!

$tests = OGDataItem::getTests($suite); 

print "<h2>Loading $suite</h2>";

print "<p>Found ". sizeof($tests) .  " item(s).</p>";

# todo: what's the url?

foreach ($tests as $test) {  
  print "<h3>Test: $test</h3></h3>";
  $og = new OGDataItem();
  $og->readTest($test); # load JSON description of this test case
  #print "<h4>rapperCheck</h4>";
  # print $og->rapperCheck();
  $myrdf =  $og->arcParse();
  #print "<h4>arcParse</h4>";
  $table = $og->rdf2info(); # html table, fixed list of attribs

  try { 
  $og->checkfields();
  } catch (Exception $e) {
    if ($e->getMessage() == "BAD_TYPE_CHARS_FAIL") { print "Poor type name found. Please avoid capitals and punctuation except ':' and '_'.<br/>"; }
  }

  # meta: { "url": "http://developers.facebook.com/tools/lint/examples/bad_type", "testid": "bad_type", "testgroup": "fb/examples", "cache_date": "Sun 14 Nov 2010 13:46:47 CET", "valid_html": false, "uses_rdfa": true, "uses_og": true, "extended": true, "triple_count": 0, "warning": "Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+", "home_site": "http://www.facebook.com/" } 
  $meta = $og->getmeta();
  # print "<div>Metadata: ". $meta['url']  . "</div>\n"; #todo: add check/exception when no item loaded
  $status = $meta['status'];
  if ($status == "valid") { 
    print "VALID! &#x2714;";
  } else {
    print "INVALID! &#x2717; <br/>";
    print "Target Warning: " . $meta['warning'];
  }  
  print $table;



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
