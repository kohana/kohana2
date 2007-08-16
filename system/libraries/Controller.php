<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Controller extends Kohana {
	
	public function __construct()
	{
		// This must always be called, it provides the singleton functionality
		parent::__construct();

		// Loader should always be available
		$this->load = new Loader();
	}

} // End Controller Class