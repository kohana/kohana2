<?php 
/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

namespace Library;

defined('SYSPATH') OR die('No direct access allowed.');

abstract class Controller {

	// Allow all controllers to run in production by default
	const ALLOW_PRODUCTION = TRUE;

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		if (\Kernel\Kohana::$instance == NULL)
		{
			// Set the instance to the first controller loaded
			\Kernel\Kohana::$instance = $this;
		}
	}

	/**
	 * Handles methods that do not exist.
	 *
	 * @param   string  method name
	 * @param   array   arguments
	 * @return  void
	 */
	public function __call($method, $args)
	{
		// Default to showing a 404 page
		\Kernel\Event::run('system.404');
	}

} // End Controller Class