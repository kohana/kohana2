<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database.php 2302 2008-03-13 17:09:55Z Shadowhand $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Core {

	// Database instances
	protected static $instances;

	// Driver instance
	protected $driver;

	public static function instance($name = 'default', $config = NULL)
	{
		if (empty(Database::$instances[$name]))
		{
			// Create a named Database instance
			Database::$instances[$name] = new Database($config);
		}

		return Database::$instances[$name];
	}

	public function __construct($config = NULL)
	{
		if (empty($config))
		{
			// Load default configuration
			$config = Config::item('database.default');
		}

		if ( ! (is_array($config) AND isset($config['hostname']) AND isset($config['database'])))
			throw new Kohana_Exception('database.invalid_configuation');
	}

	public function select($col = '*')
	{
		$args = func_get_args();

		if (empty($args))
		{
			// Default to "SELECT *"
			$args = array('*');
		}

		return new Database_Select($args, $this);
	}

	public function query($sql = NULL)
	{
		if ( ! is_string($sql))
		{
			echo 'not an SQL string';
		}
	}

} // End Database