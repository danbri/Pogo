<?php
require_once 'OGDataItem.php'; 

$tests = OGDataItem::getTests('testcases/fb_tests.xml'); 

foreach ($tests as $test) {  
  $og = new OGDataItem();
  $og->readTest($test); # load JSON description of this test case
  print $og->rapperCheck();
  print $og->arcParse();
}  # loop thru testcases

?>
