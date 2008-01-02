<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Database_Mysqli_Driver
 *  Provides specific database items for MySQL.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Database_Mysqli_Driver extends Database_Mysql_Driver {

	// Database connection link
	protected $link;
	protected $db_config;
	protected $statements = array();

	/**
	 * Constructor: __construct
	 *  Sets up the config for the class.
	 *
	 * Parameters:
	 *  config - database configuration
	 */
	public function __construct($config)
	{
		$this->db_config = $config;

		Log::add('debug', 'MySQLi Database Driver Initialized');
	}

	/**
	 * Closes the database connection.
	 */
	public function __destruct()
	{
		is_object($this->link) and $this->link->close();
	}

	public function connect()
	{
		// Import the connect variables
		extract($this->db_config['connection']);

		// Build the connection info
		$host = (isset($host)) ? $host : $socket;

		// Make the connection and select the database
		if ($this->link = new mysqli($host, $user, $pass, $database))
		{
			if ($charset = $this->db_config['character_set'])
			{
				$this->set_charset($charset);
			}

			return $this->link;
		}

		return FALSE;
	}

	public function query($sql)
	{
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|REPLACE|SET)\b#i', $sql))
		{
			$hash = $this->query_hash($sql);

			if ( ! isset(self::$query_cache[$hash]))
			{
				// Set the cached object
				self::$query_cache[$hash] = new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
			}

			// Return the cached query
			return self::$query_cache[$hash];
		}

		return new Kohana_Mysqli_Result($this->link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset)
	{
		if ($this->link->set_charset($charset) === FALSE)
			throw new Kohana_Database_Exception('database.error', $this->show_error());
	}

	public function stmt_prepare($sql = '', $label)
	{
		$this->statements[$label] = $this->link->stmt_init();
		if ($stmt->prepare($sql))
			return TRUE;

		return FALSE;
	}

	public function stmt_execute($vals = array(), $label)
	{
		$bind_types = '';
		$i = 0;
		foreach ($vals as $val)
		{
			if (is_int($val))
			{
				$bind_typs .= 'i';
			}
			elseif (is_float($val))
			{
				$bind_types .= 'd';
			}
			else
			{
				$bind_types .= 's';
			}
		}
		call_user_func_array(array($this->statements[$label], 'bind_param'), array_combine($bind_types, $vals));
		$this->statements[$label]->execute();
	}

	public function stmt_clear($label)
	{
		if (isset($this->statments[$label]) AND is_object($this->statments[$label]))
			return $this->statments[$label]->close();

		return FALSE;
	}

	public function escape_str($str)
	{
		is_object($this->link) or $this->connect($this->db_config);

		return $this->link->real_escape_string($str);
	}

	public function show_error()
	{
		return $this->link->error;
	}

	public function field_data($table)
	{
		$query  = $this->link->query('SHOW COLUMNS FROM '.$this->escape_table($table));

		$table  = array();
		while ($row = $query->fetch_object())
		{
			$table[] = $row;
		}

		return $table;
	}

} // End Database_Mysqli_Driver Class

/**
 * Class: Mysqli_Result
 *  The result class for MySQLi queries.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Kohana_Mysqli_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	// Result resource
	protected $result = NULL;
	protected $link = NULL;

	// Total rows and current row
	protected $total_rows  = FALSE;
	protected $current_row = FALSE;

	// Insert id
	protected $insert_id = FALSE;

	// Data fetching types
	protected $fetch_type  = 'mysqli_fetch_object';
	protected $return_type = MYSQLI_ASSOC;

	/**
	 * Constructor: __construct
	 *  Sets up the class.
	 *
	 * Parameters:
	 *  result - result resource
	 *  link   - database resource link
	 *  object - return objects or arrays
	 *  sql    - sql query that was run
	 */
	public function __construct($link, $object = TRUE, $sql)
	{
		$this->link = $link;

		if ( ! $this->link->multi_query($sql))
		{
			// SQL error
			throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
		}
		else
		{
			$this->result = $this->link->store_result();

			// If the query is an object, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
			if (is_object($this->result))
			{
				$this->current_row = 0;
				$this->total_rows  = $this->result->num_rows;
				$this->fetch_type = ($object === TRUE) ? 'fetch_object' : 'fetch_array';
			}
			elseif ($this->link->error)
			{
				// SQL error
				throw new Kohana_Database_Exception('database.error', $this->link->error.' - '.$sql);
			}
			else
			{
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = $this->link->insert_id;
				$this->total_rows = $this->link->affected_rows;
			}
		}

		// Set result type
		$this->result($object);
	}

	/**
	 * Destructor: __destruct
	 *  Magic __destruct function, frees the result.
	 */
	public function __destruct()
	{
		if (is_object($this->result))
		{
			$this->result->free_result();
		}
	}

	public function result($object = TRUE, $type = MYSQLI_ASSOC)
	{
		$this->fetch_type = ((bool) $object) ? 'fetch_object' : 'fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'fetch_object')
		{
			$this->return_type = class_exists($type, FALSE) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}

	public function result_array($object = NULL, $type = MYSQLI_ASSOC)
	{
		$rows = array();

		if (is_string($object))
		{
			$fetch = $object;
		}
		elseif (is_bool($object))
		{
			if ($object === TRUE)
			{
				$fetch = 'fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = class_exists($type, FALSE) ? $type : 'stdClass';
			}
			else
			{
				$fetch = 'fetch_array';
			}
		}
		else
		{
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'fetch_object')
			{
				$type = class_exists($type, FALSE) ? $type : 'stdClass';
			}
		}

		if ($this->result->num_rows)
		{
			// Reset the pointer location to make sure things work properly
			$this->result->data_seek(0);

			while ($row = $this->result->$fetch($type))
			{
				$rows[] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function insert_id()
	{
		return $this->insert_id;
	}

	public function list_fields()
	{
		$field_names = array();
		while ($field = $this->result->fetch_field())
		{
			$field_names[] = $field->name;
		}

		return $field_names;
	}
	// End Interface

	// Interface: Countable
	/**
	 * Method: count
	 *  Counts the number of rows in the result set.
	 *
	 * Returns:
	 *  The number of rows in the result set
	 */
	public function count()
	{
		return $this->total_rows;
	}

	public function num_rows()
	{
		Log::add('error', 'You should really be using "count($result)" instead of "$result->num_rows()". Fix your code!');

		return $this->total_rows;
	}
	// End Interface

	// Interface: ArrayAccess
	/**
	 * Method: offsetExists
	 *  Determines if the requested offset of the result set exists.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  TRUE if the offset exists, FALSE otherwise
	 */
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

	/**
	 * Method: offsetGet
	 *  Retreives the requested query result offset.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  The query row
	 */
	public function offsetGet($offset)
	{
		// Check to see if the requested offset exists.
		if ( ! $this->offsetExists($offset))
			return FALSE;

		// Go to the offset
		$this->result->data_seek($offset);

		// Return the row
		$fetch = $this->fetch_type;
		return $this->result->$fetch($this->return_type);
	}

	/**
	 * Method: offsetSet
	 *  Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *  value  - value to set
	 *
	 * Returns:
	 *  <Kohana_Database_Exception> object
	 */
	public function offsetSet($offset, $value)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	/**
	 * Method: offsetUnset
	 *  Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  <Kohana_Database_Exception> object
	 */
	public function offsetUnset($offset)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}
	// End Interface

	// Interface: Iterator
	/**
	 * Method: current
	 *  Retreives the current result set row.
	 *
	 * Returns:
	 *  The current result row (type based on <Mysql_result.result>)
	 */
	public function current()
	{
		return $this->offsetGet($this->current_row);
	}

	/**
	 * Method: key
	 *  Retreives the current row id.
	 *
	 * Returns:
	 *  The current result row id
	 */
	public function key()
	{
		return $this->current_row;
	}

	/**
	 * Method: next
	 *  Moves the result pointer ahead one.
	 *
	 * Returns:
	 *  The next row id
	 */
	public function next()
	{
		return ++$this->current_row;
	}

	/**
	 * Method: next
	 *  Moves the result pointer back one.
	 *
	 * Returns:
	 *  The previous row id
	 */
	public function prev()
	{
		return --$this->current_row;
	}

	/**
	 * Method: rewind
	 *  Moves the result pointer to the beginning of the result set.
	 *
	 * Returns:
	 *  0
	 */
	public function rewind()
	{
		return $this->current_row = 0;
	}

	/**
	 * Method: valid
	 *  Determines if the current result pointer is valid.
	 *
	 * Returns:
	 *  TRUE if the pointer is valid, FALSE otherwise
	 */
	public function valid()
	{
		return $this->offsetExists($this->current_row);
	}
	// End Interface

} // End Mysqli_Result Class