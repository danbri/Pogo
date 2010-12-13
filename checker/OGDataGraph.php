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

/*
  Copyright 2010 Dan Brickley <danbri@danbri.org>

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/




require_once 'OG_L18N.php'; # natural-lang text strings belong here
require_once 'OG_Checker.php'; 

#error_reporting (E_ALL ^ E_NOTICE ^ E_DEPRECATED ); # looking in a hash for missing info - not a crime
# error_reporting(E_ALL|E_STRICT); # dev't

# verbosity stopgap
function verbose($s) { 
  print "debug: $s\n"; 
} 

class OGDataGraph {
 
  #configuration
  public static $my_base_uri = 'http://localhost/pogo/Pogo/checker'; # used for finding testcases/ etc via HTTP
  public $testdir = "./testcases/";
  #end configuration!

  public $meta = array(); # for testcase-loaded metadata
  # public $htmlok; # notneeded?

  # Meta Content
  public $triples; 		# the full RDF view, follows ARC's conventions for s/p/o structures
  public $fields = array();	# the flat Lite view, simple atribute/values of a common object
				# also need for less-flat view, with nested objects?

  public $url; 			# notneeded?
  public $log = array(); 	#not used yet; plan is to keep transaction log, load, transform etc.

  public static $nslist; 	# namespace prefixes list, loaded from json when needed.

  public static $officialFields = array('title', 'type', 'url', 'image', 'description', 'site_name', 'latitude', 'longitude', 'street-address', 'locality', 'region', 'postal-code', 'country-name', 'email', 'phone_number', 'fax_number', 'video', 'video:height', 'video:width', 'video:type', 'audio', 'audio:title', 'audio:artist', 'audio:album', 'audio:type');
  
  function __toString() {
     return "[OGDataGraph status: triples=".sizeof($this->triples)." ]";
  }

  # function __autoload() {  } 

  public function __get($name) {
    $name = str_replace('_',':', $name);
    # verbose( "Getting '$name'");
    if (array_key_exists($name, $this->fields)) {
      return $this->fields[$name];
    } else {
      #$this->dumpFields();
      # return ''; # no!
    }
  }

  # default to lite, so as not to depend on RDFa parser plugin(s)
  function readFromURL($mode='lite',$u = 'default') {
    if ($u=='default') { $u = $this->url; } 
    # verbose("reading from url $u with mode $mode."); 
    if ($mode == 'lite') {
      $this->liteParse($u);
      $this->buildTriplesFromOGModel();
    } else {
      $this->arcParse($u);
      $this->buildOGModelFromTriples();
    }
    $this->fields['url'] = $u; # different from og_url 
    Checker::paranoidMarkupCheck($this); # uptight for now
  }

  function liteParse($u='default') {
    if ($u != 'default') { $url = $u; } else { $url = $this->meta['url']; $u = $url; }
    # verbose("liteParse: '$u'");
    require_once 'plugins/lite/OpenGraph.php';

    try { 
    $o = @OpenGraph::fetch($u);
    } catch (Exception $e) {                                 
      print "Problem fetching $u: ".$e;
      return;
    }
    # the @ suppresses warnings leaking out into page content
    # http://www.signore.net/code/phpwarnings_code.php
    # todo: better exception structures

    foreach (OGDataGraph::$officialFields as $f) {
      # verbose("Mapping $f");
      @$v = $o->_values[$f]; # suppress warning for missing fields, or unittests fail.
      if ($v) {
        # verbose("Store:$v");
        $this->fields[ 'og:'.$f ] = $v;
      }
    }
  }

  function dumpFields() {
    foreach ($this->fields as $f => $v) {
      print "$f -> $v\n";
    }  
  }

  function dumpTriples() {
    foreach ($this->triples as $key => $value) {
        verbose("Factoid: " . $value['s'] . " " . $value['p'] . " " . $value['o'] . " \n");
      }
  }

  #################################################################################
  # Testcases-related methods

  # utility 
  public static function isValidURL($url) { 
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); 
  }

  # sitemap reader
  public static function getTests($source) {  

    $dom = new DomDocument();
    $dom->load($source);
    $tests = array();
    $xpath = new DomXPath($dom);
    # $xpath->registerNamespace('c', 'http://www.google.com/schemas/sitemap/0.84');
    $xpath->registerNamespace('c', 'http://www.sitemaps.org/schemas/sitemap/0.9');
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
    $this->url = $meta['url'];
    if (preg_match('/^\//', $this->url )) {
      # verbose("Got a relative URL; TODO: prepend base path from local cfg:". OGDataGraph::$my_base_uri;
      $this->url = OGDataGraph::$my_base_uri . $meta['url'];
    } 
    # print "Expected triples: " . $meta['triple_count'] . "\n"; 
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
    # note: don't wire this to untrusted content yet!
    #
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

  public function fullParse($u = 'default' ) {
    return $this->arcParse($u); # defaulting to ARC's RDFa parser; should also add perl parser (for RDFa 1.1).
  }

  # parse, either a specified URI or from pre-loaded metadata
  public function arcParse($u = 'default' ) {
    require_once 'plugins/arc/ARC2.php'; # lots of PHP4-compatibility warnings when in PHP5.
    $meta = $this->meta;    

    if ($u != 'default') { $url = $u; } else { $url = $meta['url']; }
    # verbose("ARC parser being called with url '$u'\n");
    try { 
      $parser = ARC2::getRDFParser();
    } catch (Exception $e) {
      verbose("Exception with getting parser! '$parser' ");
    }

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

  public function simpleTable() {

    $t = "<table border='1' style='background: #eeeeee;'>\n";
    $t .= "<tr><td class=\"ogfield\">Type</td><td>". $this->og_type ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">Image</td><td><a href='".$this->og_image."'><small><img src='". $this->og_image ."' alt='image shown inline'><br/>". $this->og_image ."</small></td></tr>";
    $t .= "<tr><td class=\"ogfield\">Title</td><td>".  $this->og_title ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">URL</td><td>". $this->og_url ."</td></tr>";
    # $t .=  "<tr><td class=\"ogfield\">Site URL (<em>as supplied</em>)</td><td>".  $this->meta['url'] ."</td></tr>";
    $t .= "</table>\n";
    return $t;
  }



  public function rdf2info() {
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
    $t .= "<tr><td class=\"ogfield\">Image</td><td><img src='". $props["http://opengraphprotocol.org/schema/image"] ."' /><br/>". $props["http://opengraphprotocol.org/schema/image"] ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">Title</td><td>". $props["http://opengraphprotocol.org/schema/title"] ."</td></tr>";
    $t .=  "<tr><td class=\"ogfield\">Site URL</td><td>". $site_url ."</td></tr>";
    $t .= "<tr><td class=\"ogfield\">URL</td><td>". $props["http://opengraphprotocol.org/schema/url"] ."</td></tr>";
    $t .= "</table>\n";
    return $t;
  }


  #################################################################################
  #
  #  Hop between Lite and Full views


  public function isOGField($f, $x) {
      # verbose("Comparing $f and $x");
      if ($x =='http://ogp.me/ns#'.$f || $x=='http://opengraphprotocol.org/schema/'.$f) { return true; }
      else { return false; }
  }

  public function buildOGModelFromTriples() {
    if (is_null($this->getTriples())) { throw new Exception("BUILD_OG_NO_TRIPLES_YET"); }
    $f = array(); 
    foreach ( OGDataGraph::$officialFields as $fieldname) {      # verbose("Scanning for field $fieldname.");
      foreach ($this->triples as $key => $v) { 		         # print "XFactoid: ".$v['s']." ".$v['p']." ".$v['o']."\n";
        if (OGDataGraph::isOGField( $fieldname, $v['p'] )) { #verbose("got:".$v['o']."!");
           $f[ 'og:'.$fieldname ] = $v['o']; 
        }
      }
    }
    $this->fields = $f;
    return false; # unimplemented
    # see FB linter: it gets URI from somewhere, title from somewhere, everything else from RDFa
    # todo: write test cases for html order situation
  }

  public function buildTriplesFromOGModel() {
    foreach ($this->fields as $attr => $val ) {
      # verbose("triple: ".$this->meta['url']."  $attr  $val <br/>");
      $claim = array();
      $claim['s'] = $this->url; 
      $claim['p'] = preg_replace('/og:/', 'http://ogp.me/ns#', $attr);
      $claim['o'] = $val; 
      $claim['s_type'] = 'uri'; # todo: double-check with ARC2 that we're API-compatible
      if (preg_match('/^http(s)?:\/\//',$val)){
        $claim['o_type'] = 'uri'; #
      } else {
        $claim['o_type'] = 'literal';
      }
      if (is_null($this->triples)) { $this->triples = array(); }
      array_push($this->triples, $claim);
      # verbose("RDFIZED: ".$claim);
      # todo, push these into triples array (in ARC format)
    }
  }

  # turn http://opengraphprotocol.org/schema/ into http://ogp.me/ns#
  # todo: convert subjects and objects too
  public function shortifyOGTriples() {
    if (is_null($this->triples)) { throw new Exception("SHORTIFY_NO_TRIPLES_YET"); }
    $new_rdf = array();
    foreach ($this->triples as $key => $value) { 
      $value['s'] = str_replace( 'http://opengraphprotocol.org/schema/', 'http://ogp.me/ns#', $value['s']); # rare
      $value['p'] = str_replace( 'http://opengraphprotocol.org/schema/', 'http://ogp.me/ns#', $value['p']); # typical
      $value['o'] = str_replace( 'http://opengraphprotocol.org/schema/', 'http://ogp.me/ns#', $value['o']); # crude
      array_push( $new_rdf, $value ); # hmm - is order semantically meaningful? see recent video discussion.
    }
    return ($new_rdf);
  }

  #################################################################################
  # CHECKS
  public function checkfields() {


    # robustification needed:
    if (is_null($this->triples)) { #verbose("Missing graph."); 
      return; 
    }

    if (sizeof($this->triples) == 0) { #verbose("Empty graph."); 
      return ; 
    }


    # verbose("Running all field value checks.");
    Checker::checkTypeLabel($this); # cf. testcases/fb/examples/bad_type.meta
    Checker::checkAppIDSyntax($this); # cf. testcases/fb/examples/api_key.meta
    Checker::checkMetaName($this);
    Checker::checkNotCSV($this);
    Checker::checkNumericPageID($this);
    Checker::checkAdminsNotBigNumber($this);
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
