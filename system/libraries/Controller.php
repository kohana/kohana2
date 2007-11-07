<?php defined('SYSPATH') or die('No direct script access.');
 /*
 * Class: Controller
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Controller_Core extends Kohana {

	/*
	 * Constructor: __construct
	 *  Loads the Input, URI and Loader libraries into the '$this' namespace.
	 */
	public function __construct()
	{
		// This must always be called, it provides the singleton functionality
		parent::__construct();

		// Loader should always be available
		$this->load = new Loader();

		// URI should always be available
		$this->uri = new URI();

		// Input should always be available
		$this->input = new Input();
	}

	/*
	 * Method: kohana_include_view
	 *  Includes a View within the controller scope.
	 *
	 * Parameters:
	 *  kohana_view_filename - filename
	 *  kohana_input_data    - array of data to make accessible within the view
	 *
	 * Returns:
	 *  Output of view file
	 */
	public function kohana_include_view($kohana_view_filename, $kohana_input_data)
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