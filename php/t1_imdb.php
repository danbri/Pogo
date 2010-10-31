<?php

require_once 'OGDataItem.php'; 

#$tests = OGDataItem::getTests('testcases/basic_tests.xml'); 

$tests = OGDataItem::getTests('testcases/imdb_tests.xml'); 

	# sitemap pointing to *.cache HTML files + *.meta JSON associative arrays

foreach ($tests as $test) {  
  $og = new OGDataItem();
  $og->readTest($test); # load JSON description of this test case

  /*
  $og->htmlW3CCheck();  # see what W3C thinks of it --- not for private data
  if ($og->htmlok->isValid()) {
    echo 'HTML is valid!';
  } else {
    echo 'HTML is NOT valid!';
    foreach ($og->htmlok->errors as $error) {
      # print "bad HTML line".$meta[testid].": [".$error->line. "] ". $error->message. "\n";         	 # http://pear.php.net/package/Services_W3C_HTMLValidator/docs/latest/Services_W3C_HTMLValidator/Se$
    }
  }
  */

  print $og->rapperCheck();

  print $og->arcParse();

}  # loop thru testcases

?>
