<?php defined('SYSPATH') or die('No direct script access.');

class ORM_Core {

	// Database field caching
	protected static $fields = array();

	// Database instance
	protected static $db;

	// This table
	protected $class;
	protected $table;

	// This object
	protected $object;

	// Changed object keys
	protected $changed = array();

	// Relationships
	protected $has_one = array();
	protected $has_many = array();
	protected $belongs_to = array();
	protected $belongs_to_many = array();
	protected $has_and_belongs_to_many = array();

	/**
	 * Constructor
	 */
	public function __construct($id = FALSE)
	{
		if (self::$db === NULL)
		{
			// Load database, if not already loaded
			isset(Kohana::instance()->db) or Kohana::instance()->load->database();

			// Insert db into this object
			self::$db = Kohana::instance()->db;
		}

		// Dinfine ALL
		defined('ALL') or define('ALL', 100);

		// Fetch table name
		$this->class = strtolower(substr(get_class($this), 0, -6));
		$this->table = inflector::plural($this->class);

		if (empty(self::$fields[$this->table]))
		{
			foreach(self::$db->list_fields($this->table) as $field)
			{
				// Cache the column names
				self::$fields[$this->table][$field] = $field;
			}
		}

		if (is_object($id))
		{
			// Preloaded object
			$this->object = $id;
		}
		else
		{
			// Load the object
			$this->find($id);
		}
	}

	/**
	 * Magic __get method
	 */
	public function __get($key)
	{
		if (isset($this->object->$key))
		{
			return $this->object->$key;
		}
	}

	/**
	 * Magic __set method
	 */
	public function __set($key, $value)
	{
		if (isset($this->object->$key))
		{
			if ($key != 'id' AND $this->object->$key !== $value)
			{
				// Set new value
				$this->object->$key = $value;

				// Data has changed
				$this->changed[$key] = $key;
			}
		}
	}

	/**
	 * Magic __call method
	 */
	public function __call($method, $args)
	{
		// Return user data as an array
		if ($method === 'as_array')
			return (array) $this->object;

		if ($method === 'find_all')
		{
			// Return an array of all objects
			return $this->find(count($args) ? current($args) : FALSE, ALL);
		}
		elseif (substr($method, 0, 8) === 'find_by_')
		{
			$key = substr($method, 8);
			$val = count($args) ? current($args) : FALSE;

			// Find a single result
			return $this->find(array($key => $val));
		}
		elseif (substr($method, 0, 12) === 'find_all_by_')
		{
			$key = substr($method, 12);
			$val = count($args) ? current($args) : FALSE;

			// Find a all results
			return $this->find(array($key => $val), ALL);
		}
		elseif (substr($method, 0, 13) === 'find_related_')
		{
			// Get table name
			$table = substr($method, 13);

			// Find the model suffix
			preg_match('/_[a-zA-Z]+$/', get_class($this), $suffix);

			// Construct a new model
			$model = ucfirst(inflector::singular($table)).current($suffix);
			$model = new $model();

			// Execute joins
			$this->related_join($table);

			return $model->find_all();
		}
		elseif (substr($method, 0, 4) === 'add_')
		{
			
		}
	}

	public function add($data)
	{
		
	}

	/**
	 * Find and load object data.
	 */
	public function find($where = FALSE, $limit = 1)
	{
		if ($limit === ALL OR is_array($where) OR $where = $this->where($where))
		{
			// Use limit
			($limit === ALL) or self::$db->limit($limit);

			// Use where
			empty($where) or self::$db->where($where);

			$query = self::$db
				->select($this->table.'.*')
				->from($this->table)
				->get();

			if ($limit > 1)
			{
				$model = get_class($this);

				// Construct an array of objects
				$objects = array();
				foreach($query as $result)
				{
					$objects[] = new $model($result);
				}
				return $objects;
			}

			if (count($query) === 1)
			{
				// Fetch the first result
				$this->object = $query->current();
			}
		}

		if (empty($this->object))
		{
			// Create a new object
			$this->object = new StdClass();

			// Fill the fields
			foreach(self::$fields[$this->table] as $field)
			{
				$this->object->$field = '';
			}
		}

		return $this;
	}

	/**
	 * Saves object data.
	 */
	public function save()
	{
		if (empty($this->changed))
			return TRUE;

		$data = array();
		foreach($this->changed as $key)
		{
			// Get changed data
			$data[$key] = $this->object->$key;
		}

		if (empty($this->object->id))
		{
			$query = self::$db
				->insert($this->table, $data);

			if (count($query) === 1)
			{
				// Set current object id by the insert id
				$this->object->id = $query->insert_id();
			}
		}
		else
		{
			$query = self::$db
				->where('id', $this->object->id)
				->update($this->table, $data);
		}

		if (count($query) === 1)
		{
			// Reset changed data
			$this->changed = array();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Fetch object data as an array.
	 */
	public function data_array()
	{
		return (array) $this->object;
	}

	/**
	 * Generate a WHERE array.
	 */
	protected function where($id)
	{
		if (empty($id))
			return FALSE;

		if (is_array($id))
			return $id;

		if (ctype_digit((string) $id))
			return array('id' => $id);
	}

	/**
	 * Execute a join to a table
	 */
	protected function related_join($table)
	{
		// If this object owns the child object, the table is THIS_THAT, otherwise THAT_THIS
		$join_table = in_array($table, $this->has_and_belongs_to_many) ? $this->table.'_'.$table : $table.'_'.$this->table;

		// Primary and foreign keys
		$primary = $this->class.'_id';
		$foreign = inflector::singular($table).'_id';

		// Execute the join
		self::$db
			->where("$join_table.$primary", $this->object->id)
			->join($join_table, "$join_table.$foreign = $table.id");
	}

} // End ORM