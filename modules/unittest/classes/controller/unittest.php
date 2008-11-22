<?php
/**
 * Unittest controller.
 *
 * $Id$
 *
 * @package    Unittest
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Controller_Unittest extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		echo new Unittest;
	}

}