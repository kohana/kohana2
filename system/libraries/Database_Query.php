<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query wrapper.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Core {

	protected $_sql;
	protected $_params;
	protected $_ttl = FALSE;

	public function __construct($sql = NULL)
	{
		$this->_sql = $sql;
	}

	public function __toString()
	{
		// Return the SQL of this query
		return $this->_sql;
	}

	public function sql($sql)
	{
		$this->_sql = $sql;

		return $this;
	}

	public function set($key, $value)
	{
		$this->_params[$key] = $value;

		return $this;
	}

	public function bind($key, & $value)
	{
		$this->_params[$key] =& $value;

		return $this;
	}

	public function execute($db = 'default')
	{
		if ( ! is_object($db))
		{
			// Get the database instance
			$db = Database::instance($db);
		}

		// Import the SQL locally
		$sql = $this->_sql;

		if ( ! empty($this->_params))
		{
			// Quote all of the values
			$params = array_map(array($db, 'quote'), $this->_params);

			// Replace the values in the SQL
			$sql = strtr($sql, $params);
		}

		if ($this->_ttl !== FALSE)
		{
			// Load the result from the cache
			return $db->query_cache($sql, $this->_ttl);
		}
		else
		{
			// Load the result (no caching)
			return $db->query($sql);
		}
	}

	/**
	 * Set caching for the query
	 *
	 * @param  boolean|int     Time-to-live (false to disable, NULL for Cache default, seconds otherwise)
	 * @return Database_Query
	 */
	public function cache($ttl = NULL)
	{
		$this->_ttl = $ttl;

		return $this;
	}

} // End Database_Query