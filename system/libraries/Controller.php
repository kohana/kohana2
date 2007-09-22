<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

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