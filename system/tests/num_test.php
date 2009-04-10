<?php
class Helper_Num_Test extends PHPUnit_Framework_TestCase
{
    public static function main()
    {
		$suite = new PHPUnit_Framework_TestSuite(__CLASS__);
		$result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
		
    }

    public function tearDown()
    {
		
    }

    public function test_round()
    {
		$n = num::round(23.322121321);
		$this->assertEquals($n, 25);
		$this->assertNotEquals($n, 26);
    }
}