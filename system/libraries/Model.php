<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Model_Core {

	protected $db;

	/**
	 * Loads database to $this->db.
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

} // End Model class