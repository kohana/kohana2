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

	public static $instances = array();

	// Global benchmarks
	public static $benchmarks = array();

	// Last execute query
	public $last_query;

	// Configuration array
	protected $_config;

	// Required configuration keys
	protected $_config_required = array();

	// Raw server connection
	protected $_connection;

	// Cache (Cache object for cross-request, array for per-request)
	protected $_cache;

	public static function instance($name = 'default')
	{
		if ( ! isset(Database::$instances[$name]))
		{
			// Load the configuration for this database group
			$config = Kohana::config('database.'.$name);

			// Set the driver class name
			$driver = 'Database_'.ucfirst($config['connection']['type']);

			// Create the database connection instance
			Database::$instances[$name] = new $driver($config);
		}

		return Database::$instances[$name];
	}

	public function __construct(array $config)
	{
		// Store the config locally
		$this->_config = $config;

		if ($this->_config['cache'] !== FALSE)
		{
			if (is_string($this->_config['cache']))
			{
				// Use Cache library
				$this->_cache = new Cache($this->_config['cache']);
			}
			elseif ($this->_config['cache'] === TRUE)
			{
				// Use array
				$this->_cache = array();
			}
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	abstract public function connect();

	abstract public function disconnect();

	abstract public function set_charset($charset);

	abstract public function query_execute($sql);

	abstract public function escape($value);

	/**
	 * Escapes a table (and or field) and adds the table prefix
	 * Reserved characters not allowed in table names for the builder are [`*.]
	 *
	 * @param  string|array  String of table/field name or array of Field=>Alias for 'Field AS Alias'
	 * @param  bool          True to add the table prefix to the alias (if an array is used for $table)
	 * @return string
	 */
	abstract public function escape_table($table, $prefix_alias = FALSE);

	abstract public function list_fields($table);

	public function query($sql)
	{
		// Start the benchmark
		$start = microtime(TRUE);

		if (is_array($this->_cache))
		{
			$hash = $this->query_hash($sql);

			if (isset($this->_cache[$hash]))
			{
				// Use cached result
				$result = $this->_cache[$hash];

				// It's from cache
				$sql .= ' [CACHE]';
			}
			else
			{
				// No cache, execute query and store in cache
				$result = $this->_cache[$hash] = $this->query_execute($sql);
			}
		}
		else
		{
			// Execute the query, cache is off
			$result = $this->query_execute($sql);
		}

		// Stop the benchmark
		$stop = microtime(TRUE);

		if ($this->_config['benchmark'] === TRUE)
		{
			// Benchmark the query
			Database::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($result));
		}

		return $result;
	}

	/**
	 * Performs the query on the cache (and caches it if it's not found)
	 *
	 * @param   string  query
	 * @param   int     time-to-live (NULL for Cache default)
	 * @return  Database_Cache_Result
	 */
	public function query_cache($sql, $ttl)
	{
		if ( ! $this->_cache instanceof Cache)
		{
			throw new Database_Exception('Database :name has not been configured to use the Cache library.');
		}

		// Start the benchmark
		$start = microtime(TRUE);

		$hash = $this->query_hash($sql);

		if (($data = $this->_cache->get($hash)) !== NULL)
		{
			// Found in cache, create result
			$result = new Database_Cache_Result($data, $sql, $this->_config['object']);

			// It's from the cache
			$sql .= ' [CACHE]';
		}
		else
		{
			// Run the query and return the full array of rows
			$data = $this->query_execute($sql)->as_array(TRUE);

			// Set the Cache
			$this->_cache->set($hash, $data, NULL, $ttl);

			// Create result
			$result = new Database_Cache_Result($data, $sql, $this->_config['object']);
		}

		// Stop the benchmark
		$stop = microtime(TRUE);

		if ($this->_config['benchmark'] === TRUE)
		{
			// Benchmark the query
			Database::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($result));
		}

		return $result;
	}

	/**
	 * Generates a hash for the given query
	 *
	 * @param   string  SQL query string
	 * @return  string
	 */
	protected function query_hash($sql)
	{
		return sha1(str_replace("\n", ' ', trim($sql)));
	}

	/**
	 * Clears the internal query cache.
	 *
	 * @param   string|boolean  clear cache by SQL statement, NULL for all, or TRUE for last query
	 * @return  Database
	 */
	public function clear_cache($sql = NULL)
	{
		if (isset($this->_cache))
		{
			// Using cross-request Cache library
			if ($sql === TRUE)
			{
				$this->_cache->delete($this->query_hash($this->_last_query));
			}
			elseif (is_string($sql))
			{
				$this->_cache->delete($this->query_hash($sql));
			}
			else
			{
				$this->_cache->delete_all();
			}
		}
		else
		{
			// Using per-request memory cache
			if ($sql === TRUE)
			{
				unset($this->_query_cache[$this->query_hash($this->last_query)]);
			}
			elseif (is_string($sql))
			{
				unset($this->_query_cache[$this->query_hash($sql)]);
			}
			else
			{
				$this->_query_cache = array();
			}
		}
	}

	/**
	 * Quotes the given value
	 *
	 * @param   mixed  value
	 * @return  mixed
	 */
	public function quote($value)
	{
		if ( ! $this->_config['escape'])
			return $value;

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

		return '\''.$this->escape($value).'\'';
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

	public function table_prefix()
	{
		return $this->_config['table_prefix'];
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