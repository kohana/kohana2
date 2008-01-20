<?php defined('SYSPATH') or die('No direct script access.');
 /**
 * Class: Controller
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Controller_Core {

	/**
	 * Constructor: __construct
	 *  Loads the Input, URI and Loader libraries into the '$this' namespace.
	 */
	public function __construct()
	{
		if (Kohana::$instance === NULL)
		{
			// Set the instance to the first controller loaded
			Kohana::$instance = $this;

			// Loader should always be available
			$this->load = new Loader;

			// URI should always be available
			$this->uri = new URI;

			// Input should always be available
			$this->input = new Input;
		}
	}

	/**
	 * Includes a View within the controller scope.
	 *
	 * Parameters:
	 *  kohana_view_filename - filename
	 *  kohana_input_data    - array of data to make accessible within the view
	 *
	 * Returns:
	 *  Output of view file
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