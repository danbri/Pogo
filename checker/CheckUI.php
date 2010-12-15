<?php

require_once 'OGDataGraph.php'; 
require_once 'OG_Checklist.php';
require_once 'cfg.php';

class CheckUI {

/*  This code generates HTML used by the front-end pages, so they don't do too
much and so HTMLism doesn't leak into the core. In fact the core has a few utilities
for printing tables, but nothing too fancy. Could move those here too. In progress now.
This class is just a group of functions, not an instantiable object.

e.g. $og->xmlnsTable(); # crude table of namespace declarations 
     $og->simpleTable();     # the main 4 properties.

     tableFromReport($report);				*/


  public static function checkFromTest($tc) {
    $og = new OGDataGraph();	  # set up a graph for this test
    print "<h4>Test: $tc</h4>";
    $og->readTest($tc);
    CheckUI::checkFromFreshDataGraph($og, $tc);
  }



  public static function tableFromReport($report) {
    if (sizeof($report) == 0) { return ''; }
    $t = "<table border='1' class='trouble'>";
    foreach ($report as $ticket) {
      if (sizeof($ticket)==0) { continue; }
      while (list($code, $info) = each($ticket)) {
        $t .= "<tr><td>$code</td><td>".Label::$messages[$code]."</td><td>$info</td></tr>\n";
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
         print CheckUI::simpleTable($og);
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
           print "<tr><td>$expect</td><td>". Label::$messages[$expect] . "</td></tr>\n";
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
        print $e->getMessage() .": " . Label::$messages[$e->getMessage()] ;
     }

     print "<p><br/> </p>";
  } catch(Exception $e) 
  {
    $this->fail(true, "failed loading test $tc, exception:".$e);
    break;
  }

 } # function

 #### HTML snippets

  public static function simpleTable($og) {
    $t = "<table border='1' style='background: #eeeeee;'>\n";
    $t .= "<tr><td class=\"ogfield\">Type</td><td>". $og->og_type ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">Image</td><td><a href='".$og->og_image."'><small><img src='". $og->og_image ."' alt='image shown inline'><br/>". $og->og_image ."</small></td></tr>";
    $t .= "<tr><td class=\"ogfield\">Title</td><td>".  $og->og_title ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">URL</td><td>". $og->og_url ."</td></tr>";
    # $t .=  "<tr><td class=\"ogfield\">Site URL (<em>as supplied</em>)</td><td>".  $og->meta['url'] ."</td></tr>";
    $t .= "</table>\n";
    return $t;
  }


  public function xmlnsTable($og) {
    $xmlns = $og->namespaces();
    $t = "<table border='1' style='background: #eeeeee;'>\n";
    while (list($prefix, $ns) = each($xmlns)) {
         $ok = OGDataGraph::isValidURL($ns);
         if ($ok) { $m = " (URI seems ok.)"; }
         $t .= "<tr><td class='prefix'>$prefix</td><td>".htmlentities($ns)."</td><td>".$m."</td></tr>";
    }
    $t .= "</table>\n";
    return $t;
  }


#    todo: fragment for url generation (use or delete)
#    $url_parts = parse_url( $props["http://opengraphprotocol.org/schema/url"] );
#    if ($url_parts['host'] && $url_parts['port']) {
#      $site_url = $url_parts['scheme'] ."://". $url_parts['host'] . $url_parts['port'] . "/" ; # TODO: must we guess this?
#    }

###############

  public static function simpleForm($url='') {
    $me = basename($_SERVER['SCRIPT_FILENAME']);
    $base = OGDataGraph::$my_base_uri;
    $f =  "<form action=\"$me\" method=\"get\" name=\"checker\">\n";
    $f .= "Input URL:<input type=\"text\" size=\"70\" name=\"url\" value=\"$url\"/><input type=\"submit\" value=\"go\"/>";
    #print '<div style="float: right"><input type="radio" name="mode" value="auto" checked="true" /> auto ';
    #print '<input type="radio" name="mode" value="lite" /> lite';
    #print '<input type="radio" name="mode" value="full" /> full</div>';
    $f .= '</form>';
    $f .= "<small>cached examples: <a href=\"?url=$base/testcases/imdb/legend_guardians.cache&mode=auto\">legend_guardians</a> <br/>";
    $f .= "live examples: <a href=\"?url=http://www.imdb.com/title/tt0083658/&mode=auto\">bladerunner</a> | ";
    $f .= " <a href='?url=http://developers.facebook.com/tools/lint/&mode=auto'>developers.facebook.com</a><br/>";
    $f .= "bad examples: <a href='?url=http://developers.facebook.com/tools/lint/examples/bad_app_id'>bad_app_id</a><br/>";
    $f .= 'geo: <a href="?url=http://localhost/pogo/Pogo/checker/testcases/ogp/geo1.cache#">california</a>';
    $f .= "</small>";
    return $f;
  }

} # class

?>
