<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default Kohana controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Welcome_Controller extends Controller {

	public function index()
	{
		$c = Cache::instance();
		echo $c->get('bla bla')."<br>";
		$c->set('bla bla', 'Hello2/ AArse');
		echo $c->get('bla bla')."<br>";
	}


} // End Welcome Controller