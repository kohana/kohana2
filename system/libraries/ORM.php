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

	// Automatic saving on model destruction
	protected $auto_save = FALSE;

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
			foreach(self::$db->list_fields($this->table) as $field => $data)
			{
				// Cache the column names
				self::$fields[$this->table][$field] = $data;
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
				$this->clear();
			}
			else
			{
				// Query and load object
				$this->find($id);
			}
		}
	}

	/**
	 * Enables automatic saving of the object when the model is destroyed.
	 */
	public function __destruct()
	{
		if ($this->auto_save == TRUE)
		{
			// Automatically save the model
			$this->save();
		}
	}

	/**
	 * Magic method for getting object and model keys.
	 */
	public function __get($key)
	{
		if (isset($this->object->$key))
		{
			return $this->object->$key;
		}
		else
		{
			switch($key)
			{
				case 'table_name':
					return $this->table;
				break;
				case 'class_name':
					return $this->class;
				break;
				case 'auto_save':
					return $this->auto_save;
				break;
			}
		}
	}

	/**
	 * Magic method for setting object and model keys.
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
		else
		{
			switch($key)
			{
				case 'auto_save':
					$this->auto_save = (bool) $value;
				break;
			}
		}
	}

	/**
	 * Magic method for calling ORM methods. This handles:
	 *  - as_array
	 *  - find_by_*
	 *  - find_all_by_*
	 *  - find_related_*
	 *  - has_*
	 *  - add_*
	 *  - remove_*
	 */
	public function __call($method, $args)
	{
		if ($method === 'as_array')
		{
			// Return all of the object data as an array
			return (array) $this->object;
		}

		if (substr($method, 0, 8) === 'find_by_' OR ($all = substr($method, 0, 12)) === 'find_all_by_')
		{
			$method = isset($all) ? substr($method, 12) : substr($method, 8);

			// WHERE is manually set
			$this->where = TRUE;

			if (is_array($keys = $this->find_keys($method)))
			{
				if (strpos($method, '_or_') === FALSE)
				{
					// Use AND WHERE
					self::$db->where(array_combine($keys, $args));
				}
				else
				{
					if (count($args) === 1)
					{
						$val = current($args);
						foreach($keys as $key)
						{
							// Use OR WHERE, with a single value
							self::$db->orwhere(array($key => $val));
						}
					}
					else
					{
						// Use OR WHERE, with multiple values
						self::$db->orwhere(array_combine($keys, $args));
					}
				}
			}
			else
			{
				// Set WHERE
				self::$db->where(array($keys => current($args)));
			}

			// Find requested objects
			return isset($all) ? $this->find_all() : $this->find();
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
				$model->where($remote);
			}
			elseif (in_array($table, $this->has_and_belongs_to_many))
			{
				// Find many<>many relationships, via a JOIN
				$this->related_join($table);
			}

			return $model->find_all();
		}

		if (preg_match('/^(has|add|remove)_(.+)/', $method, $matches))
		{
			$action = $matches[1];
			$model  = is_object(current($args)) ? current($args) : $this->load_model($matches[2]);

			// Real foreign table name
			$table = $model->table_name;

			// Sanity check, make sure that this object has ownership
			if (in_array($matches[2], $this->has_one))
			{
				$ownership = 1;
			}
			elseif (in_array($table, $this->has_many))
			{
				$ownership = 2;
			}
			elseif (in_array($table, $this->has_and_belongs_to_many))
			{
				$ownership = 3;
			}
			else
			{
				// Model does not have ownership, abort now
				return FALSE;
			}

			// Primary key related to this object
			$primary = $this->class.'_id';

			// Related foreign key
			$foreign = $model->class_name.'_id';

			if ( ! is_object(current($args)))
			{
				if ($action === 'add' AND is_array(current($args)))
				{
					foreach(current($args) as $key => $val)
					{
						// Fill object with data from array
						$model->$key = $val;
					}
				}
				else
				{
					if ($ownership === 1 OR $ownership === 2)
					{
						// Make sure the related key matches this object id
						self::$db->where($primary, $this->object->id);
					}

					// Load the related object
					$model->find(current($args));
				}
			}

			if ($ownership === 3)
			{
				// The many<>many relationship, via a joining table
				$relationship = array
				(
					$primary => $this->object->id,
					$foreign => $model->id
				);
			}

			switch($action)
			{
				case 'add':
					if (isset($relationship))
					{
						// Insert for many<>many relationship
						self::$db->insert($this->related_table($table), $relationship);
					}
					else
					{
						// Set the related key to this object id
						$model->$primary = $this->object->id;
					}

					return $model->save();
				break;
				case 'has':
					if (isset($relationship))
					{
						// Find the many<>many relationship
						return (bool) count
						(
							self::$db
							->select($primary)
							->from($this->related_table($table))
							->where($relationship)
							->limit(1)
							->get()
						);
					}

					return ($model->$primary === $this->object->id);
				break;
				case 'remove':
					if (isset($relationship))
					{
						// Attempt to delete the many<>many relationship
						return (bool) count(self::$db->delete($this->related_table($table), $relationship));
					}
					elseif ($model->$primary === $this->object->id)
					{
						// Delete the related object
						return $model->delete();
					}
					else
					{
						// Massive failure
						return FALSE;
					}
				break;
			}

			// This should never be executed
			return FALSE;
		}

		if (method_exists(self::$db, $method))
		{
			// Do not allow query methods
			if (preg_match('/query|get|list_fields|field_data/', $method))
				return $this;

			if ($method === 'select')
			{
				$this->select = TRUE;
			}
			elseif (preg_match('/like|regex/', $method))
			{
				$this->where = TRUE;
			}

			// Pass through to Database, manually calling up to 2 args, for speed.
			switch(count($args))
			{
				case 1:
					self::$db->$method(current($args));
				break;
				case 2:
					self::$db->$method(current($args), next($args));
				break;
				default:
					call_user_func_array(array(self::$db, $method), $args);
				break;
			}

			return $this;
		}
	}

	/**
	 * Finds the key for a WHERE statement. Usually this should be overloaded
	 * in the model, if you want to do: new Foo_Model('name') or similar.
	 */
	protected function where_key($id = NULL)
	{
		return 'id';
	}

	/**
	 * Find and load data for this object.
	 *
	 * Returns:
	 *  $this object reference.
	 */
	public function find($id = FALSE)
	{
		// Generate WHERE
		$this->where or self::$db->where($this->where_key($id), $id);

		// Only one result will be returned
		self::$db->limit(1);

		// Load the result of the query
		return $this->load_result(FALSE);
	}

	/**
	 * Find and load an array of objects.
	 *
	 * Returns:
	 *  An array of objects.
	 */
	public function find_all()
	{
		// Return an array of objects
		return $this->load_result(TRUE);
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

		if ($this->object->id == '')
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
			// Perform an update
			$query = self::$db->update($this->table, $data, array('id' => $this->object->id));
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
		$query = self::$db->delete($this->table, $this->where);

		// Reset this object
		$this->clear();

		// Will return TRUE if anything was deleted
		return (count($query) > 0);
	}

	/**
	 * Clears the current object by creating an empty object and assigning empty
	 * values to each of the object fields. At the same time, the WHERE and
	 * SELECT statements are cleared and the changed keys are reset.
	 */
	public function clear()
	{
		// Create an empty object
		$this->object = new StdClass();

		// Empty the object
		foreach(self::$fields[$this->table] as $field => $data)
		{
			$this->object->$field = '';
		}

		// Reset object status
		$this->changed = array();
		$this->select  = FALSE;
		$this->where   = FALSE;
	}

	/**
	 * Helper for __call, breaks a string into WHERE keys.
	 */
	protected function find_keys($keys)
	{
		if (strpos($keys, '_or_'))
		{
			$keys = explode('_or_', $keys);
		}
		elseif (strpos($keys, '_and_'))
		{
			$keys = explode('_and_', $keys);
		}

		return $keys;
	}

	/**
	 * Loads a database object result.
	 *
	 * Parameters:
	 *  array  - force the return to be an array
	 *
	 * Return:
	 *  boolean - TRUE for single result, FALSE for an empty result
	 *  array   - Multiple row result set
	 */
	protected function load_result($array = FALSE)
	{
		// Make sure there is something to select
		($this->select == FALSE) and self::$db->select($this->table.'.*');

		// Fetch the query result
		$result = self::$db
			->from($this->table)
			->get()
			->result(TRUE);

		if (count($result) > 0)
		{
			if (count($result) > 1 OR $array == TRUE)
			{
				// Model class name
				$class = get_class($this);

				$array = array();
				foreach($result->result_array() as $row)
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
			if ($array == TRUE)
			{
				// Return an empty array when an array is requested
				return array();
			}

			// Reset the object
			$this->clear();
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
	 * Execute a join to a table.
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