<?php
header("HTTP/1.1 200 OK");
header("Content-type: text/html"); # half-hearted attempt to do NPH
?><html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />

<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
require_once 'OG_L18N.php';
require_once 'cfg.php';

$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;
$me = 'index.php';

#$mode = $_GET['mode'];
#if (is_null($mode)) {$mode='auto';}
#if ($mode &&  !preg_match( '/(^full$|^lite$|^auto$|^viz$|^testcase$)/', $mode )  ) { exit("Unknown mode '$mode' requested."); } 
#$url = $_GET['url'];

#print "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
#print "Input URL:<input type=\"text\" size=\"70\" name=\"url\" value=\"$url\"/><input type=\"submit\" value=\"go\"/>";
#print '<div style="float: right"><input type="radio" name="mode" value="auto" checked="true" /> auto ';
#print '<input type="radio" name="mode" value="lite" /> lite';
#print '<input type="radio" name="mode" value="full" /> full</div>';
#print '</form>';

#if (!$url) {  exit(1); }
#if (!isValidURL($url)){ exit("Unsupported URL syntax."); }
?>
<body>
<?php 

print "Fetching sitemap XML.<br/>";

$map = 'testcases/approved.xml';
$map = 'testcases/fb_tests.xml';

require_once 'OGDataGraph.php';
print "<hr/>";
try { 
$tests = OGDataGraph::getTests($map);
} catch (Exception $e) { print "oops"; print $e; }
print $tests;

foreach ($tests as $tc) {
  # set up a graph for this test
  $og = new OGDataGraph();
  print "<h4>Test: $tc</h4>";
  print "<p>Loading test.</p>";
  $og->readTest($tc);
  $url = $og->meta['url'];

  print "URL now: $url";
  print "<p>Expected triples: ". $og->meta['triple_count']."</p>";
  try {
     $og->readTest($tc);
     try {
       $og->readFromURL('full', $url); 

       $xmlns = $og->namespaces(); 
       print $og->xmlnsTable();
       
       print "<h4>Checks</h4>"; 
       $report = $og->checkfields(); 
       print Checker::tableFromReport($report);

     } catch (Exception $e) {
        print "Parsing failed... <br/>";
        print $e->getMessage() .": " . $msg[$e->getMessage()] ;
     }
     # add checks for: No og: namespace declaration. Unknown og: namespace declaration.

  } catch(Exception $e) 
  {
    $this->fail(true, "failed loading test $tc, exception:".$e);
    break;
  }
}

?>

<hr />
[pogo checker] status: v1.0, <em>experimental release.</em>
</body>
</html>
