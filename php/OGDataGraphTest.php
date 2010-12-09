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
 

  # http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.exceptions
  public function testARCAvailable()
  {
    try {
      require_once 'plugins/arc/ARC2.php';
    } catch (Exception $e) {
      $this->fail("ARC2 library not found");
      return;
    }
    $this->assertTrue(true, "ARC2 library found.");
  }


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testFailingInclude()
    {
        include 'not_existing_file.php';
    }
} 
?>

