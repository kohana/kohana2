<?php defined('SYSPATH') or die('No direct script access.');
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
	protected $db;

	public function __construct($group = 'default')
	{
		
	}

	public function where()
	{
		$this->add(func_get_args(), 'AND');
	}

	public function orwhere()
	{
		$this->add(func_get_args(), 'OR');
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

			foreach ($args as $key => $value)
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

	public function build($group == 'default')
	{
		if (is_string($group))
		{
			$this->db = Database::instance($group);
		}
		elseif (is_array($group))
		{
			$this->db = new Database($group);
		}
		elseif ($group instanceof Database)
		{
			$this->db = $group;
		}

		// Iterate the where array to build the query portion and return it
		$where_string = '(';
		foreach ($this->where as $where)
		{
			$where_string .= $this->db->driver->escape_column($where[0]['key']).' '.$where[0]['op'].' '.$this->db->driver->escape($where[0]['value']).(count($this->where) > 1 ? ' '.$where[1].' ' : '');
		}
		$where_string .= ')';

		return $where_string;
	}

	public function __toString()
	{
		return $this->build();
	}
}