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

	protected $where      = array();
	protected $from       = array();
	protected $join       = array();
	protected $where      = array();
	protected $orderby    = array();
	protected $order      = array();
	protected $groupby    = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;
	protected $drivers    = array();

	public function __construct($sql = '*')
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach($sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			if (strpos($val, '(') === FALSE AND $val !== '*')
			{
				if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches))
				{
					$val            = $matches[1];
					$this->distinct = TRUE;
				}
			}

			$this->select[] = $val;
		}
	}

	public function from($sql)
	{
		foreach((array) $sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			$this->from[] = $val;
		}

		return $this;
	}

	public function where($key, $value = NULL)
	{
		$keys  = is_array($key) ? $key : array($key => $value);

		$where = new Database_Where();
		$this->where[] = $where->where($keys);

		return $this;
	}

	public function orwhere($key, $value = NULL)
	{
		$keys  = is_array($key) ? $key : array($key => $value);

		$where = new Database_Where();
		$this->where[] = $where->orwhere($keys);

		return $this;
	}

	public function orderby($orderby, $direction = '')
	{
		$direction = strtoupper(trim($direction));

		if ($direction != '')
		{
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()', 'NULL'))) ? ' '.$direction : ' ASC';
		}

		if (empty($orderby))
		{
			$this->orderby[] = $direction;
			return $this;
		}

		if ( ! is_array($orderby))
		{
			$orderby = explode(',', (string) $orderby);
		}

		$order = array();
		foreach ($orderby as $field)
		{
			$field = trim($field);

			if ($field != '')
			{
				$order[] = $field;
			}
		}
		$this->orderby[] = implode(',', $order).$direction;
		return $this;
	}

	public function limit($limit, $offset = FALSE)
	{
		$this->limit  = (int) $limit;

		if ($offset)
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