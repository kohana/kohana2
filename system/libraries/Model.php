<?php defined('SYSPATH') or die('No direct script access.');

class Model_Core {

	public function __construct()
	{
		// Load the database into the model
		$this->db = isset(Kohana::instance()->db) ? Kohana::instance()->db : new Database('default');
	}

} // End Model Core