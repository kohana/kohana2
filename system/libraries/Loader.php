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
 * Loader Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/loader.html
 */
class Loader_Core {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		foreach(Config::item('core.autoload') as $type => $load)
		{
			if ($load == FALSE) continue;

			foreach(explode(',', $load) as $name)
			{
				if (($name = trim($name)) == FALSE) continue;

				switch($type)
				{
					case 'libraries':
						if ($name == 'database')
						{
							$this->database();
						}
						else
						{
							$this->library($name);
						}
					break;
					case 'models':
						$this->model($name);
					break;
				}
			}
		}
	}

	/**
	 * Library
	 *
	 * @access public
	 * @param  string
	 * @param  array
	 * @param  boolean
	 * @return mixed
	 */
	public function library($name, $config = array(), $return = FALSE)
	{
		if (isset(Kohana::instance()->$name))
			return FALSE;

		if ($name == 'database')
		{
			$this->database($config);
		}
		else
		{
			$class = ucfirst($name);
			$class = new $class($config);

			if ($return == TRUE)
			{
				return $class;
			}
			else
			{
				Kohana::instance()->$name = $class;
			}
		}
	}

	/**
	 * Database
	 *
	 * @access public
	 * @param  string
	 * @param  boolean
	 * @return mixed
	 */
	public function database($group = 'default', $return = FALSE)
	{
		$db = new Database($group);

		// Return the new database object
		if ($return == TRUE)
		{
			return $db;
		}
		else
		{
			Kohana::instance()->db = $db;
		}
	}

	/**
	 * Helper
	 *
	 * @access public
	 * @param  string
	 * @return void
	 */
	public function helper($name)
	{
		// Allow recursive loading
		if (is_array($name))
		{
			$helpers = $name;

			foreach($helpers as $name)
			{
				$this->helper($name);
			}
		}
		else
		{
			include Kohana::find_file('helpers', $name, TRUE);
		}
	}

	/**
	 * Model
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function model($name, $alias = FALSE)
	{
		// The alias is used for Controller->alias
		$alias = ($alias == FALSE) ? $name : $alias;
		$class = ucfirst($name).'_Model';

		if (isset(Kohana::instance()->$alias))
			return FALSE;

		// Load the model
		$model = new $class();

		if ($alias === TRUE)
		{
			return $model;
		}
		else
		{
			Kohana::instance()->$alias = $model;
		}
	}

	/**
	 * View
	 *
	 * @access public
	 * @param  string
	 * @param  array
	 * @return object
	 */
	public function view($name, $data = array())
	{
		return new View($name, $data);
	}

} // End Loader Class