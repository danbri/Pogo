<?php

# PHP Utility for checking Open Graph Protocol markup.
#
# 'And thirdly, the code is more what you'd call "guidelines" than actual rules.'
# -- http://www.imdb.com/title/tt0325980/quotes
#
# Nearby in the Web: http://webr3.org/apps/play/api/lib a js rdfa API 


# This code should at least find the same issues flagged by the FB linter:
# 
# http://developers.facebook.com/blog/post/390
# http://developers.facebook.com/tools/lint/examples/
# http://developers.facebook.com/tools/lint/?url=opengraphprotocol.org
# http://developers.facebook.com/tools/lint/?url=http://www.imdb.com/title/tt0117500/
# http://developers.facebook.com/tools/lint/?url=developers.facebook.com
# http://developers.facebook.com/tools/lint/?url=http://www.rottentomatoes.com/m/matrix/
# http://developers.facebook.com/tools/lint/?url=blog.paulisageek.com


require_once 'OG_L18N.php'; # natural-lang text strings belong here
require_once 'OG_Checker.php'; 

error_reporting (E_ALL ^ E_NOTICE); # looking in a hash for missing info - not a crime
# error_reporting(E_ALL|E_STRICT); # dev't

# stopgap verbosity 
function verbose($s) { 
  #print "<strong>debug</strong>: $s<br/>"; 
} 

class OGDataGraph {

  public $meta = array();
  public $testdir = "./testcases/";
  public $htmlok;
  public $triples;
  public $url;

  public static $nslist; # namespace prefixes
  


  function __toString() {
    return "[OGDataGraph status: triples=$triples url=$url htmlok=$htmlok meta=$meta ]";
  }


  # default to lite, so as not to depend on RDFa parser plugin(s)
  #
  function readFromURL($u, $mode='lite') {
    verbose("reading from url $u with mode $mode."); 
    if ($mode == 'lite') {
      return $this->liteParse($u);
    } else {
      return $this->arcParse($u);
    }
  }

  function liteParse($u) {
    return 'lite: todo';
 
  }

#  function __autoload() {
#    # loadNamespaceList(); # not needed
#  } # we could store the list in php form instead of json. 



  #################################################################################
  # Testcases-related methods

  public static function getTests($source) {  

    $dom = new DomDocument();
    $dom->load($source);
    $tests = array();
    $xpath = new DomXPath($dom);
    $xpath->registerNamespace('c', 'http://www.google.com/schemas/sitemap/0.84');
    $result = $xpath->query("//c:loc/text()");
    foreach($result as $b) {
      array_push($tests, $b->data);
    }
    return $tests;
  }

  public function localFile() {
    return ( $this->testdir . $this->meta['testgroup'] . "/" . $this->meta['testid']);
  }

  public function readTest($tc) { 
    $tc = preg_replace( '/file:/','', $tc);
    $handle = fopen( $tc, "r");
    $contents = stream_get_contents($handle);
    #print "meta: ". $contents . "\n<br/>\n";
    fclose($handle);
    $meta = json_decode( $contents, true );
    $this->meta = $meta;
    #    print "Expected triples: " . $meta['triple_count'] . "\n"; 
    #    print "Actual triples: TODO\n";
    $fn = $meta['testgroup'] . "/" . $meta['testid'];
  }


  public function getmeta(){ 
    return $this->meta;
  }




  #################################################################################
  # Full RDFa parsers
  # Rapper (Redland) parser; for commandline use only currently.
  
  public function rapperCheck() {
    $meta = $this->meta;
    $fn = $this->localFile();
    # Let's compare tidy and untidy counts
    # Requires: HTML Tidy and Rapper (Redland RDF parser)
    $c1 = "rapper  --count -i rdfa " . $fn . ".cache " . $meta['url'] ;
    
    #print "Basic commandline: " . $c1 . "\n\n";

    $c2 = "tidy -f logs/_errorlog -numeric -q -asxml ".$fn . ".cache" . "  | rapper  --count -i rdfa - ".$meta['url'];
    # print "Tidied commandline: " . $c2 . "\n\n";
    
    # TODO: impl
    # http://us.php.net/manual/en/function.shell-exec.php
    # http://us.php.net/manual/en/language.operators.execution.php

    $rc1 = `$c1`;
    # print "Rapper count 1: $rc1 \n";

    $rc2 = `$c2`;
    # print "Rapper count 2: $rc2 \n";

  }


  public function getTriples() { return $this->triples; }


  #################################################################################
  # Full RDFa parsers
  # ARC RDFa parser plugin (general RDFa 1.0 parser with microformat support)
  
  public function arcParse($u) {

    require_once 'plugins/arc/ARC2.php'; # lots of PHP4-compatibility warnings when in PHP5.
    $meta = $this->meta;
    if ($u) { $url = $u; } else { $url = $meta['url']; } # eg. 'http://www.rottentomatoes.com/m/oceans_eleven/';

    verbose("ARC RDFa parser: '$u'<br/>");
    $parser = ARC2::getRDFParser();
    $parser->parse($url);
    $parser->extractRDF('rdfa');
    $triples = $parser->getTriples();
    $this->triples=$triples;
    foreach ($triples as $key => $value) {
       if (!preg_match( '/http:\/\/opengraphprotocol\.org/', $value['p'])) {
         if (preg_match( '/poshrdf/', $value['p'])) continue;
         if (preg_match( '/stylesheet/', $value['p'])) continue;
          # print "Factoid: " . $value['s'] . " " . $value['p'] . " " . $value['o'] . " \n";
       }
    }
    return($this->triples);
  }
  # representation: array with associative arrays, keys: s, p, o, s_type (uri, ...), o_type (literal, ...)





  #################################################################################
  # HTML output 

  public function rdf2info() {
    #print "Got a graph ". $g;
    #print "TODO: pull type, admins, app ID, Description, Image, title, Site URL, URL from it.";
    # for that, we need an OO repr?
    $props = array(); #todo
    foreach ($this->triples as $key => $value) {
       if (preg_match( '/http:\/\/opengraphprotocol\.org/', $value['p'])) {
          $prop =  $value['p'];
          $props[$prop] = $value['o'];
          # print $prop. " " . $value['o'] . " \n";
       }
    }
    $url_parts = parse_url( $props["http://opengraphprotocol.org/schema/url"] );

    if ($url_parts['host'] && $url_parts['port']) { 
      $site_url = $url_parts['scheme'] ."://". $url_parts['host'] . $url_parts['port'] . "/" ; # TODO: must we guess this?
    } else {
      $site_url = '';
    }
    $t = "<table border='1'>\n";
    $t .= "<tr><td class=\"ogfield\">Type</td><td>". $props["http://opengraphprotocol.org/schema/type"] ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">Image</td><td>". $props["http://opengraphprotocol.org/schema/image"] ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">Title</td><td>". $props["http://opengraphprotocol.org/schema/title"] ."</td></tr>";
    $t .=  "<tr><td class=\"ogfield\">Site URL</td><td>". $site_url ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">URL</td><td>". $props["http://opengraphprotocol.org/schema/url"] ."</td></tr>";
    $t .= "</table>\n";
    return $t;
  }

  #################################################################################
  # CHECKS
  #
  # todo: move to a separate PHP class

  public function checkfields() {
    print "Running all field value checks.<br/><br/>";
    $this->checkTypeLabel(); # cf. testcases/fb/examples/bad_type.meta
    $this->checkAppIDSyntax(); # cf. testcases/fb/examples/api_key.meta
    $this->checkMetaName();
    $this->checkNotCSV();
    $this->checkNumericPageID();
    $this->checkAdminsNotBigNumber();
  }
  

  public function checkNotCSV() {
    foreach ($this->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') {
        if (!preg_match( '/^\s*[0-9]+(\s*,\s*[0-9]+)*\s*$/', $value['o']) )  { throw new Exception('FAILED_FBADMINS_REGEX'); }
      }
    }
  }

  public function checkNumericPageID() {
    foreach ($this->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlpage_id') { 
        if ( preg_match( '/[^0-9]+/', $value['o']) )  { throw new Exception('FAILED_PAGEID_NUMBERSONLY_REGEX'); }
      }
    }
  }


  public function checkAdminsNotBigNumber() {
    foreach ($this->triples as $key => $value) {
      if ($value['p'] == 'http://www.facebook.com/2008/fbmladmins') { 
        if ( preg_match( '/[0-9]{10}/', $value['o']) )  { throw new Exception('FAILED_BIG_NUMBER_IN_ADMINS'); } # todo: clarify rule!
      }
    }
  }



  public function checkMetaName() {
#    print "TODO: check syntax of meta name. Requires raw parser API not triples.";
    return; # todo: requires markup access, not ARC triples. use built-in simple parser.
  }

  public function checkTypeLabel() {
	#    print "Checking all type field values.<br/>";
	#      print "Key: $key Value: $value <br/>\n";      print "[S]: " . $value['s'] . "<br/>\n";      print "[P]: " . $value['p'] . "<br/>\n";     print "[O]: " . $value['o'] . "<br/>\n";
    foreach ($this->triples as $key => $value) {
      if ($value['p'] == 'http://opengraphprotocol.org/schema/type') { 
        if (preg_match( '/[^a-z_:]/', $value['o']) )  { throw new Exception('BAD_TYPE_CHARS_FAIL'); }
      }
    }
  print "<br/>"; # tmp
  }
  #  Warning: Your og:type may only contain lowercase letters, _ and :. i.e. it must match [a-z_:]+



  public function checkAppIDSyntax() {
    foreach ($this->triples as $key => $value) {
      # print "[S]: " . $value['s'] . "<br/>\n";      print "[P]: " . $value['p'] . "<br/>\n";     print "[O]: " . $value['o'] . "<br/>\n";
      if ($value['p'] == 'http://www.facebook.com/2008/fbmlapp_id') { 
        # print "Checking app_id is purely numeric.";
        if (preg_match( '/[^0-9]+/', $value['o']) )  { throw new Exception('NONDIGIT_APPID_CHARS_FAIL'); } # todo: get tighter regex w/ no false positives from FB.
        # else { print "Passed."; } 
      }
    }
  print "<br/>"; # tmp
    
  }


  function shortify($u) {
    foreach (OGDataGraph::$nslist as $prefix => $uri) {
      # print "DOES $u CONTAIN $uri ? <br/>";
      if(strstr($u , $uri ) ) {
        $short = str_replace( $uri, $prefix . ':', $u ); # abbreviate
        # print "Replacing $uri with $prefix in $u : result is $short<br/>";
        return($short);
      }
    } # end loop thru namespaces; todo: cache
    return ($u);
  }   
  #################################################################################
  ## Services and Utilities
  # TODO: review privacy and security concerns for random Web use

  public function htmlW3CCheck() {
    require_once 'Services/W3C/HTMLValidator.php'; # TODO
    $fn = $this->localFile();	# http://validator.w3.org/check
    $v = new Services_W3C_HTMLValidator();
    print "HTML check! fn=". $fn . "\n";
    $r = $v->validateFile( $fn . ".cache"  );  # http://pear.php.net/package/Services_W3C_HTMLValidator/docs/latest/Services_W3C_HTMLValidator/Services_W3C_HTMLValidator_Response.html
    $this->htmlok = $r;	 # http://pear.php.net/package/Services_W3C_HTMLValidator/docs/latest/Services_W3C_HTMLValidator/Services_W3C_HTMLValidator_Error.html
  }

}

?>
