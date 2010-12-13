<?php

# 'And thirdly, the code is more what you'd call "guidelines" than actual rules.'
# -- http://www.imdb.com/title/tt0325980/quotes


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

/*    $this->checkTypeLabel(); # cf. testcases/fb/examples/bad_type.meta
    $this->checkAppIDSyntax(); # cf. testcases/fb/examples/api_key.meta
    $this->checkMetaName();
    $this->checkNotCSV();
    $this->checkNumericPageID();
    $this->checkAdminsNotBigNumber();
*/
  

  public function checkNotCSV($og) {
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') {
        if (!preg_match( '/^\s*[0-9]+(\s*,\s*[0-9]+)*\s*$/', $value['o']) )  { throw new Exception('FAILED_FBADMINS_REGEX'); }
      }
    }
  }

  public function checkNumericPageID($og) {
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlpage_id') { 
        if ( preg_match( '/[^0-9]+/', $value['o']) )  { throw new Exception('FAILED_PAGEID_NUMBERSONLY_REGEX'); }
      }
    }
  }


  public function checkAdminsNotBigNumber($og) {
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') { 
        if ( preg_match( '/[0-9]{10}/', $value['o']) )  { throw new Exception('FAILED_BIG_NUMBER_IN_ADMINS'); } # todo: clarify rule!
      }
    }
  }


  ###############################################################################################
  # Checks that operate over the simple OG property representation (fields not triples)

  public function paranoidMarkupCheck($og) {
    foreach ($og->fields as $key => $value) {
      if ( preg_match( '/</', $value) )  { throw new Exception('UNESCAPED_LESSTHAN_IN_CONTENT_VALUE'); } 
    }
  }



  public function checkMetaName($og) {
    #    print "TODO: check syntax of meta name. Requires raw parser API not triples.";
    return; # todo: requires markup access, not ARC triples. use built-in simple parser.
  }

  public function checkTypeLabel($og) {
    foreach ($og->triples as $key => $value) {
      if ($value['p'] == 'http://opengraphprotocol.org/schema/type') { 
        if (preg_match( '/[^a-z_:]/', $value['o']) )  { throw new Exception('BAD_TYPE_CHARS_FAIL'); }
      }
    }
  print "<br/>"; # tmp
  }
  #  Warning: Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+



  public function checkAppIDSyntax($og) {

    foreach ($og->triples as $key => $value) {
      # print "[S]: " . $value['s'] . "<br/>\n";      print "[P]: " . $value['p'] . "<br/>\n";     print "[O]: " . $value['o'] . "<br/>\n";
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlapp_id') { 
        # print "Checking app_id is purely numeric.";
        if (preg_match( '/[^0-9]+/', $value['o']) )  { throw new Exception('NONDIGIT_APPID_CHARS_FAIL'); } # todo: get tighter regex w/ no false positives from FB.
      }
    }
  print "<br/>"; # tmp
    
  }


  function shortify($u) {
    foreach (OGDataGraph::$nslist as $prefix => $uri) {   # print "DOES $u CONTAIN $uri ? <br/>";
      if(strstr($u , $uri ) ) {
        $short = str_replace( $uri, $prefix . ':', $u );  # print "Replacing $uri with $prefix in $u : result is $short<br/>";
        return($short);
      }
    } # end loop thru namespaces; todo: cache
    return ($u);
  }   

}

?>
