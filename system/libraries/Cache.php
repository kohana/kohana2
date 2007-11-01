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

	function __construct($config = array())
	{
		$this->driver = new Driver();
		
		$this->groups = $this->get('kohana.groups');
	}

	function get($name)
	{
		return $this->driver->get($name);
	}

	function set($name, $item)
	{
		return $this->driver->set($name, $item);
	}

	function del($name)
	{
		return $this->driver->del($name, $item);
	}

} // End Cache Class