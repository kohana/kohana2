<?php defined('SYSPATH') or die('No direct script access.');

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
			}
		}

		// Load model data
		$this->get($where);

		Log::add('debug', $name.' ORM Model loaded');
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

			/**
			 * @todo This should really be a big join, instead of looping through the ids and querying for data in each new object
			 */
			// Query for all of the ids
			$result = self::$db
			->select('id')
			->from($this->_table)
			->get();

			// Loop through each object and return an array of the objects
			if (count($result) > 0)
			{
				$class = get_class($this);

				foreach($result as $row)
				{
					$array[] = new $class($row->id);
				}
			}

			return $array;
		}
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
	public function data()
	{
		$array = array();

		foreach(array_keys($this->_meta) as $key)
		{
			$array[$key] = $this->_data[$key];
		}

		return $array;
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