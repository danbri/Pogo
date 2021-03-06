<?php

# http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html

require_once 'OGDataGraph.php';
require_once 'PHPUnit/Autoload.php';

class OGDataGraphTest extends PHPUnit_Framework_TestCase
{
  
  function setUp() {
    $og1 = $OGDataGraph->new = new OGDataGraph();
  } 

  public function testRaptorAvailable()
  {
    $got_rapper = preg_match('/Raptor RDF syntax parsing/', `rapper --help`);
    $this->assertNotEquals($got_rapper, 0, "rapper commandline utility (RDF Redland parser) needed for some RDFa tests");
  }
 
  /**
   * @depends testRaptorAvailable
   */
  public function testRapperVersion()
  {
    $this->assertNotEquals( preg_match('/^1\.[4567890]/',0, `rapper --version 2>/dev/null`), "Raptor v1.4+ needed" );
  }
 

  public function testARCAvailable()
  {
    if (!file_exists(dirname(__FILE__) . '/plugins/arc/ARC2.php')) {
      $this->fail("ARC2 library not found");
    } else {
      $this->assertTrue(true, "ARC2 library found.");
  }
  }


  public function testLiteAvailable()
  {
    $fn = dirname(__FILE__) . '/plugins/lite/OpenGraph.php';
    if (!file_exists($fn)) {
      $this->fail("Lite OpenGraph library not found at $f");
    } else {
      $this->assertTrue(true, "Lite OpenGraph library found.");
    }
  }

  /**
  * @depends testLiteAvailable
  */
  public function testLiteBasic() 
  {
    require 'plugins/lite/OpenGraph.php';
    $o = OpenGraph::fetch('http://www.imdb.com/title/tt0083658/');
    $this->assertNotNull($o, "opengraph lite parser shouldn't be null.");
    # todo: load from file. test for network. both.
  }



  ##############################################################################
  # Installation / local config -related

  # some testcases have relative URIs for meta['url'] in the JSON. Can we fetch them?
  public function testLocalRepoAccessible() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    # verbose("META:".var_dump($og->meta) ."\n");
    $og->readFromURL(); # we happen to know this is a relative URI
    $this->assertEquals($og->og_title, 'The Rock', "Should get title from local repo." );
  }



  ##############################################################################
  public function testLoadCSVTest() {
    $og = new OGDataGraph();
    try {
      $og->readTest('testcases/fb/examples/not_CSV.meta');
      $this->assertTrue( !is_null($og), "Loaded a test with no error.");
    } catch (Exception $e) {
      $this->fail("Loading not_CSV failed.");
    }
  }

  /**
  * @depends testLoadCSVTest
  */
  public function testMetaCSVTest() {

    require_once 'OG_Checker.php';

    # copied from dependency above (which we could delete...)
    $og = new OGDataGraph();
    try {
      $og->readTest('testcases/fb/examples/not_CSV.meta');
      $this->assertTrue( !is_null($og), "Loaded a test with no error.");
    } catch (Exception $e) {
      $this->fail("Loading not_CSV failed.");
    } # copied from above test.

    $this->assertNotNull($og, "Should get an $og");
    $m = $og->getmeta();
    $this->assertNotNull($m, "Didn't get metadata.");
    #print "\nDUMP: ". var_dump($m) . "\n\n";
    $this->assertEquals($m['triple_count'],1, "not_CSV test has 1 triples.");
    $this->assertEquals($m['status'],'invalid', "not_CSV test has status of 'invalid'.");
    $this->assertEquals($m['url'],'http://developers.facebook.com/tools/lint/examples/not_CSV', "URL should match expectation.");

    try {
      $og->arcParse('http://developers.facebook.com/tools/lint/examples/not_CSV'); # need api for local file too
    } catch (Exception $e) {
      $this->fail("Something terrible happened while parsing ". $m['url'] );
    }
    try {    
      Checker::check_failed_fbadmins_regex($og);
    } catch (Exception $e) {
      $this->assertEquals($e->getMessage(), "FAILED_FBADMINS_REGEX", "not_CSV test should fail fb:admins regex.");
    }
  }




  public function testExceptionExpectedMissingTest() {
    try {    
      $og = new OGDataGraph();
      $og->readTest('testcases/fb/examples/missing-test');
      $this->fail("Library was missing, should've failed.");
    } catch(Exception $e) {
      $this->assertTrue(true, "failed as expected");
    }
  }


  public function testLoadGoodTestOK() {
    try {    
      $og = new OGDataGraph();
      $og->readTest('testcases/fb/examples/good.meta');
    } catch(Exception $e) {
      $this->fail("Didn't load good test, although it should be here.");
    }
  }


  /**
   * @depends testLoadGoodTestOK
   */
  // Testing Utilities for migration as http://opengraphprotocol.org/schema/ becomes http://ogp.me/ns#
   
  public function testLongAndShortNamespaces() {
    $new_ns_regex = '/http:\/\/ogp\.me\/ns#/';

    $og = new OGDataGraph();

    try {    
      $og->readTest('testcases/fb/examples/good.meta');
    } catch(Exception $e) {
      $this->fail("failed to load good.meta.");
    }

    $rdf = $og->getTriples();
    $this->assertNull($rdf, "Should not get an RDF graph until we fetch or build one.");

    $og->arcParse();
    $rdf = $og->getTriples();
    $this->assertNotNull($rdf, "Should've got an RDF graph.");
    foreach ($rdf as $key => $value) {
      if ( preg_match($new_ns_regex, $value['p']) != 0 ) {
        $this->fail("Matched short ns in a testcase that doesn't contain it.");
      }
    }
  
    $this->assertNotNull($og, "Should have an og object now.");

    # verbose("pre-short-ns: ".$og);
    $shorted = $og->shortifyOGTriples();
    # verbose("post-short-ns: ".$og);

    $this->assertNotNull($shorted, "ShortifyingOGTriples should return something not null.");
    $got_shorty = false;
    foreach ($shorted as $key => $value) {
      if ( preg_match($new_ns_regex, $value['p']) != 0 ) { $got_shorty = true; } 
    }
    $this->assertTrue($got_shorty, "Expect to have got shorter ogp.me ns URI at least in 1 or more predicates, after shorteningOGTriples().");
  }

  public function testBuildOGModelFromTriples() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/fb/examples/good.meta'); } catch(Exception $e) { $this->fail(true, "failed load testcases/fb/examples/good.meta");}
    $rdf = $og->fullParse();
    $this->assertNotNull($rdf, "Should get an array of triples.");
    $this->assertType('array', $rdf, "RDF should be represented as an array.");
    $this->assertNotEquals( sizeOf($rdf), 0, "Array should not be sizeof 0.");
    $this->assertNotNull( $og->buildOGModelFromTriples(), "Should be able to buildOGModelFromTriples successfully.");
    $fields = $og->fields;
    $this->assertNotNull($fields, "Should have access to a fields associative array.");
    # $og->dumpFields();
    $this->assertNotNull ($og->fields['og:title'], "Should have generated a title field from the RDFa now.");
    $this->assertEquals($og->fields['og:title'], "The Rock", "Should have extracted title as 'The Rock' from RDFa.");
  }

  /**
  * @depends testBuildOGModelFromTriples
  */
  public function testOverloadedPropertyLookup() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/fb/examples/good.meta'); } catch(Exception $e) { $this->fail(true, "failed load testcases/fb/examples/good.meta");}
    $rdf = $og->fullParse();
    $og->buildOGModelFromTriples();
    # verbose("TITLE SHORT VERSION: ".$og->og_title ."\n");
    $this->assertEquals( $og->og_title , 'The Rock', "Should expose simple OG properties as an associative array.");
    # $this->markTestIncomplete("Need to implement model from triples reader."); # more to test? repeated properties? multiple colons?

  }

 
  /**
  * @depends testOverloadedPropertyLookup
  */
  public function testOverloadedBogusPropertyLookup() {
    // same for a non-existent property, 'og:qwerty'
    $og = new OGDataGraph();
    try { $og->readTest('testcases/fb/examples/good.meta'); } catch(Exception $e) { $this->fail(true, "failed load testcases/fb/examples/good.meta");}
    $rdf = $og->fullParse();
    $og->buildOGModelFromTriples();
    $this->assertNotEquals( $og->og_qwerty , 'The Rock', "Should expose simple OG properties as an associative array.");
    $this->assertNull( $og->og_qwerty, "There is no og:qwerty property in this test case."); 
  }

/* <head>
<meta property="og:title" content="The Rock" />
<meta property="og:type" content="movie" />
<meta property="og:url" content="http://www.imdb.com/title/tt0117500/" />
<meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" />
</head>
*/
  public function testBasicGoodExampleFull() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/fb/examples/good.meta'); } catch(Exception $e) { $this->fail(true, "failed load testcases/fb/examples/good.meta");}
    $rdf = $og->fullParse();
    $og->buildOGModelFromTriples();
    $this->assertEquals( $og->og_title , 'The Rock', '<meta property="og:title" content="The Rock" /> gives og->og_title');
    $this->assertEquals( $og->og_type , 'movie', '<meta property="og:type" content="movie" /> gives og->og_type');
    $this->assertEquals( $og->og_url , 'http://www.imdb.com/title/tt0117500/', '<meta property="og:url" content="http://www.imdb.com/title/tt0117500/" /> gives og->og_url'); 
    $this->assertEquals( $og->og_image , 'http://ia.media-imdb.com/images/rock.jpg', '<meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" /> gives og->image');
  }

  public function testBasicGoodExampleLite() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/fb/examples/good.meta'); } catch(Exception $e) { $this->fail(true, "failed load testcases/fb/examples/good.meta");}
    $og->fetchAndCache();
    $rdf = $og->liteParse();
    # 
    $this->AssertNotNull($og->og_title, "We should have a title.");
    $this->assertEquals( $og->og_title , 'The Rock', '<meta property="og:title" content="The Rock" /> gives og->og_title');
    $this->assertEquals( $og->og_type , 'movie', '<meta property="og:type" content="movie" /> gives og->og_type');
    $this->assertEquals( $og->og_url , 'http://www.imdb.com/title/tt0117500/', '<meta property="og:url" content="http://www.imdb.com/title/tt0117500/" /> gives og->og_url'); 
    $this->assertEquals( $og->og_image , 'http://ia.media-imdb.com/images/rock.jpg', '<meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" /> gives og->image');
    $this->assertNotNull($og->url, "URL field shouldn't be null, but read from meta file in testcases/.");
    $og->buildTriplesFromOGModel();
    # verbose("triples: " . $og->dumpTriples());
  }

  /**
  * @depends testLocalRepoAccessible
  */
  public function testOGPSamplesLite() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1a.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    $og->readFromURL();
    $this->assertEquals($og->og_title, 'The Rock', "Should get title" );
  }

  /**
  * @depends testLocalRepoAccessible
  */
  public function testOGPSamplesFull() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1a.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    $og->readFromURL('full');
    $this->assertEquals($og->og_title, 'The Rock', "Should get title" );
  }


##############################################################################
# Security-related

# Strip out markup in all forms. What do the specs say about this?
# For now, we expect all parsers to throw an exception if they find any < within content. Is this enough? Too much?

  /**
  * @depends testLocalRepoAccessible
  */
  public function testNoScriptLite() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/sec1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('lite'); 
    $this->assertNull($og->og_title, "Should we throw out og fields containing markup? Too strict?"); 
    # $this->assertNotEquals($og->og_title, 'The <script>alert(\'Hello World!\')</script>Rock', 'No funny business.' );
    # we can't anticipate all the variations this could show up in, but should suspect content be rejected or escaped? harsh for now.
  }

  /**
  * @depends testLocalRepoAccessible
  */
  public function testNoScriptFull() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/sec1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('full'); 
    $this->assertNull($og->og_title, "Should we throw out og fields containing markup? Too strict?"); 
#    $this->markTestIncomplete( 'This test has not been implemented yet: Lite parser needs more integration.');
    # $this->assertNotEquals($og->og_title, 'The <script>alert(\'Hello World!\')</script>Rock', 'No funny business.' );
    # we can't anticipate all the variations this could show up in, but should suspect content be rejected or escaped? harsh for now.
  }



##############################################################################



##############################################################################
# Whitespace-handling
# 
# Spec issues: should it say we strip/ignore from start/finish? also strip \n ?

  /**
  * @depends testLocalRepoAccessible
  */
  public function testPassNewlinesThroughFull() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/eg1a.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('full');
    $this->assertNotNull($og->og_description, "Expecting an og:description");
    $this->assertRegExp('/James\n\s+/', $og->og_description, "We expect newlines within content to be preserved.");
  }


  /**
  * @depends testLocalRepoAccessible
  */
  public function testPassNewlinesThroughLite() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/eg1a.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('lite');
    $this->assertNotNull($og->og_description, "Expecting an og:description");
    $this->assertRegExp('/James\n\s+/', $og->og_description, "We expect newlines within content to be preserved.");
  }




  /**
  * @depends testLocalRepoAccessible
  */
  public function testCanReadJapaneseUTF8Lite() {
    $og = new OGDataGraph();
    try {$f='testcases/i18n/restaurant_jp_type_utf8.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('lite');
    $this->assertNotNull($og->og_type, "Expecting a (wrong but should parse) og:type using Japanese katakana symbols.");
  }

  /**
  * @depends testLocalRepoAccessible
  */
  public function testCanReadJapaneseUTF8Full() {
    $og = new OGDataGraph();
    try {$f='testcases/i18n/restaurant_jp_type_utf8.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}

    $this->markTestIncomplete( 'This test has not been implemented yet. Something wrong here, it seems to invoke ARC turtle parser, not rdfa.');

    try {
      $og->readFromURL('full');
    } catch (Exception $e) { 
      $this->fail("Something failed during full parsing. ". $e->getMessage() );
    }
    $this->assertNotNull($og->og_type, "Expecting a (wrong but should parse) og:type using Japanese katakana symbols.");
  }





  /**
  * @depends testLocalRepoAccessible
  */
  public function testCanReadJapaneseShift_JISLite() {
    $og = new OGDataGraph();
    try {$f='testcases/i18n/restaurant_jp_type_Shift_JIS.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('lite');
    $this->assertNotNull($og->og_type, "Expecting a (wrong but should parse) og:type using Japanese katakana symbols.");
  }

  /**
  * @depends testLocalRepoAccessible
  */
  public function testCanReadJapaneseShift_JISFull() {
    $og = new OGDataGraph();
    try {$f='testcases/i18n/restaurant_jp_type_Shift_JIS.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}

###    $this->markTestIncomplete( 'This test has not been implemented yet. Something wrong here, it seems to invoke ARC turtle parser, not rdfa.');

    try {
      $og->readFromURL('full');
    } catch (Exception $e) { 
      $this->fail("Something failed during full parsing. ". $e->getMessage() );
    }
    $this->assertNotNull($og->og_type, "Expecting a (wrong but should parse) og:type using Japanese katakana symbols.");
  }






  public function testReadsGoodShortNSTestcaseMetaFile() {
    $og = new OGDataGraph();

    $f='testcases/fb/examples/good-shortns.meta';
    try { 
      $og->readTest($f);
   } catch(Exception $e) { 
      $this->fail(true, "failed loading testcase metadata $f, exception:".$e);
    }
  }


################################################################################################
# HTML5 parsing tests

  # work-in-progress
  # to test just this one, ...
  # phpunit --filter testhtml5libParserAvailable  --colors --verbose OGDataGraphTest
  #

  public function testhtml5libParserAvailable() {
    require_once 'plugins/html5lib//library/HTML5/Parser.php';
    $dom = HTML5_Parser::parse('<html><head xmlns:og="http://ogp.me/#"><meta property="og:title" content="html5"/><body>');
    $nodelist = HTML5_Parser::parseFragment('<b>Boo</b><br>');
    $nodelist2 = HTML5_Parser::parseFragment('<td>Bar</td>', 'table');

    # DOMNodeList, DOMDocument, 
    $this->assertNotNull($dom, "Should get an HTML DOM from htmllib parser.");
    $this->assertNotNull($nodelist, "Should get a nodelist from htmllib parser.");
    $this->assertNotNull($nodelist2, "Should get a second nodelist from htmllib parser.");

    $this->assertType("DOMDocument", $dom, "DOM should be a DOM");
    $this->assertType("DOMNodeList", $nodelist, "Nodelist should be a DOMNodeList");
    $this->assertType("DOMNodeList", $nodelist2, "Nodelist should be a DOMNodeList");

    #verbose("HTML5: ". var_dump($dom) . "  ".var_dump($nodelist ));
    #verbose("Tags are:  ". var_dump($dom->getElementsByTagName('meta')));

    $og = new OGDataGraph();
  
    $f='testcases/fb/examples/good-shortns.meta';
    try {
      $og->readTest($f);
    } catch(Exception $e) {
       $this->fail(true, "failed loading testcase metadata $f, exception:".$e);
    }
   $this->assertNotNull($og);
   $og->fetchAndCache();
   $this->assertNotNull($og->content);
   $domnodelist = HTML5_Parser::parseFragment($og->content);
   $this->assertNotNull($domnodelist);
   
   $attrs = $domnodelist->item(1)->attributes;
   $this->assertNotNull($attrs, "Should get some meta attributes.");

   $meta_property = $attrs->getNamedItem("property");
   # $this->assertNotNull( $meta_property, "Our meta element has a property attribute.");

   # future investigation...

   foreach ($domnodelist as $n) {
     $amap = $n->attributes;
	#     $this->assertNotNull($amap, "Each meta node should have attributes.");
	#     verbose("\n\n\nDOMNode: ".print_r($n). "\n\n\n");
   }

 }

##############################################################################
# Charset-handling

# see mixi testcase for example. how to structure tests here?
#


################################################################################################


# BUG CHASING CORNER

# ok testcases/fb/examples/bad_app_id was throwing OG_NAMESPACE_UNDECLARED when we know it's got one.
# ... it's not now, but we'll keep this handy.
   
  public function testOGNamespaceInBadAppIDTestcase() {
    $og = new OGDataGraph();
     $f='testcases/fb/examples/bad_app_id.meta';
    try {
      $og->readTest($f);
    } catch(Exception $e) {
      $this->fail(true, "failed loading testcase metadata $f, exception:".$e);
    }
    $this->assertNotNull($og,"Should have a graph.");
    $this->assertNotNull($og->meta,"Should have a metadata-annotated graph.");
    $this->assertNotNull($og->meta['url'],"Should have a meta field for url in graph.");
    $og->fetchAndCache(); ## populates ->content
    $og->liteParse();
    $this->assertNotNull($og->content,"Should have raw content of cached page.");
    $report = Checker::checkall($og);
  }


# todo: cache vs web - confirm
#
#  testcases/imdb/legend_guardians.cache 
#  this testcase has a different image (from wikipedia) and the word 'cached' added to og:title
#  todo: tests that make 100% sure we're loading from disk vs net when we think we are
#
# 1. loaded from disk, og:title contains 'cached'
#   - same for lite and full
#.3. from web, doesn't contain
#   - same for lite and full



// http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
//    $this->markTestIncomplete( 'This test has not been implemented yet: Lite parser not integrated.');
}
?>
