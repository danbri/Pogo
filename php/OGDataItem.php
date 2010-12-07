<?php

# PHP Utility for checking Open Graph Protocol markup.
#
# Nearby in the Web: http://webr3.org/apps/play/api/lib a js rdfa API 
#
# http://developers.facebook.com/blog/post/390
# http://developers.facebook.com/tools/lint/examples/
# http://developers.facebook.com/tools/lint/?url=opengraphprotocol.org
# http://developers.facebook.com/tools/lint/?url=http://www.imdb.com/title/tt0117500/
# http://developers.facebook.com/tools/lint/?url=developers.facebook.com
# http://developers.facebook.com/tools/lint/?url=http://www.rottentomatoes.com/m/matrix/
# http://developers.facebook.com/tools/lint/?url=blog.paulisageek.com


class OGDataItem {

  public $meta = array();
  public $testdir = "./testcases/";
  public $htmlok;
  public $triples;

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
    #print "meta: ". $contents . "\n<br/>\n";
    fclose($handle);
    $meta = json_decode( $contents, true );
    $this->meta = $meta;
#    print "meta2: ". $meta . "\n<br/>\n";
    # print "META: ". $meta . "\n";
    #    print "Expected triples: " . $meta['triple_count'] . "\n"; 
    #    print "Actual triples: TODO\n";
    $fn = $meta[testgroup] . "/" . $meta[testid];
  }


  public function getmeta(){ 
    return $this->meta;
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
    
    #print "Basic commandline: " . $c1 . "\n\n";

    $c2 = "tidy -f logs/_errorlog -numeric -q -asxml ".$fn . ".cache" . "  | rapper  --count -i rdfa - ".$meta[url];
    # print "Tidied commandline: " . $c2 . "\n\n";
    
    # TODO: impl
    # http://us.php.net/manual/en/function.shell-exec.php
    # http://us.php.net/manual/en/language.operators.execution.php

    $rc1 = `$c1`;
    # print "Rapper count 1: $rc1 \n";

    $rc2 = `$c2`;
    # print "Rapper count 2: $rc2 \n";

  }


  public function arcParse() {

  require_once 'plugins/arc/ARC2.php';
  $meta = $this->meta;
  $url = $meta[url]; 				#'http://www.rottentomatoes.com/m/oceans_eleven/';

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
  #  return($this->triples);
  # factoid: p :  http://purl.org/dc/elements/1.1/title
  # factoid: o :  Oceans (Disneynature's Oceans) Movie Reviews, Pictures - Rotten Tomatoes
  # factoid: s_type :  uri
  # factoid: o_type :  literal

  }


  public function rdf2info() {
    #print "Got a graph ". $g;
    #print "TODO: pull type, admins, app ID, Description, Image, title, Site URL, URL from it.";
    # for that, we need an OO repr?
    $props = array(); #todo
    foreach ($this->triples as $key => $value) {
       if (preg_match( '/http:\/\/opengraphprotocol\.org/', $value['p'])) {
          $prop =  $value['p'];
          $props[$prop] = $value['o'];
          print $prop. " " . $value['o'] . " \n";
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

/* 
Type	movie
Admins	1106591 615860
App ID	326803741017
Description	The Matrix - Directed by Andy Wachowski , Larry Wachowski With Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss, Hugo Weaving	In the near future, a computer hacker named Neo discovers that all life on Earth may be nothing more than an elaborate facade created by a malevolent.... Visit Rotten Tomatoes for Photos, Showtimes, Cast, Crew, Reviews, Plot Summary, Comments, Discussions, Taglines, Trailers, Posters, Fan Sites.
Image	
Title	The Matrix
Site URL	http://www.rottentomatoes.com/
URL	http://www.rottentomatoes.com/m/matrix/
*/


}

?>
