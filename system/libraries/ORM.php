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
	 * Constructor.
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
	 * Magic __get method.
	 */
	public function __get($key)
	{
		if (isset($this->object->$key))
		{
			return $this->object->$key;
		}
		elseif ($key === 'table_name')
		{
			return $this->table;
		}
		elseif ($key === 'class_name')
		{
			return $this->class;
		}
	}

	/**
	 * Magic __set method.
	 */
	public function __set($key, $value)
	{
		if ($key != 'id' AND isset($this->object->$key))
		{
			if ($this->object->$key !== $value)
			{
				// Set new value
				$this->object->$key = $value;

				// Data has changed
				$this->changed[$key] = $key;
			}
		}
	}

	/**
	 * Magic __call method.
	 */
	public function __call($method, $args)
	{
		if ($method === 'as_array')
		{
			// Return object data as an array
			return (array) $this->object;
		}

		if ($method === 'find_all')
		{
			// Return an array of all objects
			return $this->find(count($args) ? current($args) : FALSE, ALL);
		}

		if (substr($method, 0, 8) === 'find_by_')
		{
			$key = substr($method, 8);
			$val = count($args) ? current($args) : FALSE;

			// Find a single result
			return $this->find(array($key => $val));
		}

		if (substr($method, 0, 12) === 'find_all_by_')
		{
			$key = substr($method, 12);
			$val = count($args) ? current($args) : FALSE;

			// Find a all results
			return $this->find(array($key => $val), ALL);
		}

		if (substr($method, 0, 13) === 'find_related_')
		{
			// Get table name
			$table = substr($method, 13);

			// Construct a new model
			$model = $this->load_model($table);

			if (in_array($table, $this->has_and_belongs_to_many))
			{
				// Execute joins for many<>many
				$this->related_join($table);
			}
			else
			{
				// Add this object id to WHERE
				self::$db->where($this->class.'_id', $this->object->id);
			}

			return $model->find_all();
		}

		if (preg_match('/^(has|add|remove)_/', $method, $action))
		{
			// Action is always the first match
			$action = $action[1];

			// Get table name
			$table = substr($method, strlen($action) + 1);

			// Get added data
			$data = count($args) ? current($args) : FALSE;

			// Load a new model
			$model = is_object($data) ? $data : $this->load_model($table);

			if (is_array($data) AND $action === 'add')
			{
				foreach($data as $key => $val)
				{
					// Set new object data
					$model->$key = $val;
				}
			}
			else
			{
				// Load model data
				$model->find(($data === $model) ? FALSE : $data);
			}

			// Use model table name, instead of guessing with inflector
			$table = $model->table_name;

			// Set primary and foreign keys
			$primary = $this->class.'_id';
			$foreign = $model->class_name.'_id';

			if (in_array($table, $this->has_one) OR in_array($table, $this->has_many))
			{
				// Set the primary key
				$model->$primary = $this->object->id;
			}
			elseif (in_array($table, $this->has_and_belongs_to_many))
			{
				// Many-to-many relationship
				$relationship = array
				(
					$primary => $this->object->id,
					$foreign => $model->id
				);
			}
			else
			{
				// This model does not have ownership
				return FALSE;
			}

			switch($action)
			{
				case 'add':
					if (isset($relationship))
					{
						// Save the relationship
						self::$db->insert($this->related_table($table), $relationship);
					}

					return $model->save();
				break;
				case 'has':
					if (isset($relationship))
					{
						// Find if the relationship exists, in the case of many<>many
						return (bool) count(self::$db
							->select($primary)
							->from($this->related_table(inflector::plural($table)))
							->where($relationship)
							->limit(1)
							->get());
					}

					// Return TRUE if the primary key of the model matches this objects id
					return ($model->$primary === $this->object->id);
				break;
				case 'remove':
					if (isset($relationship))
					{
						// Attempt to delete the many<>many relationship
						return (bool) count(self::$db
							->where($relationship)
							->delete($this->related_table($table)));
					}

					if (in_array($table, $this->has_one) OR in_array($table, $this->has_many))
					{
						// Double check that the model has the same key as this object
						return ($model->$primary === $this->object->id) ? $model->delete() : FALSE;
					}
				break;
			}

			return FALSE;
		}
	}

	/**
	 * Find and load this object data.
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

		// Reset changed
		$this->changed = array();

		// Return true if something was actually loaded
		return ($this->object->id != 0);
	}

	/**
	 * Saves this object data.
	 */
	public function save()
	{
		// No data was changed
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
			$query = self::$db->insert($this->table, $data);

			if (count($query) === 1)
			{
				// Set current object id by the insert id
				$this->object->id = $query->insert_id();
			}
		}
		else
		{
			$query = self::$db
				->set($data)
				->where('id', $this->object->id)
				->update($this->table);
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
	 * Deletes this object.
	 */
	public function delete()
	{
		// Can't delete something that does not exist
		if (empty($this->object->id))
			return FALSE;

		// Delete this object
		$query = self::$db
			->where('id', $this->object->id)
			->delete($this->table);

		if (count($query))
		{
			// Reset the object
			$this->object = NULL;
			$this->find(FALSE);

			return TRUE;
		}

		return FALSE;
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
	 * Creates a model from a table name.
	 */
	protected function load_model($table)
	{
		// Get model name
		$model= ucfirst(inflector::singular($table)).'_Model';

		// Create a new model
		return new $model();
	}

	/**
	 * Finds the many<>many relationship table.
	 */
	protected function related_table($table)
	{
		if (in_array($table, $this->has_and_belongs_to_many))
		{
			return $this->table.'_'.$table;
		}
		elseif (in_array($table, $this->belongs_to_many))
		{
			return $table.'_'.$this->table;
		}
		else
		{
			return $table;
		}
	}

	/**
	 * Execute a join to a table.
	 */
	protected function related_join($table)
	{
		$join_table = $this->related_table($table);

		// Primary and foreign keys
		$primary = $this->class.'_id';
		$foreign = inflector::singular($table).'_id';

		// Execute the join
		self::$db
			->where("$join_table.$primary", $this->object->id)
			->join($join_table, "$join_table.$foreign = $table.id");
	}

} // End ORM