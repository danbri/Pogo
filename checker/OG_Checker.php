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

    array_push ($notices, Checker::check_og_namespace_undeclared($og));
    
    # Certain checks only make sense once we've got some data.


    # robustification needed. these were shortcuts to speed things, but maybe 
    # worth running some other checks too, to catch reasons for failure? if so
    # move them up prior to this code block for now. But really these are just 
    # more checks and should be named and structured the same. todo.
 
    if (is_null($og->triples)) { #verbose("Missing graph.");
      $report = array();
      $report['MISSING_REQUIRED_PROPERTY'] = "No data found; missing graph.";
      $report['FAILED_READ_URL'] = "No page content, perhaps a problem reading URL.";
      $report['NO_PAGE_CONTENT'] = "No page content.";
      array_push($notices,$report);
      return Checker::reportSummary($notices);
#      return $notices;
    }
    if (sizeof($og->triples) == 0) { #verbose("Empty graph.");
      $report = array();
      $report['MISSING_REQUIRED_PROPERTY'] = "No data found; empty graph.";
      $report['FAILED_READ_URL'] = "No page content, perhaps a problem reading URL.";
      $report['NO_PAGE_CONTENT'] = "No page content.";
      array_push($notices,$report);
      return Checker::reportSummary($notices);
#      return $notices;
    }


    # The following checks apply to the loaded data and its actual content:
    #

    # to rename
    array_push ($notices, Checker::check_bad_type_chars_fail($og) ); # cf. testcases/fb/examples/bad_type.meta
    array_push ($notices, Checker::check_failed_fbadmins_regex($og));
    array_push ($notices, Checker::check_nonnumeric_page_id($og));
    array_push ($notices, Checker::check_failed_big_number_in_admins($og));
    array_push ($notices, Checker::check_nondigit_appid_chars_fail($og) ); # cf. testcases/fb/examples/api_key.meta
    array_push ($notices, Checker::check_missing_required_property($og));

    $summary = Checker::reportSummary($notices);

    # Compute dependencies: e.g. OG_NAMESPACE_UNDECLARED implies MISSING_REQUIRED_PROPERTY for now.
    if ($summary['OG_NAMESPACE_UNDECLARED']) {
     @$summary['MISSING_REQUIRED_PROPERTY'] = "Technically we are missing all properties, since xmlns:og is undeclared."; 
    }
    return $summary;

  }
   
    public static function reportSummary($report) {
      $summary=array();
      if (sizeof($report) == 0) { return ''; }  
      foreach ($report as $ticket) {
        if (sizeof($ticket)==0) { continue; }
        while (list($code, $info) = each($ticket)) {
           $summary[$code]=$info; # flattening, on assumption different checks find different bugs
        }
      }
      return $summary;
    }



  #########################################################################################
  #
  # Checks
  #
  public function check_failed_fbadmins_regex($og) {
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

  #was checkNumericPageID
  public function check_nonnumeric_page_id($og) {
    foreach ($og->triples as $key => $value) {
      $report = array();
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlpage_id') { 
        if ( preg_match( '/[^0-9]+/', $value['o']) )  { 
          $report['NONNUMERIC_PAGE_ID'] = $value['o'];
        }
      }
    }
    return $report;
  }

  public function check_failed_big_number_in_admins($og) {
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



  # todo: migrate to real parser. but will catch most cases.
  public function check_og_namespace_undeclared($og) {
    $report = array();	
    # print "GOT content: {{".$og->content . "}}";		  
    if (!preg_match('/xmlns:og/',$og->content  ) ) {
        $report['OG_NAMESPACE_UNDECLARED'] = "Couldn't find xmlns:og in the document.";
    } 
    return $report;
  }

#    todo: only run this check if fb: prefix is used.
#  # todo: migrate to real parser. but will catch most cases.
#  public function check_fb_namespace_undeclared($og) {
#    $report = array();			  
#    if (!preg_match('/xmlns:fb/',$og->content , $matches ) ) {
#        $report['FB_NAMESPACE_UNDECLARED'] = 'Couldn't find xmlns:fb in the document.';
#    } 
#    return $report;
#  }


  public function check_bad_type_chars_fail($og) {
    $report = array();
    # verbose("CHECKING FOR BAD TYPE: ". $og->og_type);
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
    # print "TESTING MISSING_REQUIRED_PROPERTY NOW. title is ".$og->og_title;
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
