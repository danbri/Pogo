<?php
require_once 'OGDataItem.php'; 

$tests = OGDataItem::getTests('testcases/fb_tests.xml'); 

foreach ($tests as $test) {  
  $og = new OGDataItem();
  $og->readTest($test); # load JSON description of this test case
  print $og->rapperCheck();
  print $og->arcParse();
  print $og->rdf2info(); # html table, fixed list of attribs

}  # loop thru testcases

/*
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/type movie 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/url http://www.imdb.com/title/tt0117500/ 
  Factoid: http://developers.facebook.com/tools/lint/examples/good http://opengraphprotocol.org/schema/image http://ia.media-imdb.com/images/rock.jpg 
*/
?>
