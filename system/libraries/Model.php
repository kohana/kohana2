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

	protected $db;

	/*
	 * Constructor: __construct
	 *  Loads database to $this->db.
	 */
	public function __construct()
	{
		// Load the database into the model
		if (Event::has_run('system.pre_controller'))
		{
			$this->db = isset(Kohana::instance()->db) ? Kohana::instance()->db : new Database('default');
		}
		else
		{
			$this->db = new Database('default');
		}
	}

} // End Model Core