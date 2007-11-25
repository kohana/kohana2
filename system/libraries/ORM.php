<?php defined('SYSPATH') or die('No direct script access.');
 /**
 * Class: ORM
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class ORM_Core {

	// Database field caching
	protected static $fields = array();

	// Database instance
	protected static $db;

	// This table
	protected $class;
	protected $table;

	// SQL building status
	protected $select = FALSE;
	protected $where = FALSE;

	// Currently loaded object
	protected $object;

	// Changed object keys
	protected $changed = array();

	// Object Relationships
	protected $has_one = array();
	protected $has_many = array();
	protected $belongs_to = array();
	protected $belongs_to_many = array();
	protected $has_and_belongs_to_many = array();

	/**
	 * Constructor: __construct
	 *  Initialize database, setup internal variables.
	 */
	public function __construct($id = FALSE)
	{
		if (self::$db === NULL)
		{
			// Load database, if not already loaded
			isset(Kohana::instance()->db) or Kohana::instance()->load->database();

			// Insert db into this object
			self::$db = Kohana::instance()->db;

			// Define ALL
			defined('ALL') or define('ALL', -1);
		}

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
			if (empty($id))
			{
				// Load an empty object
				$this->load_result(FALSE);
			}
			else
			{
				// Query and load object
				$this->where($id)->find();
			}
		}
	}

	/**
	 * Method: __get
	 *  Magic method for getting data.
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
	 * Method: __set
	 *  Magic method for setting data.
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
	 * Method: __call
	 *  Magic method for calling dynamic methods.
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
			return $this->find(ALL);
		}

		if (substr($method, 0, 8) === 'find_by_')
		{
			$key = substr($method, 8);
			$val = count($args) ? current($args) : FALSE;

			// Find via the requested key
			return $this->where(array($key => $val))->find();
		}

		if (substr($method, 0, 12) === 'find_all_by_')
		{
			$key = substr($method, 12);
			$val = count($args) ? current($args) : FALSE;

			// Find all results matching a requested key
			return $this->where(array($key => $val))->find(ALL);
		}

		if (substr($method, 0, 13) === 'find_related_')
		{
			// Get table name
			$table = substr($method, 13);

			// Construct a new model
			$model = $this->load_model($table);

			// Remote reference to this object
			$remote = array($this->class.'_id' => $this->object->id);

			if (in_array($table, $this->has_one))
			{
				// Find one<>one relationships
				return $model->where($remote)->find();
			}
			elseif (in_array($table, $this->has_many))
			{
				// Find one<>many relationships
				$this->where($remote);
			}
			elseif (in_array($table, $this->has_and_belongs_to_many))
			{
				// Find many<>many relationships, via a JOIN
				$this->related_join($table);
			}

			return $model->find(ALL);
		}

		if (preg_match('/^(has|add|remove)_/', $method, $action))
		{
			// Action is always the first match
			$action = $action[1];

			// Get table name
			$table = substr($method, strlen($action) + 1);

			// Get added data
			$data = count($args) ? current($args) : FALSE;

			if (is_array($data) AND $action === 'add')
			{
				// Load the model by table name
				$model = $this->load_model($table);

				foreach($data as $key => $val)
				{
					// Set new object data
					$model->$key = $val;
				}
			}
			else
			{
				if (is_object($data))
				{
					// Assign the model to the data
					$model = $data;
				}

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
	 * Generates a SELECT statement for Database.
	 */
	public function select()
	{
		$count = func_num_args();

		if ($count === 0)
		{
			$this->select = $this->table.'.*');
		}
		else
		{
			$this->select = ($count === 1) ? func_get_arg(0) : func_get_args();
		}

		return $this;
	}

	/**
	 * Generate a WHERE array.
	 */
	public function where()
	{
		switch(func_num_args())
		{
			case 0:
				if ($this->object->id > 0)
				{
					self::$db->where('id', $this->object->id);

					// WHERE has been set
					$this->where = TRUE;
				}
			break;
			case 1:
				$id = func_get_arg(0);
				if ( ! empty($id))
				{
					self::$db->where(is_array($id) ? $id : array('id' => $id));

					// WHERE has been set
					$this->where = TRUE;
				}
			break;
			case 2:
				$key = func_get_arg(0);
				$val = func_get_arg(1);

				if (is_array($key))
				{
					// Choose the OR method to use
					$or = (strpos($val, '%') === FALSE) ? 'orwhere' : 'orlike';

					foreach ($key as $k)
					{
						// Use OR WHERE/LIKE
						self::$db->$or($k, $val);
					}
				}
				else
				{
					self::$db->where(array($key => $val));
				}

				// WHERE has been set
				$this->where = TRUE;
			break;
		}

		return $this;
	}

	/**
	 * Method: find
	 *  Find and load this object data.
	 *
	 * Parameters:
	 *  where - database where clause or array of clauses
	 *  limit - maximum number of returned objects
	 *
	 * Returns:
	 *  TRUE or FALSE
	 *  Array of objects if where is an array
	 */
	public function find($limit = 1, $offset = FALSE)
	{
		// SELECT
		($this->select == FALSE) and $this->select();
		// WHERE
		($this->where == FALSE) and $this->where();
		// LIMIT
		($limit !== ALL) and self::$db->limit($limit, $offset);

		// Return an array if the limit is ALL or greater than 1
		$array = ($limit === ALL OR $limit > 1) ? TRUE : FALSE;

		// Load the result of the query
		return $this->load_result(self::$db->from($this->table)->get(), $array);
	}

	public function find_all($where = FALSE)
	{
		// Do the select
		empty($this->select) or $this->select();

		// SELECT
		self::$db->select($this->select);
		// WHERE
		empty($where) or self::$db->where($where);

		return $this->load_result(self::$db->from($this->table)->get(), TRUE);
	}

	public function find_all_by($key, $val)
	{
		// SELECT
		($this->select == FALSE)
		// WHERE
		self::$db->where($key, $val);

		
	}

	/**
	 * Method: save
	 *  Saves this object data.
	 *
	 * Returns:
	 *  TRUE or FALSE
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
			// Perform an insert
			$query = self::$db->insert($this->table, $data);

			if (count($query) === 1)
			{
				// Set current object id by the insert id
				$this->object->id = $query->insert_id();
			}
		}
		else
		{
			// WHERE is this object
			$this->where();

			// Perform an update
			$query = self::$db->update($this->table, $data);
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
	 * Method: delete
	 *  Deletes this object.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function delete()
	{
		// Can't delete something that does not exist
		if (empty($this->object->id))
			return FALSE;

		// Where is this object
		$this->where();

		// Delete this object
		$query = self::$db->delete($this->table);

		if (count($query) > 0)
		{
			// Reset the object
			$this->load_result(FALSE);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Loads a database object result.
	 *
	 * Parameters:
	 *  result - database result object
	 *  array  - force the return to be an array
	 *
	 * Return:
	 *  boolean - TRUE for single result, FALSE for an empty result
	 *  array   - Multiple row result set
	 */
	protected function load_result($result, $array = FALSE)
	{
		if ($result != FALSE AND count($result) > 0)
		{
			if (count($result) > 1 OR $array == TRUE)
			{
				// Model class name
				$class = get_class($this);

				$array = array();
				foreach($result as $row)
				{
					// Add object to the array
					$array[] = new $class($row);
				}

				// Return an array of all the objects
				return $array;
			}
			else
			{
				// Fetch the first result
				$this->object = $result->current();
			}
		}
		else
		{
			// Create an empty object
			$this->object = new StdClass();

			// Empty the object
			foreach(self::$fields[$this->table] as $field)
			{
				$this->object->$field = '';
			}
		}

		// Clear the changed keys, a new object has been loaded
		$this->changed = array();
		$this->select = FALSE;
		$this->where = FALSE;

		// Return this object
		return $this;
	}

	/**
	 * Method: load_model
	 *  Creates a model from a table name.
	 *
	 * Parameters:
	 *  table - table name
	 *
	 * Returns:
	 *  Instance of model
	 */
	protected function load_model($table)
	{
		// Get model name
		$model= ucfirst(inflector::singular($table)).'_Model';

		// Create a new model
		return new $model();
	}

	/**
	 * Method: related_table
	 *  Finds the many<>many relationship table.
	 *
	 * Parameters:
	 *  table - table name
	 *
	 * Returns:
	 *  Table name
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
	 * Method: related_join
	 *  Execute a join to a table.
	 *
	 * Parameters:
	 *  table - table name
	 */
	protected function related_join($table)
	{
		$join_table = $this->related_table($table);

		// Primary and foreign keys
		$primary = $this->class.'_id';
		$foreign = inflector::singular($table).'_id';

		// Where has been set
		$this->where = TRUE;

		// Execute the join
		self::$db
			->where("$join_table.$primary", $this->object->id)
			->join($join_table, "$join_table.$foreign = $table.id");
	}

} // End ORM