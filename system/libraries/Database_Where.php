<?php //defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database_Where.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Where_Core {

	protected $where = array();
	protected $drivers = array();

	public function __construct($group = 'default')
	{
		
	}

	public function where()
	{
		$this->add(func_get_args(), 'AND');
		return $this;
	}

	public function orwhere()
	{
		$this->add(func_get_args(), 'OR');
		return $this;
	}

	private function add($args, $type)
	{
		// Count the arguments: one, two or three?
		$num_args = count($args);

		// A where object was passed
		if ($num_args === 1 AND is_object($args[0]))
		{
			$this->where[] = array($args[0], $type);
		}
		// An array for the conditions
		elseif ($num_args === 2 OR ($num_args === 1 AND is_array($args[0])))
		{
			$operator = $num_args === 2 ? $args[1] : '=';

			foreach ($args[0] as $key => $value)
			{
				$this->where[] = array(array('key' => $key, 'value' => $value, 'op' => $operator), $type);
			}
		}
		// Separate conditions
		elseif ($num_args === 3)
		{
			$this->where[] = array(array('key' => $args[0], 'value' => $args[1], 'op' => $args[2]), $type);
		}
	}

	public function build($group = 'default')
	{
		if (is_string($group))
		{
			$config = Config::item('database.'.$group);
			$conn = Database::parse_con_string($config['connection']);
			$driver = $conn['type'];
		}
		elseif (is_array($group)) // DB Group was passed
		{
			$conn = Database::parse_con_string($group['connection']);
			$driver = $conn['type'];
		}
		elseif ($group instanceof Database_Driver)
		{
			$driver = $group;
		}

		if (!isset($this->drivers[$driver]))
		{
			// Set driver name
			$driver_class_name = 'Database_'.ucfirst($driver).'_Driver';
	
			// Load the driver
			if ( ! Kohana::auto_load($driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', $driver_class_name);
	
			// Initialize the driver
			$this->drivers[$driver] = new $driver_class_name();
	
			// Validate the driver
			if ( ! ($this->drivers[$driver] instanceof Database_Driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', 'Database drivers must use the Database_Driver interface.');
		}

		// Iterate the where array to build the query portion and return it
		$wheres_left = count($this->where);
		$where_string = '(';
		foreach ($this->where as $where)
		{
			if (is_object($where[0]))
			{
				$where_string.=$where[0]->build();
			}
			else
			{
				$where_string.=$this->db->driver->escape_column($where[0]['key']).' '.$where[0]['op'].' '.$this->db->driver->escape($where[0]['value']).($wheres_left-- > 1 ? ' '.$where[1].' ' : '');
			}
		}
		$where_string .= ')';

		return $where_string;
	}

	public function __toString()
	{
		return $this->build();
	}
}