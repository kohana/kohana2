<?php
/**
 * Kohana Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * $Id: Controller.php 3326 2008-08-09 21:24:30Z Shadowhand $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Controller_Core {

	// Allow all controllers to run in production by default
	const ALLOW_PRODUCTION = TRUE;

	// Current request
	public $request;

	/**
	 * Loads the request locally.
	 */
	public function __construct(Kohana_Request $request)
	{
		$this->request = $request;
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
		Event::run('system.404', $this->request->current_uri);
	}

	/**
	 * Called when the request stops processing the controller.
	 *
	 * @return  void
	 */
	public function _end_request()
	{
		// Do nothing by default
	}

	/**
	 * Includes a View within the controller scope.
	 *
	 * @param   string  view filename
	 * @param   array   array of view variables
	 * @return  string
	 */
	public function _kohana_load_view($kohana_view_filename, $kohana_input_data)
	{
		if ($kohana_view_filename == '')
			return;

		// Buffering on
		ob_start();

		// Import the view variables to local namespace
		extract($kohana_input_data, EXTR_SKIP);

		// Views are straight HTML pages with embedded PHP, so importing them
		// this way insures that $this can be accessed as if the user was in
		// the controller, which gives the easiest access to libraries in views
		include $kohana_view_filename;

		// Fetch the output and close the buffer
		return ob_get_clean();
	}

} // End Controller Class