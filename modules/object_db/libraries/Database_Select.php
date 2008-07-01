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
class Database_Select_Core {

	protected $db;

	protected $from       = array();
	protected $join       = array();
	protected $where      = array();
	protected $order_by   = array();
	protected $group_by   = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;

	public function __construct(array $columns, Database $db)
	{
		$this->db = $db;

		foreach($columns as $column)
		{
			if (is_string($column))
			{
				$column = trim($column);

				if ($column === '') continue;

				if (preg_match('/^DISTINCT\s++(.+)$/i', $column, $matches))
				{
					// Find distinct columns
					$this->distinct = TRUE;

					// Use only the column name
					$column = $matches[1];
				}
			}

			$this->select[] = $column;
		}
	}

	public function from($tables)
	{
		$tables = func_get_args();
		
		foreach ($tables as $table)
		{
			if (is_string($table))
			{
				$table = trim($table);

				if ($table === '') continue;
			}

			$this->from[] = $table;
		}

		return $this;
	}

	public function where($keys, $op = '=', $value = NULL)
	{
		if ( ! is_array($keys))
		{
			// Make keys into key/value pairs
			$keys = array($keys => $value);
		}

		$this->where[] = new Database_Where($keys, $op, 'AND', $this->db);

		return $this;
	}

	public function or_where($keys, $op = '=', $value = NULL)
	{
		if ( ! is_array($keys))
		{
			// Make keys into key/value pair
			$keys = array($keys => $value);
		}

		$this->where[] = new Database_Where($keys, $op, 'OR', $this->db);

		return $this;
	}

	public function order_by($columns, $direction = NULL)
	{
		if ( ! is_array($columns))
		{
			// Make columns into key/value pair
			$columns = array($columns => $direction);
		}

		foreach ($columns as $column => $direction)
		{
			if (is_string($column))
			{
				$column = trim($column);

				if ($column === '') continue;
			}

			if ( ! empty($direction) AND preg_match('/^(?:ASC|DESC|NULL|RAND\(\))$/i', $direction))
			{
				$direction = strtoupper($direction);
			}

			$this->order_by[] = array($column, $direction);
		}

		return $this;
	}

	public function group_by($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns);
		}

		foreach ($columns as $column)
		{
			if (is_string($column))
			{
				$column = trim($column);

				if ($column === '') continue;
			}

			$this->group_by[] = $column;
		}

		return $this;
	}

	public function limit($limit, $offset = NULL)
	{
		$this->limit = (int) $limit;

		if ( ! empty($offset))
		{
			$this->offset($offset);
		}

		return $this;
	}

	public function offset($value)
	{
		$this->offset = (int) $value;

		return $this;
	}

	public function build($group = 'default')
	{
		if (is_string($group)) // group name was passed
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

	}

	public function __toString()
	{
		return $this->build();
	}
}