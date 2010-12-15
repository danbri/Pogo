<?php
header("HTTP/1.1 200 OK");
header("Content-type: text/html"); # half-hearted attempt to do NPH
?><html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />
<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
require_once 'OG_Checklist.php';
require_once 'cfg.php';
$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;
print '<body>';
#$map = 'testcases/approved.xml';
$map = 'testcases/fb_tests.xml';
#$map = 'testcases/_all.xml';
#$map = 'testcases/_todo.xml';

try { 
$tests = OGDataGraph::getTests($map);
} catch (Exception $e) { print "Failed to load configured testcases sitemap file."; }

foreach ($tests as $tc) {
  $og = new OGDataGraph();	  # set up a graph for this test
  print "<h4>Test: $tc</h4>";
  $og->readTest($tc);
  $url = $og->meta['url'];

  # Each testcase can have a textual summary of problems in the 'warning' field:
  if ($og->meta['warning']) {    print "<em class='warning'>".$og->meta['warning']."</em><br/>\n";    } 

  # Each testcase defines its main URL (relative in case of repo-only files, others are cached from public links)
  #
  print "Input URL: $url";

  try {
     $og->readTest($tc);
     try {
       $og->readFromURL('full', $url); 
       $xmlns = $og->namespaces(); 
       # print $og->xmlnsTable();

       # If we got some data back, display a summary table
       if (sizeof($og->triples ) > 0) { 
         print $og->simpleTable();
       } else {
         print "<p>Results: no OpenGraph data found.</p>";
       }
       print "<p>Expected data items: ".$og->meta['triple_count']." actual: ". sizeof($og->triples)."</p>\n";

      # Each testcase can have an array of warning codes from our OG_Checklist
      if ( sizeof($og->meta['warn']) > 0 ) { 
         print "<h5>Expected Issues</h5>";
         # print var_dump($og->meta['warn'] );
         print "<table class='expected'>";
         foreach ($og->meta['warn'] as $expect) {
           print "<tr><td>$expect</td><td>". $msg[$expect] . "</td></tr>\n";
         }
         print '</table>';
       }


       # Actually run some checks
       try { 
       $report = Checker::checkall($og); 

       } catch (Exception $e) {
         print "Unexpected Exception during checking: ".$e->getMessage() ."\n</br>";
       }

       print "\n<h5>Detected Issues</h5>\n\n";
       # print "Raw report: ".var_dump($report);
       print Checker::tableFromReport($report);
       # should be conditional on error status here


     } catch (Exception $e) {
        print "Parsing failed... <br/>";
        print $e->getMessage() .": " . $msg[$e->getMessage()] ;
     }

     print "<p><br/> </p>";
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
