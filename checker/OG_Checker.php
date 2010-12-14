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



  public function checkMetaName($og) {
    $report = array();			   #    print "TODO: check syntax of meta name. Requires raw parser API not triples.";
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


  public function checkAppIDSyntax($og) {

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
