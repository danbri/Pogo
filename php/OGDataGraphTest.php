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


    $this->assertNotNull($og, "Should get an $og from prev test via dependency(?).");
    $m = $og->getmeta();
    $this->assertNotNull($m, "Didn't get metadata.");
    #print "\nDUMP: ". var_dump($m) . "\n\n";
    $this->assertEquals($m['triple_count'],0, "not_CSV test has 0 triples.");
    $this->assertEquals($m['status'],'invalid', "not_CSV test has status of 'invalid'.");
    $this->assertEquals($m['url'],'http://developers.facebook.com/tools/lint/examples/not_CSV', "URL should match expectation.");

    try {
      $og->arcParse('http://developers.facebook.com/tools/lint/examples/not_CSV'); # need api for local file too
    } catch (Exception $e) {
      $this->fail("Something terrible happened while parsing ". $m['url'] );
    }
    try {    
#      $og->checkNotCSV();
      Checker::checkNotCSV($og);
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

  public function testOGPSamplesLite() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    $og->readFromURL();
    $this->assertEquals($og->og_title, 'The Rock', "Should get title" );
  }

  public function testOGPSamplesFull() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    $og->readFromURL('full');
    $this->assertEquals($og->og_title, 'The Rock', "Should get title" );
  }


##############################################################################
# Installation / local config -related

  # some testcases have relative URIs for meta['url'] in the JSON. Can we fetch them?
  public function testLocalRepoAccessible() {
    $og = new OGDataGraph();
    try { $og->readTest('testcases/ogp/eg1.meta'); } catch(Exception $e) { $this->fail(true, "failed load"); }
    $og->readFromURL(); # we happen to know this is a relative URI
    $this->assertEquals($og->og_title, 'The Rock', "Should get title from local repo." );
  }

##############################################################################
# Security-related

# Strip out markup in all forms. What do the specs say about this?
# For now, we expect all parsers to throw an exception if they find any < within content. Is this enough? Too much?

  /**
  * @depends testLocalRepoAccessible
  * @expectedException Exception
  * @expectedExceptionMessage UNESCAPED_LESSTHAN_IN_CONTENT_VALUE
  */
  public function testNoScriptLite() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/sec1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL(); # default to lite; for full need to mention uri, damn.
    $this->markTestIncomplete( 'This test has not been implemented yet: Lite parser not integrated.');
    $this->assertNull($og->og_title, "RDFa parsers should reject markup within property values when in content attribute."); # too harsh?
    # print $og->og_title;
    # $this->assertNotEquals($og->og_title, 'The <script>alert(\'Hello World!\')</script>Rock', 'No funny business.' );
    # we can't anticipate all the variations this could show up in, but should suspect content be rejected or escaped? harsh for now.
  }

  /**
  * @depends testLocalRepoAccessible
  * @expectedException Exception
  * @expectedExceptionMessage UNESCAPED_LESSTHAN_IN_CONTENT_VALUE
  */
  public function testNoScriptFull() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/sec1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('full');
    print $og->og_title;
    $this->assertNull($og->og_title, "RDFa parsers should reject markup within property values when in content attribute.");
    # don't try scrubbing. $this->assertEquals('The Rock', $og->og_title, "Should get title from local repo." );
  }


##############################################################################



##############################################################################
# Whitespace-handling
# 
# Spec issues: should it say we strip/ignore from start/finish? also strip \n ?

  public function testPassNewlinesThroughFull() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/eg1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('full');
    $this->assertNotNull($og->og_description, "Expecting an og:description");
    $this->assertRegExp('/James\n\s+/', $og->og_description, "We expect newlines within content to be preserved.");
  }

  public function testPassNewlinesThroughLite() {
    $og = new OGDataGraph();
    try {$f='testcases/ogp/eg1.meta';$og->readTest($f);}catch(Exception $e){$this->fail(true, "failed loading $f, exception:".$e);}
    $og->readFromURL('lite');
    $this->assertNotNull($og->og_description, "Expecting an og:description");
    $this->assertRegExp('/James\n\s+/', $og->og_description, "We expect newlines within content to be preserved.");
  }


##############################################################################
# Charset-handling



#


// http://www.phpunit.de/manual/current/en/incomplete-and-skipped-tests.html
//    $this->markTestIncomplete( 'This test has not been implemented yet: Lite parser not integrated.');
}
?>
