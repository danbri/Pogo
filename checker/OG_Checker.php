<?php



require_once 'OG_Checklist.php';
require_once 'cfg.php';
       
# 'And thirdly, the code is more what you'd call "guidelines" than actual rules.'
# -- http://www.imdb.com/title/tt0325980/quotes


  # utility that depends on the json list of common namespace prefixes from prefix.cc
  function shortify($u) {
    foreach (OGDataGraph::$nslist as $prefix => $uri) {   # print "DOES $u CONTAIN $uri ? <br/>";
      if(strstr($u , $uri ) ) {
        $short = str_replace( $uri, $prefix . ':', $u );  # print "Replacing $uri with $prefix in $u : result is $short<br/>";
        return($short);
      }
    } # end loop thru namespaces; todo: cache
    return ($u);
  }   


# This class encapsulates all our checks against the form and content of OG markup
#
# These should include:
#
#  - core syntax checks (elements and attributes, namespace declarations)
#  - checks on suspicious or obviously wrong values
#  - checks on Facebook-specific fields
#  - checks on 'extension' fields and non-OG RDFa data (eg. different 'title's)
#
# Any code embodying knowledge of good/bad/dodgy OG markup ought to live here.
# The integrity of the surrounding software has its own unit tests. Not clear 
# How these should best be integrated.


#####################################################################################
# CHECKS


# TODO: each check and/or failing needs a symbolic name, for association with testcases


class Checker {

  public function checkall($og) {

    # verbose("Running all field value checks.");
    $notices = array();

    # Raw syntax checks
    # Initially conducted with regex, should push down to DOM code.
    #
    array_push ($notices, Checker::check_metaname_attribute_not_property($og));
    array_push ($notices, Checker::check_no_page_content($og));

    # Certain checks only make sense once we've got some data.


    # robustification needed. these were shortcuts to speed things, but maybe 
    # worth running some other checks too, to catch reasons for failure? if so
    # move them up prior to this code block for now. But really these are just 
    # more checks and should be named and structured the same. todo.
 
    if (is_null($og->triples)) { #verbose("Missing graph.");
      $report = array();
      $report['MISSING_REQUIRED_PROPERTY'] = "No data found; missing graph.";

      array_push($notices,$report);
      return $notices;
    }
    if (sizeof($og->triples) == 0) { #verbose("Empty graph.");
      $report = array();
      $report['MISSING_REQUIRED_PROPERTY'] = "No data found; empty graph.";
      array_push($notices,$report);
      return $notices;
    }


    # The following checks apply to the loaded data and its actual content:
    #

    # to rename
    array_push ($notices, Checker::checkTypeLabel($og) ); # cf. testcases/fb/examples/bad_type.meta
    array_push ($notices, Checker::checkNotCSV($og));
    array_push ($notices, Checker::checkNumericPageID($og));
    array_push ($notices, Checker::checkAdminsNotBigNumber($og));
      
    array_push ($notices, Checker::check_nondigit_appid_chars_fail($og) ); # cf. testcases/fb/examples/api_key.meta
    array_push ($notices, Checker::check_missing_required_property($og));
    return $notices;
  }
   


  ### UTILITIES

  public function tableFromReport($report) {
    if (sizeof($report) == 0) { return ''; }
    $msg = Label::$messages;
    $t = "<table border='1' class='trouble'>";
    foreach ($report as $ticket) {
      if (sizeof($ticket)==0) { continue; }
      # print "<pre>".var_dump($ticket)."</pre>";
      while (list($code, $info) = each($ticket)) {
        $t .= "<tr><td>$code</td><td>".$msg[$code]."</td><td>$info</td></tr>\n";
      }
    }
      $t .= "</table>";
    return $t;
  }



  #########################################################################################
  #
  # Checks
  #
  public function checkNotCSV($og) {
    $report = array();
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') {
        if (!preg_match( '/^\s*[0-9]+(\s*,\s*[0-9]+)*\s*$/', $value['o']) )  { 
          $report['FAILED_FBADMINS_REGEX'] = $value['o'];
        }
      }
    }
    return $report;
  }

  public function checkNumericPageID($og) {
    foreach ($og->triples as $key => $value) {
      $report = array();
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlpage_id') { 
        if ( preg_match( '/[^0-9]+/', $value['o']) )  { 
          $report['FAILED_FBADMINS_REGEX'] = $value['o'];
        }
      }
    }
    return $report;
  }


  public function checkAdminsNotBigNumber($og) {
    $report = array();
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') { 
        if ( preg_match( '/[0-9]{10}/', $value['o']) )  { 
          $report['FAILED_BIG_NUMBER_IN_ADMINS'] = $value['o'];
        } # todo: clarify rule!
      }
    }
    return $report;
  }


  ###############################################################################################
  # Checks that operate over the simple OG property representation (fields not triples)

  public function paranoidMarkupCheck($og) {
    $report = array();
    foreach ($og->fields as $key => $value) {
      if ( preg_match( '/</', $value) )  { 
        $report['UNESCAPED_LESSTHAN_IN_CONTENT_VALUE'] = 'less-than symbol.';
      } 
    }
    return $report;
  }



  public function check_no_page_content ($og) {
    $report = array();
    if ( strlen($og->content) < 1 ) { $report['NO_PAGE_CONTENT'] = 'No page content found.'; }
  }

  public function check_metaname_attribute_not_property($og) {
    $report = array();			  
    if (preg_match('/(\s+name\s*=\s*")(og|fb)?:/',$og->content , $matches ) ) {

      # sometimes sites use name= alongside property=, which is fine
      if (!preg_match('/(\s+property\s*=\s*")(og|fb)?:/',$og->content ) ) {
        $report['METANAME_ATTRIBUTE_NOT_PROPERTY'] = 'Use "property" not "name" meta attribute here: '.$matches[1].$matches[2]."...";
      }
    } 
    return $report;
  }

  public function checkTypeLabel($og) {
    $report = array();
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://opengraphprotocol.org/schema/type') { 
        if (preg_match( '/[^a-z_:]/', $value['o'] ) )  { 
          $report['BAD_TYPE_CHARS_FAIL'] = $value['o'];
        }
      }
    }
    return $report;
  }
  #  Warning: Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+


  # http://ogp.me/ "The four required properties for every page are:"
  #
  # og:title - The title of your object as it should appear within the graph, e.g., "The Rock".
  # og:type - The type of your object, e.g., "movie". Depending on the type you specify, other properties may also be required.
  # og:image - An image URL which should represent your object within the graph.
  # og:url - The canonical URL of your object that will be used as its permanent ID in the graph, e.g., "http://www.imdb.com/title/tt0117500/".
  # 
  public function check_missing_required_property($og) {
    $report = array();
    $oops = '';
    if (is_null($og->og_title)) { $oops .= "og:title is missing. "; }
    if (is_null($og->og_type)) { $oops .= "og:type is missing. "; }
    if (is_null($og->og_image)) { $oops .= "og:image is missing. "; }
    if (is_null($og->og_url)) { $oops .= "og:url is missing. "; }
    if ($oops != '') { $report['MISSING_REQUIRED_PROPERTY'] = $oops; }
    return $report;
    # aside: note that this happens to use OG API not raw triples
  }


  public function check_nondigit_appid_chars_fail($og) {

    $report = array();
    foreach ($og->triples as $key => $value) {
      # print "[S]: " . $value['s'] . "<br/>\n";      print "[P]: " . $value['p'] . "<br/>\n";     print "[O]: " . $value['o'] . "<br/>\n";
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlapp_id') { 
        # print "Checking app_id is purely numeric.";
        if (preg_match( '/[^0-9]+/', $value['o']) )  { 
          $report['NONDIGIT_APPID_CHARS_FAIL'] = $value['o'];
        } # todo: get tighter regex w/ no false positives from FB.
      }
    }
    return $report;
  }



}

?>
