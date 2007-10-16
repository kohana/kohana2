<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Cache Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/cache.html
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