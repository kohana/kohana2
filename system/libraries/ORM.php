<?php defined('SYSPATH') or die('No direct script access.');
 /**
 * Object Relational Mapping (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as a model.
 *
 * @see http://en.wikipedia.org/wiki/Active_record
 * @see http://en.wikipedia.org/wiki/Object-relational_mapping
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
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
	protected $from = FALSE;
	protected $order = FALSE;

	// Default sorting
	protected $sort = array('id', 'asc');

	// Currently loaded object
	protected $object;
	protected $object_related;
	protected $loaded = FALSE;
	protected $saved = FALSE;

	// Changed object keys
	protected $changed = array();
	protected $changed_related = array();

	// Object Relationships
	protected $has_one = array();
	protected $has_many = array();
	protected $belongs_to = array();
	protected $belongs_to_many = array();
	protected $has_and_belongs_to_many = array();

	/**
	 * Factory method. Creates an instance of an ORM model and returns it.
	 *
	 * @param   string   model name
	 * @param   mixed    id to load
	 * @return  object
	 */
	public static function factory($model = FALSE, $id = FALSE)
	{
		$model = empty($model) ? __CLASS__ : ucfirst($model).'_Model';
		return new $model($id);
	}

	/**
	 * Initialize database, setup internal variables, find requested object.
	 *
	 * @return  void
	 */
	public function __construct($id = FALSE)
	{
		// Fetch table name
		empty($this->class) and $this->class = strtolower(substr(get_class($this), 0, -6));
		empty($this->table) and $this->table = inflector::plural($this->class);

		// Connect to the database
		$this->connect();

		if (is_object($id))
		{
			// Preloaded object
			$this->object = $id;

			// Convert the value to the correct type
			$this->load_object_types();

			// Object is loaded
			$this->loaded = $this->saved = TRUE;
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
	 *
	 * @return  void
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
	 * Reloads the database when the object is unserialized.
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		// Connect to the database
		$this->connect();
	}

	/**
	 * Magic method for getting object and model keys.
	 *
	 * @param   string  key name
	 * @return  mixed
	 */
	public function __get($key)
	{
		if (isset($this->object->$key))
		{
			return $this->object->$key;
		}
		elseif (in_array($key, $this->has_one) OR in_array($key, $this->belongs_to))
		{
			// Set the model name
			$model = ucfirst($key).'_Model';

			// Set the child id name
			$child_id = $key.'_id';

			$this->object->$key = new $model
			(
				isset($this->object->$child_id)
				// Get the foreign object using the key defined in this object
				? $this->object->$child_id
				// Get the foreign object using the primary key of this object
				: array($this->class.'_id' => $this->object->id)
			);

			// Return the model
			return $this->object->$key;
		}
		elseif (in_array($key, $this->has_and_belongs_to_many))
		{
			if (empty($this->object_related[$key]['cur']))
			{
				// Foreign key
				$fk = inflector::singular($key).'_id';

				// Query to find relationships
				$query = self::$db
					->select($fk)
					->from($this->related_table($key))
					->where($this->class.'_id', $this->id)
					->get();

				foreach ($query as $row)
				{
					// Set relationships
					$this->object_related[$key]['cur'][] = $row->$fk;
				}
			}

			// Return the current relationships
			return $this->object_related[$key]['cur'];
		}
		else
		{
			switch ($key)
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
	 *
	 * @param   string  key name
	 * @param   mixed   value to set
	 * @return  void
	 */
	public function __set($key, $value)
	{
		if ($key != 'id' AND isset(self::$fields[$this->table][$key]))
		{
			// Force the value to the correct type
			$value = $this->set_value_type($key, $value);

			if ($this->object->$key !== $value)
			{
				// Set new value
				$this->object->$key = $value;

				// Data has changed
				$this->changed[$key] = $key;

				// Data has been changed
				$this->saved = FALSE;
			}
		}
		elseif (in_array($key, $this->has_and_belongs_to_many) AND is_array($value))
		{
			if (empty($this->object_related[$key]))
			{
				// Force a load of the relationship
				$this->$key;
			}

			// Set the new relationships
			$this->object_related[$key]['new'] = $value;

			// Relationships changed
			$this->changed_related[$key] = $key;
		}
		else
		{
			switch ($key)
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
	 *
	 * @throws  Kohana_Exception
	 * @param   string  method name
	 * @param   array   method arguments
	 * @return  mixed
	 */
	public function __call($method, $args)
	{
		if ($method === 'as_array')
		{
			// Return all of the object data as an array
			return (array) $this->object;
		}

		if (substr($method, 0, 8) === 'find_by_' OR substr($method, 0, 12) === 'find_all_by_')
		{
			// Make a find_by call
			return $this->call_find_by($method, $args);
		}

		if (substr($method, 0, 13) === 'find_related_')
		{
			// Make a find_related call
			return $this->call_find_related($method, $args);
		}

		if (preg_match('/^(has|add|remove)_(.+)/', $method, $matches))
		{
			if (empty($this->object->id))
			{
				// many<>many relationships only work when the object has been saved
				return FALSE;
			}

			// Make a has/add/remove call
			return $this->call_has_add_remove($method, $args, $matches);
		}

		if (method_exists(self::$db, $method))
		{
			// Do not allow query methods
			if (preg_match('/query|get|insert|update|list_fields|field_data/', $method))
				return $this;

			if ($method === 'select')
			{
				$this->select = TRUE;
			}
			elseif (preg_match('/where|like|in|regex/', $method))
			{
				$this->where = TRUE;
			}
			elseif ($method === 'from')
			{
				$this->from = TRUE;
			}
			elseif ($method === 'orderby')
			{
				$this->order = TRUE;
			}

			// Pass through to Database, manually calling up to 2 args, for speed.
			switch (count($args))
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

		// Throw an exception to warn the user
		throw new Kohana_Exception('orm.method_not_implemented', $method, get_class($this));
	}

	/**
	 * __call: find_by_*, find_all_by_*
	 *
	 * @param   string  method
	 * @param   array   arguments
	 * @return  object
	 */
	protected function call_find_by($method, $args)
	{
		// Use ALL
		$ALL = (substr($method, 0, 12) === 'find_all_by_');

		// Method args
		$method = $ALL ? substr($method, 12) : substr($method, 8);

		// WHERE is manually set
		$this->where = TRUE;

		// split method name into $keys array by "_and_" or "_or_"
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
					foreach ($keys as $key)
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

		if ($ALL)
		{
			// Array of results
			return $this->load_result(TRUE);
		}
		else
		{
			// Allow chains
			return $this->find();
		}
	}

	/**
	 * __call: find_related_*
	 *
	 * @param   string   method name
	 * @param   array    arguments
	 * @return  object
	 */
	protected function call_find_related($method, $args)
	{
		// Extract table name
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
		elseif (in_array($table, $this->belongs_to_many))
		{
			// Use the foreign column name to check the relationship
			$id = $this->class.'_id';

			if ($model->$id === NULL)
			{
				// Find many<>many relationships, via a JOIN
				$this->related_join($table);
			}
			else
			{
				// Find one<>many relationships
				$model->where($remote);
			}
		}
		else
		{
			// This table does not have ownership
			return FALSE;
		}

		return $model->load_result(TRUE);
	}

	/**
	 * __call: has_*, add_*, remove_*
	 *
	 * @param   string   method
	 * @param   array    arguments
	 * @param   array    action matches
	 * @return  boolean
	 */
	protected function call_has_add_remove($method, $args, $matches)
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
				foreach (current($args) as $key => $val)
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
			// Save the model before finishing the action
			$model->save();

			// The many<>many relationship, via a joining table
			$relationship = array
			(
				$primary => $this->object->id,
				$foreign => $model->id
			);
		}

		switch ($action)
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
					return (bool) self::$db
						->select($primary)
						->from($this->related_table($table))
						->where($relationship)
						->limit(1)
						->get()->count();
				}

				return ($model->$primary === $this->object->id);
			break;
			case 'remove':
				if (isset($relationship))
				{
					// Attempt to delete the many<>many relationship
					return (bool) self::$db->delete($this->related_table($table), $relationship)->count();
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

	/**
	 * Finds the key for a WHERE statement. Usually this should be overloaded
	 * in the model, if you want to do: new Foo_Model('name') or similar.
	 *
	 * @return  string  name of key for the id
	 */
	protected function where_key($id = NULL)
	{
		return $this->table.'.id';
	}

	/**
	 * Test if an object is loaded.
	 *
	 * @return  boolean
	 */
	public function loaded()
	{
		return $this->loaded;
	}

	/**
	 * Test if an object is saved.
	 *
	 * @return  boolean
	 */
	public function saved()
	{
		return $this->saved;
	}

	/**
	 * Find and load data for the current object.
	 *
	 * @param   string   id of the object to find, an array, or TRUE to reload
	 * @param   boolean  return the result, or load it into the current object
	 * @return  object   object instance
	 */
	public function find($id = FALSE, $return = FALSE)
	{
		// To allow the object to be reloaded
		($id === TRUE) and $id = $this->id;

		// Generate WHERE
		if ($this->where === FALSE AND ! empty($id))
		{
			if (is_array($id))
			{
				self::$db->where($id);
			}
			else
			{
				self::$db->where($this->where_key($id), $id);
			}
		}

		// Only one result will be returned
		self::$db->limit(1);

		// Load the result of the query
		return $this->load_result(FALSE, $return);
	}

	/**
	 * Find and load an array of objects.
	 *
	 * @param   integer  SQL limit
	 * @param   integer  SQL offset
	 * @return  array    all objects in a simple array
	 */
	public function find_all($limit = NULL, $offset = 0)
	{
		if ($limit !== NULL)
		{
			// Add LIMIT
			self::$db->limit($limit, $offset);
		}

		// Return an array of objects
		return $this->load_result(TRUE, TRUE);
	}

	/**
	 * Count the number of records in the table.
	 *
	 * @return  integer
	 */
	public function count_all()
	{
		// Return the total number of records in a table
		return self::$db->count_records($this->table);
	}

	/**
	 * Count the number of records in the last query, without LIMIT or OFFSET applied.
	 *
	 * @return  integer
	 */
	public function count_last_query()
	{
		if ($sql = self::$db->last_query())
		{
			if (stripos($sql, 'LIMIT') !== FALSE)
			{
				// Remove LIMIT from the SQL
				$sql = preg_replace('/\bLIMIT\s+[^a-z]+/i', '', $sql);
			}

			if (stripos($sql, 'OFFSET') !== FALSE)
			{
				// Remove OFFSET from the SQL
				$sql = preg_replace('/\bOFFSET\s+\d+/i', '', $sql);
			}

			// Get the total rows from the last query executed
			$result = self::$db->query('SELECT COUNT(*) AS '.self::$db->escape_column('total_rows').' FROM ('.$sql.') AS '.self::$db->escape_table('counted_results'));

			if ($result->count())
			{
				// Return the total number of rows from the query
				return (int) $result->current()->total_rows;
			}
		}

		return FALSE;
	}

	/**
	 * Creates a key/value array from all of the objects available. Uses find_all
	 * to find the objects.
	 *
	 * @param   string  key column
	 * @param   string  value column
	 * @return  array
	 */
	public function select_list($key, $val)
	{
		// Return a select list from the results
		return $this->select($key, $val)->find_all()->select_list($key, $val);
	}

	/**
	 * Validates the current object. This method should generally be called
	 * via the model, after the $_POST Validation object has been created.
	 *
	 * @param   object    Validation array
	 * @return  boolean
	 */
	public function validate(Validation $array, $save = FALSE)
	{
		if ( ! $array->submitted())
		{
			foreach ($array->safe_array() as $key => $val)
			{
				// Pre-fill data
				$array[$key] = $this->$key;
			}
		}

		// Validate the array
		if ($status = $array->validate())
		{
			foreach ($array->safe_array() as $key => $val)
			{
				// Set new data
				$this->$key = $val;
			}

			if ($save === TRUE OR is_string($save))
			{
				// Save this object
				$this->save();

				if (is_string($save))
				{
					// Redirect to the saved page
					url::redirect($save);
				}
			}
		}

		// Return validation status
		return $status;
	}

	/**
	 * Saves the current object.
	 *
	 * @return  bool
	 */
	public function save()
	{
		// No data was changed
		if (empty($this->changed) AND empty($this->changed_related))
			return TRUE;

		if ( ! empty($this->changed))
		{
			$data = array();
			foreach ($this->changed as $key)
			{
				// Get changed data
				$data[$key] = $this->object->$key;
			}

			if (empty($this->object->id))
			{
				// Perform an insert
				$query = self::$db->insert($this->table, $data);

				if ($query->count())
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

			if ($query->count())
			{
				// Reset changed data
				$this->changed = array();

				// Reset SELECT, WHERE, and FROM
				$this->select = $this->where = $this->from = FALSE;

				// Object has been loaded and saved
				$this->loaded = $this->saved = TRUE;
			}
		}

		if ( ! empty($this->changed_related) AND ($this->saved OR $this->loaded))
		{
			foreach ($this->changed_related as $table)
			{
				// Skip empty rows
				if (empty($this->object_related[$table]))
					continue;

				// Current and new relationships
				$cur = $this->object_related[$table]['cur'];
				$new = $this->object_related[$table]['new'];

				// Foreign and primary keys
				$fk = inflector::singular($table).'_id';
				$pk = $this->class.'_id';

				// Relationship table
				$table = $this->related_table($table);

				if ($delete = array_diff($cur, $new))
				{
					// Delete relationships
					self::$db->in($fk, $delete)->where($pk, $this->id)->delete($table);
				}

				foreach (array_diff($new, $cur) as $key)
				{
					// Create new relationships
					self::$db->insert($table, array($pk => $this->id, $fk => $key));
				}
			}
		}

		return TRUE;
	}

	/**
	 * Deletes this object, or all objects in this table.
	 *
	 * @param   bool  delete all rows in table
	 * @return  bool  FALSE if the object cannot be deleted
	 * @return  int   number of rows deleted
	 */
	public function delete($all = FALSE)
	{
		if ($all === TRUE)
		{
			// WHERE for TRUE: "WHERE 1" (h4x)
			$where = ($this->where === TRUE) ? NULL : TRUE;
		}
		else
		{
			// Can't delete something that does not exist
			if (empty($this->object->id))
				return FALSE;

			if ( ! empty($this->has_and_belongs_to_many))
			{
				// Foreign WHERE for this object
				$where = array($this->class.'_id' => $this->object->id);

				foreach ($this->has_and_belongs_to_many as $table)
				{
					// Delete all many<>many relationships for this object
					self::$db->delete($this->table_name.'_'.$table, $where);
				}
			}

			// WHERE for this object
			$where = array('id' => $this->object->id);
		}

		// Clear this object
		$this->clear();

		// Return the number of rows deleted
		return self::$db->delete($this->table, $where)->count();
	}

	/**
	 * Delete all rows in the table.
	 *
	 * @return  int   number of rows deleted
	 */
	public function delete_all()
	{
		// Proxy to delete(TRUE)
		return $this->delete(TRUE);
	}

	/**
	 * Clears the current object by creating an empty object and assigning empty
	 * values to each of the object fields. At the same time, the WHERE and
	 * SELECT statements are cleared and the changed keys are reset.
	 *
	 * @chainable
	 * @return  void
	 */
	public function clear()
	{
		// Create an empty object
		$this->object = new StdClass;

		// Empty the object
		foreach (self::$fields[$this->table] as $field => $data)
		{
			$this->object->$field = '';
		}

		// Convert the value to the correct type
		$this->load_object_types();

		// Reset object status
		$this->changed = array();
		$this->select = $this->where = $this->from = FALSE;
		$this->loaded = $this->saved = FALSE;

		return $this;
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
	 * @param   boolean        force the return to be an array
	 * @param   boolean        $return
	 * @return  boolean|array  TRUE for single result, FALSE for an empty result, or array of rows
	 */
	protected function load_result($array = FALSE, $return = FALSE)
	{
		// Make sure there is something to select
		$this->select or self::$db->select($this->table.'.*');

		// Make sure there is a table to select from
		$this->from or self::$db->from($this->table);

		// Make sure there is ordering applied
		$this->order or self::$db->orderby($this->sort[0], $this->sort[1]);

		// Fetch the query result
		$result = self::$db->get()->result(TRUE);

		if ($array === TRUE)
		{
			// Create a new ORM iterator of the result
			return new ORM_Iterator(get_class($this), $result);
		}
		else
		{
			if ($return === TRUE)
			{
				// Return the first result
				return ORM::factory($this->class, $result->current());
			}

			if ($result->count() === 1)
			{
				// Load the first result, if there is only one result
				$this->object = $result->current();

				// Convert the value to the correct type
				$this->load_object_types();

				// Object is loaded
				$this->loaded = $this->saved = TRUE;
			}
			else
			{
				// Clear the object, nothing was loaded
				$this->clear();

				$this->loaded = $this->saved = FALSE;
			}
		}

		// Clear the changed keys, a new object has been loaded
		$this->changed = array();
		$this->select = $this->where = $this->from = FALSE;

		// Return this object
		return $this;
	}

	/**
	 * Creates a model from a table name.
	 *
	 * @param   string  table name
	 * @return  object  ORM instance
	 */
	protected function load_model($table)
	{
		// Create and return the object
		return ORM::factory(inflector::singular($table));
	}

	/**
	 * Converts the loaded object values to correct types.
	 *
	 * @return void
	 */
	protected function load_object_types()
	{
		foreach (self::$fields[$this->table] as $field => $data)
		{
			if (isset($this->object->$field))
			{
				// Set the value type
				$this->object->$field = $this->set_value_type($field, $this->object->$field);
			}
		}
	}

	/**
	 * Converts a value for a specific field to the correct type.
	 *
	 * @param   string  field name
	 * @param   mixed   value
	 * @return  mixed
	 */
	protected function set_value_type($field, $value)
	{
		// Do nothing for columns with no data, or columns that are allowed to be NULL.
		if ( ! isset(self::$fields[$this->table][$field]) OR ($value === NULL AND ! empty(self::$fields[$this->table][$field]['null'])))
			return $value;

		// Get field data
		$data = self::$fields[$this->table][$field];

		if ( ! empty($data['binary']) AND ! empty($data['exact']) AND $data['length'] == 1)
		{
			// Use boolean for binary(1) fields
			$data['type'] = 'boolean';
		}

		switch ($data['type'])
		{
			case 'int':
				// // If the value is an empty string, set the value back to NULL
				// if ($value === '' AND ! empty($data['null']))
				// 	return NULL;

				$value = (int) $value;
			break;
			case 'float':
				$value = (float) $value;
			break;
			case 'boolean':
				$value = (bool) $value;
			break;
			case 'string':
				$value = (string) $value;
			break;
		}

		return $value;
	}

	/**
	 * Loads the database if it is not already loaded. Used during initialization
	 * and unserialization.
	 *
	 * @return  void
	 */
	protected function connect()
	{
		if (self::$db === NULL)
		{
			// Load database, if not already loaded
			isset(Kohana::$instance->db) or Kohana::$instance->db = Database::instance();

			// Insert db into this object
			self::$db = Kohana::$instance->db;
		}

		if (empty(self::$fields[$this->table]))
		{
			if ($fields = self::$db->list_fields($this->table))
			{
				foreach ($fields as $field => $data)
				{
					// Cache the column names
					self::$fields[$this->table][$field] = $data;
				}
			}
			else
			{
				// Table doesn't exist
				throw new Kohana_Exception('database.table_not_found', $this->table);
			}
		}
	}

	/**
	 * Finds the many<>many relationship table.
	 *
	 * @param   string  table name
	 * @return  string
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
	 * @param   string  table name
	 * @return  void
	 */
	protected function related_join($table)
	{
		$join = $this->related_table($table);

		// Primary and foreign keys
		$primary = $this->class.'_id';
		$foreign = inflector::singular($table).'_id';

		// Where has been set
		$this->where = TRUE;

		// Execute the join
		self::$db->where("$join.$primary", $this->object->id)->join($join, "$join.$foreign", "$table.id");
	}

} // End ORM