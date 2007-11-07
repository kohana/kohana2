<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Cache
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Cache_Core {

	protected $groups;

	protected $driver;

	/*
	 * Constructor: __construct
	 *  Set up driver and get groups.
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	function __construct($config = array())
	{
		$this->driver = new Driver();
		
		$this->groups = $this->get('kohana.groups');
	}

	/*
	 * Method: get
	 *  Get data from cache.
	 *
	 * Parameters:
	 *  name - name of cache entry
	 *
	 * Returns:
	 *   Cached data
	 */
	function get($name)
	{
		return $this->driver->get($name);
	}

	/*
	 * Method: set
	 *  Save data into cache.
	 *
	 * Parameters:
	 *  name - name of cache entry
	 *  item - data to save
	 *
	 * Returns:
	 *   TRUE or FALSE
	 */
	function set($name, $item)
	{
		return $this->driver->set($name, $item);
	}

	/*
	 * Method: del
	 *  Delete cache entry.
	 *
	 * Parameters:
	 *  name - name of cache entry
	 *
	 * Returns:
	 *   TRUE or FALSE
	 */
	function del($name)
	{
		return $this->driver->del($name, $item);
	}

} // End Cache Class