<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Model
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Model_Core {

	/*
	 * Constructor: __construct
	 *  Loads database into '$this->db'.
	 */
	public function __construct()
	{
		// Load the database into the model
		$this->db = isset(Kohana::instance()->db) ? Kohana::instance()->db : new Database('default');
	}

} // End Model Core