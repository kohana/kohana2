<?php defined('SYSPATH') or die('No direct script access.');

class ORM2_Core {

	// Current relationships
	protected $has_one                 = array();
	protected $belongs_to              = array();
	protected $has_many                = array();
	protected $has_and_belongs_to_many = array();

	// Current object
	protected $object = array();
	protected $loaded = FALSE;
	protected $saved  = FALSE;

	// Model table name
	protected $table_name;
	protected $table_columns;

	// Use plural table names by default
	protected $table_names_plural = TRUE;
	protected $reload_one_wakeup  = TRUE;

	// Table primary key
	protected $primary_key = 'id';

	// Database instance name
	protected $db = 'default';

	public static function factory($model, $id = NULL)
	{
		// Set class name
		$model = ucfirst($model).'_Model';

		return new $model($id);
	}

	public function __construct($id = NULL)
	{
		// Initialize database
		$this->__initialize();

		if ($id === NULL)
		{
			$this->clear();
		}
		elseif (is_object($id))
		{
			// Object is loaded and saved
			$this->loaded = $this->saved = TRUE;

			$this->load_values((array) $id);
		}
		else
		{
			$this->find($id);
		}
	}

	public function __initialize()
	{
		if ( ! is_object($this->db))
		{
			// Get database instance
			$this->db = Database::instance($this->db);
		}

		if (empty($this->table_name))
		{
			// Set the table name
			$this->table_name = strtolower(substr(get_class($this), 0, -6));

			if ($this->table_names_plural === TRUE)
			{
				// Make the table name plural
				$this->table_name = inflector::plural($this->table_name);
			}
		}

		if ( ! is_array($this->table_columns))
		{
			// Load table columns
			$this->table_columns = $this->db->list_fields($this->table_name);
		}
	}

	public function __sleep()
	{
		// Store only information about this object
		return array('object', 'loaded', 'saved');
	}

	public function __wakeup()
	{
		// Initialize database
		$this->__initialize();

		if ($this->reload_one_wakeup === TRUE)
		{
			// Reload the object
			$this->reload();
		}
	}

	public function __call($method, array $args)
	{
		if (method_exists($this->db, $method))
		{
			// Do not allow query methods
			if (preg_match('/^(?:query|get|insert|update|list_fields|field_data)$/', $method))
				return $this;

			// Pass through to Database, manually calling up to 3 args, for speed.
			switch (count($args))
			{
				case 0:
					return $this->db->$method();
				break;
				case 1:
					$this->db->$method($args[0]);
				break;
				case 2:
					$this->db->$method($args[0], $args[1]);
				break;
				case 3:
					$this->db->$method($args[0], $args[1], $args[2]);
				break;
				default:
					call_user_func_array(array($this->db, $method), $args);
				break;
			}

			return $this;
		}
		else
		{
			throw new Kohana_Exception('core.invalid_method', $method, get_class($this));
		}
	}

	public function __get($column)
	{
		if (isset($this->object[$column]) OR array_key_exists($column, $this->object))
		{
			return $this->object[$column];
		}
		elseif (($owner = isset($this->has_one[$column])) OR isset($this->belongs_to[$column]))
		{
			// Determine the model name
			$model = ($owner === TRUE) ? $this->has_one[$column] : $this->belongs_to[$column];

			// Load model
			$model = ORM2::factory($model);

			if (isset($this->object[$column.'_'.$model->meta('primary_key')]))
			{
				// Use the FK that exists in this model as the PK
				$where = array($model->meta('primary_key') => $this->object[$column.'_'.$model->meta('primary_key')]);
			}
			else
			{
				// Use this model PK as the FK
				$where = array($this->foreign_key() => $this->object[$this->primary_key]);
			}

			// one<>alias:one relationship
			return $this->object[$column] = $model->find($where);
		}
		elseif (in_array($column, $this->has_one) OR in_array($column, $this->belongs_to))
		{
			$model = ORM2::factory($column);

			if (isset($this->object[$column.'_'.$model->meta('primary_key')]))
			{
				// Use the FK that exists in this model as the PK
				$where = array($model->meta('primary_key') => $this->object[$column.'_'.$model->meta('primary_key')]);
			}
			else
			{
				// Use this model PK as the FK
				$where = array($this->foreign_key() => $this->object[$this->primary_key]);
			}

			// one<>one relationship
			return $this->object[$column] = ORM2::factory($column, $where);
		}
		elseif (isset($this->has_many[$column]))
		{
			// Load the "middle" model
			$through = ORM2::factory(inflector::singular($this->has_many[$column]));

			// Load the "end" model
			$model = ORM2::factory(inflector::singular($column));

			// Load JOIN info
			$join_table = $through->meta('table_name');
			$join_col1  = $model->foreign_key(NULL, $join_table);
			$join_col2  = $model->foreign_key(TRUE);

			// one<>alias:many relationship
			return $this->object[$column] = $model
				->join($join_table, $join_col1, $join_col2)
				->where($this->foreign_key(NULL, $join_table), $this->object[$this->primary_key])
				->find_all();
		}
		elseif (in_array($column, $this->has_many))
		{
			// one<>many relationship
			return $this->object[$column] = ORM2::factory(inflector::singular($column))
				->where($this->foreign_key($column), $this->object[$this->primary_key])
				->find_all();
		}
		elseif (in_array($column, $this->has_and_belongs_to_many))
		{
			// Load the remote model, always singular
			$model = ORM2::factory(inflector::singular($column));

			// Load JOIN info
			$join_table = $model->join_table($this->table_name);
			$join_col1  = $model->foreign_key(NULL, $join_table);
			$join_col2  = $model->foreign_key(TRUE);

			// many<>many relationship
			return $this->object[$column] = $model
				->join($join_table, $join_col1, $join_col2)
				->where($this->foreign_key(NULL, $join_table), $this->object[$this->primary_key])
				->find_all();
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	public function __set($column, $value)
	{
		if (isset($this->object[$column]) OR array_key_exists($column, $this->object))
		{
			$this->object[$column] = $this->load_type($column, $value);
		}
		else
		{
			throw new Kohana_Exception('core.invalid_property', $column, get_class($this));
		}
	}

	public function __isset($column)
	{
		return isset($this->object[$column]);
	}

	public function __unset($column)
	{
		unset($this->object[$column]);
	}

	public function __toString()
	{
		return $this->object[$this->primary_key];
	}

	public function meta($key)
	{
		$allowed = array
		(
			// Model table information
			'primary_key', 'table_name',

			// Model status
			'loaded', 'saved',

			// Relationships
			'has_one', 'belongs_to', 'has_many', 'has_many_and_belongs_to'
		);

		if (in_array($key, $allowed))
		{
			return $this->$key;
		}
		else
		{
			throw new Kohana_Exception('orm.meta_not_available', $key, get_class($this));
		}
	}

	public function reload()
	{
		return $this->find($this->{$this->primary_key});
	}

	public function find($id = NULL)
	{
		if (func_num_args() > 0)
		{
			if (is_array($id))
			{
				// Search for all clauses
				$this->db->where($id);
			}
			else
			{
				// Search for a specific column
				$this->db->where($this->where_key($id), $id);
			}
		}

		return $this->load_result();
	}

	public function find_all($limit = NULL, $offset = 0)
	{
		if (func_num_args() > 0)
		{
			// Set limit and offset
			$this->db->limit($limit, $offset);
		}

		return $this->load_result(TRUE);
	}

	public function clear()
	{
		// Object is no longer loaded or saved
		$this->loaded = $this->saved = FALSE;

		// Replace the current object with an empty one
		$this->load_values(array());

		return $this;
	}

	public function where_key($id)
	{
		return $this->primary_key;
	}

	public function foreign_key($table = NULL, $remote_table = NULL)
	{
		if ($table === TRUE)
		{
			// Return the name of this tables PK
			return $this->table_name.'.'.$this->primary_key;
		}

		if (is_string($remote_table))
		{
			// Add a period for remote_table.column support
			$remote_table .= '.';
		}

		if ( ! is_string($table) OR ! isset($this->object[$table.'_'.$this->primary_key]))
		{
			// Use this table
			$table = $this->table_name;

			if ($this->table_names_plural === TRUE)
			{
				// Make the key name singular
				$table = inflector::singular($table);
			}
		}

		return $remote_table.$table.'_'.$this->primary_key;
	}

	public function join_table($table)
	{
		// This uses alphabetical comparison to choose the name of the table.
		// Example: The joining table of users and roles would be roles_users,
		// because "r" comes before "u". Joining products and categories would
		// result in categories_prouducts, because "c" comes before "p".
		// Example: zoo > zebra > robber > ocean > angel > aardvark

		if ($this->table_name > $table)
		{
			$table = $table.'_'.$this->table_name;
		}
		else
		{
			$table = $this->table_name.'_'.$table;
		}

		return $table;
	}

	protected function load_type($column, $value)
	{
		if ( ! isset($this->table_columns[$column]))
			return $value;

		// Load column data
		$column = $this->table_columns[$column];

		if ($value === NULL AND ! empty($column['null']))
			return $value;

		if ( ! empty($column['binary']) AND ! empty($column['exact']) AND (int) $column['length'] === 1)
		{
			// Use boolean for BINARY(1) fields
			$column['type'] = 'boolean';
		}

		switch ($column['type'])
		{
			case 'int':
				$value = ($value === '' AND ! empty($data['null'])) ? NULL : (int) $value;
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

	protected function load_values(array $values)
	{
		// Get the table columns
		$columns = array_keys($this->table_columns);

		// Make sure all the columns are defined
		$this->object += array_combine($columns, array_fill(0, count($columns), NULL));

		foreach ($columns as $name)
		{
			if (isset($values[$name]))
			{
				// Set the column
				$this->__set($name, $values[$name]);
			}
		}

		return $this;
	}

	protected function load_result($array = FALSE)
	{
		if ($array === FALSE)
		{
			// Only fetch 1 record
			$this->db->limit(1);
		}

		// Load the result
		$result = $this->db->get($this->table_name)->result(FALSE);

		if ($array === TRUE)
		{
			// Return an iterated result
			return new ORM_Iterator(get_class($this), $result);
		}

		// Model is loaded and saved
		$this->loaded = $this->saved = TRUE;

		// Load object values
		$this->load_values($result->current());

		return $this;
	}

} // End ORM