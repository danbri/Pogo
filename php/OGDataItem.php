<?php

# PHP Utility for checking Open Graph Protocol markup.
#
# Nearby in the Web: http://webr3.org/apps/play/api/lib a js rdfa API 


class OGDataItem {

  public $meta = array();
  public $testdir = "./testcases/";
  public $htmlok;

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
    return ( $this->testdir . $this->meta[testgroup] . "/" . $this->meta[testid]);
  }

  public function readTest($tc) { 
    
    $tc = preg_replace( '/file:/','', $tc);
    $handle = fopen( $tc, "r");
    $contents = stream_get_contents($handle);
    print "meta: ". $contents . "\n\n";
    fclose($handle);
    $meta = json_decode( $contents, true );
    $this->meta = $meta;
    # print "META: ". $meta . "\n";
    print "Expected triples: " . $meta['triple_count'] . "\n"; 
    print "Actual triples: TODO\n";
    print "\n";
    $fn = $meta[testgroup] . "/" . $meta[testid];

  }



  ## Services and Utilities
  # TODO: identify privacy and security concerns; which can be used in WWW interface? vs API?


  public function htmlW3CCheck() {

    require_once 'Services/W3C/HTMLValidator.php'; # TODO

    $fn = $this->localFile();	# http://validator.w3.org/check
    $v = new Services_W3C_HTMLValidator();
    print "HTML check! fn=". $fn . "\n";

    $r = $v->validateFile( $fn . ".cache"  );  # http://pear.php.net/package/Services_W3C_HTMLValidator/docs/latest/Services_W3C_HTMLValidator/Services_W3C_HTMLValidator_Response.html
    $this->htmlok = $r;
    # http://pear.php.net/package/Services_W3C_HTMLValidator/docs/latest/Services_W3C_HTMLValidator/Services_W3C_HTMLValidator_Error.html

  }

  public function rapperCheck() {
    $meta = $this->meta;
    $fn = $this->localFile();
    # Let's compare tidy and untidy counts
    # Requires: HTML Tidy and Rapper (Redland RDF parser)
    $c1 = "rapper  --count -i rdfa " . $fn . ".cache " . $meta[url] ;
    print "Basic commandline: " . $c1 . "\n\n";

    $c2 = "tidy -f logs/_errorlog -numeric -q -asxml ".$fn . ".cache" . "  | rapper  --count -i rdfa - ".$meta[url];
    print "Tidied commandline: " . $c2 . "\n\n";
    
    # TODO: impl
    # http://us.php.net/manual/en/function.shell-exec.php
    # http://us.php.net/manual/en/language.operators.execution.php

    $rc1 = `$c1`;
    print "Rapper count 1: $rc1 \n";

    $rc2 = `$c2`;
    print "Rapper count 2: $rc2 \n";

  }


  public function arcParse() {

  require_once 'plugins/arc/ARC2.php';
  $meta = $this->meta;
  $url = $meta[url]; 				#'http://www.rottentomatoes.com/m/oceans_eleven/';

  $parser = ARC2::getRDFParser();
  $parser->parse($url);
  $parser->extractRDF('rdfa');
  $triples = $parser->getTriples();

  print "<h3>Extended data</h3>";
  foreach ($triples as $key => $value) {
     if (!preg_match( '/http:\/\/opengraphprotocol\.org/', $value['p'])) {
       if (preg_match( '/poshrdf/', $value['p'])) continue;
       if (preg_match( '/stylesheet/', $value['p'])) continue;
        print "Factoid: " . $value['s'] . " " . $value['p'] . " " . $value['o'] . " \n";
     }
  }

  # factoid: p :  http://purl.org/dc/elements/1.1/title
  # factoid: o :  Oceans (Disneynature's Oceans) Movie Reviews, Pictures - Rotten Tomatoes
  # factoid: s_type :  uri
  # factoid: o_type :  literal

  }

}

?>
