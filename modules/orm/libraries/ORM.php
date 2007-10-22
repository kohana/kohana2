<?php defined('SYSPATH') or die('No direct script access.');

/*
	Has one?
	  TRUE - Add fields via join()
	Has many?
	  TRUE - FOREIGN has THIS_id?
	    TRUE  - Load by $this->id
	    FALSE - Load by joining table THIS_FOREIGN
	Belongs to many?
	  TRUE - FOREIGN has THIS_id?
	    TRUE  - Load by $this->id
	    FALSE - Load by joining table FOREIGN_THIS
*/

class ORM_Core {

	// Database object
	private static $db = NULL;

	// Model class name
	protected $_class = '';

	// Table name
	protected $_table = '';

	// Table meta-data
	protected $_meta = array();

	// Where statement used to fetch the object
	protected $_where = FALSE;

	// Object data
	protected $_data = array();

	// Object relationships
	protected $_relationships = array();

	// Cache of changed data
	protected $_changed = array();

	/**
	 * Constructor
	 */
	public function __construct($where = FALSE)
	{
		if (self::$db === NULL)
		{
			// Load Database
			self::$db = new Database(Config::item('orm.group'));
		}

		// Set model class name
		$this->_class = '_'.end(explode('_', get_class($this)));

		if ($this->_table == '')
		{
			// Set table name, save $name for Log
			$name = substr(get_class($this), 0, -(strlen($this->_class)));
			$this->_table = inflector::plural(strtolower($name));
		}

		if ( ! empty($this->_relationships))
		{
			foreach($this->_relationships as $key => $val)
			{
				// Makes a mirrored array, eg: foo=foo
				$this->_relationships[$key] = array_combine($val, $val);
			}
		}

		// Load meta-data about the table this object represents
		$result = self::$db
		->select(array
		(
			'column_name',
			'column_type',
			'data_type',
			'column_key',
			'column_default',
			'is_nullable',
			'character_maximum_length',
			'numeric_precision'
		))
		->from('information_schema.columns')
		->where(array
		(
			'table_name' => $this->_table
		))
		->get();

		if (count($result) > 0)
		{
			/**
			 * @todo This really should be cached
			 */
			foreach($result as $row)
			{
				$this->_meta[$row->column_name] = array
				(
					'primary'   => ($row->column_key  == 'PRI'),
					'unique'    => ($row->column_key  == 'UNI'),
					'nullable'  => ($row->is_nullable == 'YES'),
					'unsigned'  => (stripos($row->column_type, 'unsigned') !== FALSE),
					'maxlength' => ($row->character_maximum_length ? $row->character_maximum_length : $row->numeric_precision),
					'default'   => $row->column_default
				);

				$this->_data[$row->column_name] = NULL;
			}
		}

		// Clean up the result
		unset($result);

		// Add child objects
		foreach($this->_data as $key => $val)
		{
			if (substr($key, -3) == '_id')
			{
				$name  = substr($key, 0, -3);
				$model = ucfirst($name).$this->_class;

				$this->_data[$name] = new $model();
			}
		}

		/*
		if ( ! empty($where))
		{
			// Primary key
			$pk = inflector::singular($this->_table).'_id';

			// SELECT
			$select = $this->select_fields();

			// FROM
			$from = $this->_table;

			// JOIN
			$join = array();

			if ( ! empty($this->_relationships['has_many']))
			{
				foreach($this->_relationships['has_many'] as $table)
				{
					$object = inflector::singular($table);

					// Foreign key
					$fk = "{$object}_id";

					// Create a new object instance
					$object = ucfirst($object).$this->_class;
					$object = new $object();

					// Add the child select fields
					$select = array_merge($select, $object->select_fields());

					// Remove the model, we no longer need it
					unset($object);

					if (isset($this->$fk))
					{
						$join[] = array($table, "{$this->_table}.{$fk} = {$table}.id");
					}
					else
					{
						// Joining table: {this}_{foreign}
						$from = "{$this->_table}_{$table}";

						// Join this table with the result
						$join[] = array($this->_table, "{$from}.{$pk} = {$this->_table}.id");

						// Join foreign table with result
						$join[] = array($table, "{$from}.{$fk} = {$table}.id");
					}
				}
			}

			while (list($table, $on) = array_shift($join))
			{
				self::$db->join($table, $on);
			}

			// WHERE
			$where = is_array($where) ? $where : array("{$this->_table}.id" => $where);

			// Get the result
			$result = self::$db->select($select)->from($from)->where($where)->get();

			foreach($result as $row)
			{
				print Kohana::debug($row);
			}
		}
		*/

		// Load model data
		// $this->get($where);

		$select = $this->select_fields();

		Log::add('debug', $name.' ORM Model loaded');
	}

	public function select_fields($prefix = '')
	{
		// Name is singular
		$name = inflector::singular($this->_table);

		// Set the prefix
		$prefix .= (($prefix != '') ? ':' : '').$name;

		// SELECT part of query
		$select = array();

		// Fetch keys
		foreach(array_keys($this->_meta) as $field)
		{
			$select[] = "{$this->_table}.{$field} AS {$prefix}:{$field}";
		}

		if ( ! empty($this->_relationships['has_one']))
		{
			// Fetch child keys
			foreach($this->_relationships['has_one'] as $object)
			{
				$select = array_merge($select, $this->_data[$object]->select_fields($prefix));
			}
		}

		if ( ! empty($this->_relationships['belongs_to']))
		{
			// Fetch child keys
			foreach($this->_relationships['belongs_to'] as $object)
			{
				$select = array_merge($select, $this->_data[$object]->select_fields($prefix));
			}
		}

		return $select;
	}

	/**
	 * Magic __get function
	 *
	 * @access public
	 * @param  string
	 * @return void
	 */
	public function __get($key)
	{
		if ( ! isset($this->_data[$key]))
		{
			if (isset($this->_relationships['has_many']) AND isset($this->_relationships['has_many'][$key]))
			{
				die($this->_name.' has many '.$key);
			}
		}
		else
		{
			return $this->_data[$key];
		}
	}

	/**
	 * Magic __set function
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function __set($key, $val)
	{
		if ($key[0] !== '_')
		{
			if (isset($this->_data[$key]) AND $this->_data[$key] != $val)
			{
				$this->_changed[$key] = $key;
			}

			$this->_data[$key] = $val;
		}
	}

	/**
	 * Magic __call method
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function __call($method, $arguments = array())
	{
		if ( ! empty($this->_relationships['has_many'][$method]))
		{
			$array = array();

			// Set a limit
			if (count($arguments) > 0)
			{
				self::$db->limit((int) current($arguments));
			}

			// Primary and foreign models
			$primary = inflector::singular($this->_table);
			$foreign = inflector::singular($method);

			// Primary and foreign keys
			$pk = $primary.'_id';
			$fk = $foreign.'_id';

			// Set model class name
			$class = ucfirst($foreign).$this->_class;

			// Fetch result of all the models
			if (isset($this->_meta[$foreign.'_id']))
			{
				self::$db->from($method)->where(array($fk => $this->id));
			}
			else
			{
				// Create a new model to fetch the keys
				$model = new $class();

				// Joining table
				$joiner = $this->_table.'_'.$method;

				// Add internal fields
				foreach(array_keys($this->_meta) as $field)
				{
					$select[] = "{$this->_table}.{$field} AS {$this->_table}:{$field}";
				}

				// Join internal table
				self::$db->join($this->_table, "{$joiner}.{$pk} = {$this->_table}.id");

				// Add external fields
				foreach(array_keys($model->data()) as $field)
				{
					$select[] = "{$method}.{$field} AS {$method}:{$field}";
				}

				// Join external table
				self::$db->join($method, "{$joiner}.{$fk} = {$method}.id");

				// Select internal and external fields using the joining table as the primary table
				self::$db->select($select)->from($joiner);
			}

			die(Kohana::debug(self::$db->get()->result_array()));

			// Return a result object
			return new ORM_Aggregate($class, self::$db->get());
		}
	}

	public function _table()
	{
		return $this->_table;
	}

	/**
	 * Re-load the object with a where clause, generally the id of the object to load
	 *
	 * @access public
	 * @param  mixed   id key or an array accepted by Database
	 * @return boolean
	 */
	public function get($where)
	{
		// Return without loading for empty statements
		if (empty($where))
			return FALSE;

		// Make the statement into an array
		if ( ! is_array($where))
		{
			$where = array('id' => $where);
		}

		// Return without loading if the object is already loaded
		if ($where === $this->_where)
			return FALSE;

		// Fetch object data
		$result = self::$db->select(array_keys($this->_meta))->from($this->_table)->where($where)->get();

		if (count($result) > 0)
		{
			// Cache the statement
			$this->_where = $where;

			// Set object values
			foreach($result[0] as $field => $value)
			{
				$this->$field = $value;
			}

			// Reset changed variables
			$this->_changed = array();

			if ( ! empty($this->_relationships['has_one']))
			{
				// Loop and load all child objects
				foreach($this->_relationships['has_one'] as $object)
				{
					// Set where statement
					$where = $object.'_id';
					$where = isset($this->$where) ? array('id' => $this->$where) : FALSE;

					if (isset($this->_data[$object]))
					{
						// Load the object
						$this->_data[$object]->get($where);
					}
					else
					{
						// Set class name
						$class = ucfirst($object).$this->_class;

						// Load child object
						$this->_data[$object] = new $class($where);
					}
				}
			}

			// Successful load
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Return object data
	 *
	 * @access public
	 * @return array
	 */
	public function data($data = FALSE)
	{
		if (empty($data))
		{
			$array = array();

			foreach(array_keys($this->_meta) as $key)
			{
				$array[$key] = isset($this->_data[$key]) ? $this->_data[$key] : NULL;
			}

			return $array;
		}

		foreach($data as $key => $value)
		{
			if (isset($this->_meta[$key]))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Save an object
	 *
	 * @access public
	 * @return boolean
	 */
	public function save()
	{
		if (empty($this->_where))
		{
			// Do an insert
			if ($id = self::$db->insert($this->_table, $this->data())->insert_id())
			{
				$this->id = $id;
				$this->_where = array('id' => $id);

				return TRUE;
			}
		}
		elseif (empty($this->_changed))
		{
			// No data has changed
			return TRUE;
		}
		else
		{
			// Fetch data
			$data = $this->data();

			// Remove id, to prevent updates
			unset($data['id']);

			// Do an update
			if (count(self::$db->update($this->_table, $data, $this->_where)) > 0)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

} // End ORM class

class ORM_Aggregate implements ArrayAccess, Iterator, Countable {

	// Result object
	protected $result = NULL;

	// Total rows and current row
	protected $total_rows  = FALSE;
	protected $current_row = FALSE;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  resource
	 * @param  resource
	 * @param  boolean
	 * @param  string
	 * @return void
	 */
	public function __construct($model, $result)
	{
		// Save the model name
		$this->model = $model;

		// Load the result into self
		$this->result = $result;

		// Find total rows
		$this->total_rows = count($this->result);

		// Set current row
		$this->current_row = 0;
	}

	/**
	 * Fetch an array of all the objects
	 */
	public function result_array()
	{
		$rows = array();

		if (count($this->result) > 0)
		{
			for($i = 0; $i < $this->total_rows; $i++)
			{
				$rows[] = $this->offsetGet($i);
			}
		}

		return $rows;
	}

	// Interface: Countable
	public function count()
	{
		return $this->total_rows;
	}
	// End Interface

	// Interface: ArrayAccess
	public function offsetExists($offset)
	{
		if ($this->total_rows > 0)
		{
			$min = 0;
			$max = $this->total_rows - 1;

			return ($offset < $min OR $offset > $max) ? FALSE : TRUE;
		}

		return FALSE;
	}

	public function offsetGet($offset)
	{
		$data = array();

		foreach($this->result[$offset] as $key => $value)
		{
			$data[$key] = $value;
		}

		// Get model name
		$model = $this->model;

		// Create new object
		$model = new $model();

		// Set data
		$model->data($data);

		return $model;
	}

	public function offsetSet($offset, $value)
	{
		/**
		 * @todo Make this useful
		 */
		return FALSE;
	}

	public function offsetUnset($offset)
	{
		/**
		 * @todo Make this useful
		 */
		return FALSE;
	}
	// End Interface

	// Interface: Iterator
	public function current()
	{
		return $this->offsetGet($this->current_row);
	}

	public function key()
	{
		return $this->current_row;
	}

	public function next()
	{
		return ++$this->current_row;
	}

	public function prev()
	{
		return --$this->current_row;
	}

	public function rewind()
	{
		return $this->current_row = 0;
	}

	public function valid()
	{
		return $this->offsetExists($this->current_row);
	}
	// End Interface

} // End Mysql_Result Class