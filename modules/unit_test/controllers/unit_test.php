<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Unit_test controller
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		$test = new Unit_Test(MODPATH.'unit_test/tests/');
		echo $test->report();
	}

}