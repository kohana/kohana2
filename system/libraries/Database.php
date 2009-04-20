<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database wrapper.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Core {

	const SELECT =  1;
	const INSERT =  2;
	const UPDATE =  3;
	const DELETE =  4;
	const CREATE = -2;
	const ALTER  = -3;
	const DROP   = -4;

	public static $instances = array();

	public static function instance($name = 'default')
	{
		if ( ! isset(Database::$instances[$name]))
		{
			// Load the configuration for this database group
			$config = Kohana::config('database.'.$name);

			if ( ! isset($config['type']))
			{
				throw new Kohana_Exception('Database type not defined in :name configuration',
					array(':name' => $name));
			}

			// Set the driver class name
			$driver = 'Database_'.ucfirst($config['type']);

			// Create the database connection instance
			Database::$instances[$name] = new $driver($config);
		}

		return Database::$instances[$name];
	}

	/**
	 * @var  string  the last query executed
	 */
	public $last_query;

	// Configuration array
	protected $_config;

	// Required configuration keys
	protected $_config_required = array();

	// Raw server connection
	protected $_connection;

	public function __construct(array $config)
	{
		foreach ($this->_config_required as $param)
		{
			if ( ! isset($config[$param]))
			{
				throw new Database_Exception('Required configuration parameter missing: :param',
					array(':param', $param));
			}
		}

		// Store the config locally
		$this->_config = $config;
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	abstract public function connect();

	abstract public function disconnect();

	abstract public function set_charset($charset);

	abstract public function query($type, $sql);

	abstract public function escape($value);

	abstract public function escape_table($table);

	abstract public function list_fields($table);

	public function quote($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif ($value === TRUE OR $value === FALSE)
		{
			return $value ? 'TRUE' : 'FALSE';
		}
		elseif (is_int($value) OR (is_string($value) AND ctype_digit($value)))
		{
			return (int) $value;
		}

		return '"'.$this->escape($value).'"';
	}

	public function list_tables()
	{
		throw new Database_Exception('The :method is not implemented in :class',
			array(':method' => __FUNCTION__, ':class' => get_class($this)));
	}

	public function list_columns($table)
	{
		throw new Database_Exception('The :method is not implemented in :class',
			array(':method' => __FUNCTION__, ':class' => get_class($this)));
	}

	/**
	 * Fetches SQL type information about a field, in a generic format.
	 *
	 * @param   string  field datatype
	 * @return  array
	 */
	protected function sql_type($str)
	{
		static $sql_types;

		if ($sql_types === NULL)
		{
			// Load SQL data types
			$sql_types = Kohana::config('sql_types');
		}

		$str = strtolower(trim($str));

		if (($open = strpos($str, '(')) !== FALSE)
		{
			// Find closing bracket
			$close = strpos($str, ')', $open) - 1;

			// Find the type without the size
			$type = substr($str, 0, $open);
		}
		else
		{
			// No length
			$type = $str;
		}

		if (empty($sql_types[$type]))
		{
			throw new Database_Exception('Undefined field type :type in :method of :class',
				array(':type' => $type, ':method' => __FUNCTION__, ':class' => get_class($this)));
		}

		// Fetch the field definition
		$field = $sql_types[$type];

		switch ($field['type'])
		{
			case 'string':
			case 'float':
				if (isset($close))
				{
					// Add the length to the field info
					$field['length'] = substr($str, $open + 1, $close - $open);
				}
			break;
			case 'int':
				// Add unsigned value
				$field['unsigned'] = (strpos($str, 'unsigned') !== FALSE);
			break;
		}

		return $field;
	}

} // End Database