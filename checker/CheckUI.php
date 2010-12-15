<?php
require_once 'OGDataGraph.php'; 
require_once 'OG_Checklist.php';
#require_once 'cfg.php';
$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;

/* 
Documentation

This code generates HTML used by the front-end pages, so they don't do too
much and so HTMLism doesn't leak into the core. In fact the core has a few utilities
for printing tables, but nothing too fancy. Could move those here too. In progress now.

e.g. $og->xmlnsTable(); # crude table of namespace declarations 
     $og->simpleTable();     # the main 4 properties.

     tableFromReport($report);

*/

class CheckUI {

 # $tc is a testcase file ID
 public static function checkFromTest($tc) {
  $og = new OGDataGraph();	  # set up a graph for this test
  print "<h4>Test: $tc</h4>";
  $og->readTest($tc);
  CheckUI::checkFromFreshDataGraph($og, $tc);
 }



  public function tableFromReport($report) {
    if (sizeof($report) == 0) { return ''; }
    $t = "<table border='1' class='trouble'>";
    foreach ($report as $ticket) {
      if (sizeof($ticket)==0) { continue; }
      while (list($code, $info) = each($ticket)) {
        $t .= "<tr><td>$code</td><td>".$msg[$code]."</td><td>$info</td></tr>\n";
      }
    }
      $t .= "</table>";
    return $t;
  }




 # OK, parts within should (eventually) be usable even without a testcase
 # Which bits of the testcase-supplied $og->meta are critical?
 # We need the 'url', 'warning', 'warn', ...
 #
 # TODO: strip out the bits that aren't testcase-powered for re-use in main user-facing checker 
 #
 public static function checkFromFreshDataGraph($og, $tc) {

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
       print CheckUI::tableFromReport($report);
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

 } # function
} # class

?>
