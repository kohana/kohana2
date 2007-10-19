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

			if ( ! empty($this->_relationships['has_one']))
			{
				// Loop and load all child objects
				foreach($this->_relationships['has_one'] as $object)
				{
					// Set WHERE
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

} // End ORM class