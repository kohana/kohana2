<?php defined('SYSPATH') or die('No direct access allowed.');
/* $Id$ */


class Controller_Core extends Kohana {

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

	public function kohana_include_view($kohana_view_filename, $kohana_input_data)
	{
		if ($kohana_view_filename == '') return;

		// Buffering on
		ob_start();

		// Import the input variables to local namespace
		extract($kohana_input_data, EXTR_SKIP);

		// Views are straight HTML pages with embedded PHP, so importing them
		// this way insures that $this can be accessed as if the user was in
		// the controller, which gives the easiest access to libraries in views
		include $kohana_view_filename;

		// Fetch the HTML output
		$kohana_view_output = ob_get_contents();

		// Flush the buffer
		ob_end_clean();

		// Return the view, yay!
		return $kohana_view_output;
	}

} // End Controller Class