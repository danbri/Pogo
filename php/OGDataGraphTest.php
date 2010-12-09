<?php

# http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html

require_once 'OGDataGraph.php';
require_once 'PHPUnit/Autoload.php';

class OGDataGraphTest extends PHPUnit_Framework_TestCase
{
  
  function setUp() {
    $og1 = $OGDataGraph->new = new OGDataGraph();
    verbose("Made an ".$og1);
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
      $og->checkNotCSV();
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

} 






# http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.exceptions
?>

