<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model base class.
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
	 * Loads or sets the database instance.
	 *
	 * @param   object   Database instance
	 * @return  void
	 */
	public function __construct($database = NULL)
	{
		static $db;

		if (is_object($database) AND ($database instanceof Database))
		{
			// Use the passed database instance
			$this->db = $database;
		}
		else
		{
			// Load the default database if necessary
			($db === NULL) and $db = new Database('default');

			// Use the static database
			$this->db = $db;
		}
	}

} // End Model