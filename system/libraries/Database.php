<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Database
 *  Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  copyright - (c) 2006 EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
 */
class Database_Core {

	// Global benchmark
	public static $benchmarks = array();

	// Configuration
	protected $config = array
	(
		'show_errors'   => TRUE,
		'benchmark'     => TRUE,
		'persistent'    => FALSE,
		'connection'    => '',
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => TRUE
	);

	// Database driver object
	protected $driver;
	protected $link;

	// Un-compiled parts of the SQL query
	protected $select     = array();
	protected $set        = array();
	protected $from       = array();
	protected $join       = array();
	protected $where      = array();
	protected $like       = array();
	protected $orderby    = array();
	protected $order      = array();
	protected $groupby    = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;
	protected $last_query = '';

	/*
	 * Constructor: __construct
	 *  Sets up the database configuration, loads the <Database_Driver>.
	 *
	 * Throws:
	 *  <Kohana_Database_Exception> if there is no database group, an invalid DSN is supplied,
	 *  or the requested driver doesn't exist.
	 */
	public function __construct($config = array())
	{
		if (empty($config))
		{
			// Load the default group
			$config = Config::item('database.default');
		}
		elseif (is_string($config))
		{
			// The config is a DSN string
			if (strpos($config, '://') !== FALSE)
			{
				$config = array('connection' => $config);
			}
			// The config is a group name
			else
			{
				$name = $config;

				// Test the config group name
				if (($config = Config::item('database.'.$config)) === NULL)
					throw new Kohana_Database_Exception('database.undefined_group', $name);
			}
		}

		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);

		// Make sure the connection is valid
		if (strpos($this->config['connection'], '://') === FALSE)
			throw new Kohana_Exception('database.invalid_dsn', $this->config['connection']);

		// Parse the DSN, creating an array to hold the connection parameters
		$db = array
		(
			'type'     => FALSE,
			'user'     => FALSE,
			'pass'     => FALSE,
			'host'     => FALSE,
			'port'     => FALSE,
			'socket'   => FALSE,
			'database' => FALSE
		);

		// Get the protocol and arguments
		list ($db['type'], $connection) = explode('://', $this->config['connection'], 2);

		if (strpos($connection, '@') !== FALSE)
		{
			// Get the username and password
			list ($db['pass'], $connection) = explode('@', $connection, 2);
			list ($db['user'], $db['pass']) = explode(':', $db['pass'], 2);

			// Prepare for finding the database
			$connection = explode('/', $connection);

			// Find the database name
			$db['database'] = array_pop($connection);

			// Reset connection string
			$connection = implode('/', $connection);

			// Find the socket
			if (preg_match('/^unix\([^)]++\)/', $connection))
			{
				// This one is a little hairy: we explode based on the end of
				// the socket, removing the 'unix(' from the connection string
				list ($db['socket'], $connection) = explode(')', substr($connection, 5), 2);
			}
			elseif (strpos($connection, ':') !== FALSE)
			{
				// Fetch the host and port name
				list ($db['host'], $db['port']) = explode(':', $connection, 2);
			}
			else
			{
				$db['host'] = $connection;
			}
		}
		else
		{
			// File connection
			$connection = explode('/', $connection);

			// Find database file name
			$db['database'] = array_pop($connection);

			// Find database directory name
			$db['socket'] = implode('/', $connection).'/';
		}

		// Reset the connection array to the database config
		$this->config['connection'] = $db;

		try
		{
			// Set driver name
			$driver = 'Database_'.ucfirst($this->config['connection']['type']).'_Driver';

			// Manually call auto-loading, for proper exception handling
			Kohana::auto_load($driver);

			// Initialize the driver
			$this->driver = new $driver($this->config);
		}
		catch (Kohana_Exception $exception)
		{
			throw new Kohana_Database_Exception('database.driver_not_supported', $this->config['connection']['type']);
		}

		// Validate the driver
		if ( ! in_array('Database_Driver', class_implements($this->driver)))
			throw new Kohana_Exception('database.driver_not_supported', 'Database drivers must use the Database_Driver interface.');

		Log::add('debug', 'Database Library initialized');
	}

	/*
	 * Method: connect
	 *  Simple connect method to get the database queries up and running.
	 */
	public function connect()
	{
		if ( ! is_resource($this->link))
		{
			if ( ! is_resource($this->link = $this->driver->connect()))
				throw new Kohana_Exception('database.connection', $this->driver->show_error());
		}
	}

	/*
	 * Method: query
	 *  Runs a query into the driver and returns the result.
	 *
	 * Parameters:
	 *  sql - sql query to execute
	 *
	 * Returns:
	 *  <Database_Result> object
	 */
	public function query($sql = '')
	{
		if ($sql == '') return FALSE;

		// No link? Connect!
		$this->link or $this->connect();

		// Start the benchmark
		$start = microtime(TRUE);

		if (func_num_args() > 1) //if we have more than one argument ($sql)
		{
			$argv = func_get_args();
			$binds = (is_array(next($argv))) ? current($argv) : $argv;
		}

		// Compile binds if needed
		if (isset($binds))
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		// Fetch the result
		$result = $this->driver->query($this->last_query = $sql);

		// Stop the benchmark
		$stop = microtime(TRUE);

		if ($this->config['benchmark'] == TRUE)
		{
			// Benchmark the query
			self::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start, 'rows' => count($result));
		}

		return $result;
	}

	/*
	 * Method: select
	 *  Selects the column names for a database <Database.query>.
	 *
	 * Parameters:
	 *  sql - string or array of column names to <Database.select>
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function select($sql = '*')
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach($sql as $val)
		{
			if (($val = trim($val)) == '') continue;

			$this->select[] = $this->driver->escape_column($val);
		}

		return $this;
	}

	/*
	 * Method: from
	 *  Selects the from table(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  sql - string or array of tables to <Database.select>
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function from($sql)
	{
		foreach((array) $sql as $val)
		{
			if (($val = trim($val)) == '') continue;

			$this->from[] = $this->config['table_prefix'].$val;
		}

		return $this;
	}

	/*
	 * Method: join
	 *  Generates the JOIN portion of the query.
	 *
	 * Parameters:
	 *  table - table name
	 *  cond  - join condition
	 *  type  - type of join (optional)
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function join($table, $cond, $type = '')
	{
		if ($type != '')
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		$this->join[] = $type.'JOIN '.$this->driver->escape_column($this->config['table_prefix'].$table).' ON '.$cond;

		return $this;
	}

	/*
	 * Method: where
	 *  Selects the where(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  key   - key name or array of key => value pairs
	 *  value - value to match with key
	 *  quote - disable quoting of WHERE clause
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function where($key, $value = NULL, $quote = TRUE)
	{
		if (func_num_args() < 2 AND ! is_array($key))
		{
			$quote = -1;
		}

		$this->where = array_merge($this->where, $this->driver->where($key, $value, 'AND ', count($this->where), $quote));
		return $this;
	}

	/*
	 * Method: orwhere
	 *  Selects the or where(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  key   - key name or array of key => value pairs
	 *  value - value to match with key
	 *  quote - disable quoting of WHERE clause
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function orwhere($key, $value = NULL, $quote = TRUE)
	{
		if (func_num_args() < 2 AND ! is_array($key))
		{
			$quote = -1;
		}

		$this->where = array_merge($this->where, $this->driver->where($key, $value, 'OR ', count($this->where), $quote));
		return $this;
	}

	/*
	 * Method: like
	 *  Selects the like(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  field - field name or array of field => match pairs
	 *  match - like value to match with field
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function like($field, $match = '')
	{
		$this->like = array_merge($this->like, $this->driver->like($field, $match, 'AND ', count($this->like)));
		return $this;
	}

	/*
	 * Method: orlike
	 *  Selects the or like(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  field - field name or array of field => match pairs
	 *  match - like value to match with field
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function orlike($field, $match = '')
	{
		$this->like = array_merge($this->like, $this->driver->like($field, $match, 'OR ', count($this->like)));
		return $this;
	}

	/*
	 * Method: groupby
	 *  chooses the column to group by in a select <Database.query>.
	 *
	 * Parameters:
	 *  by - column name to group by
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function groupby($by)
	{
		if ( ! is_array($by))
		{
			$by = explode(',', (string) $by);
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				$this->groupby[] = $val;
			}
		}

		return $this;
	}

	/*
	 * Method: having
	 *  Selects the having(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  key   - key name or array of key => value pairs
	 *  value - value to match with key
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function having($key, $value = '')
	{
	    $this->like = array_merge($this->like, $this->driver->having($key, $value, 'AND'));
        return $this;
	}

	/*
	 * Method: orhaving
	 *  Selects the or having(s) for a database <Database.query>.
	 *
	 * Parameters:
	 *  key   - key name or array of key => value pairs
	 *  value - value to match with key
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function orhaving($key, $value = '')
	{
		$this->like = array_merge($this->like, $this->driver->having($key, $value, 'OR'));
        return $this;
	}

	/*
	 * Method: orderby
	 *  Chooses which column(s) to order the <Database.select> <Database.query> by.
	 *
	 * Parameters:
	 *  orderby   - column(s) to order on
	 *  direction - direction of the order
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function orderby($orderby, $direction = '')
	{
		$direction = strtoupper(trim($direction));

		if ($direction != '')
		{
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()'))) ? ' '.$direction : ' ASC';
		}

		$this->orderby[] = $this->driver->escape_column($orderby).$direction;
		return $this;
	}

	/*
	 * Method: limit
	 *  Selects the limit section of a <Database.query>.
	 *
	 * Parameters:
	 *  value  - number of rows to limit result to
	 *  offset - offset in result to start returning rows from
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function limit($limit, $offset = FALSE)
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}

	/*
	 * Method: offset
	 *  Sets the offset portion of a <Database.query>.
	 *
	 * Parameters:
	 *  value - offset value
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function offset($value)
	{
		$this->offset = (int) $value;
		return $this;
	}

	/*
	 * Method: set
	 *  Allows key/value pairs to be set for <Database.insert>ing or <Database.update>ing.
	 *
	 * Parameters:
	 *  key   - key name or array of key => value pairs
	 *  value - value to match with key
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function set($key, $value = '')
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$this->set[$k] = $this->driver->escape($v);
		}

		return $this;
	}

	/*
	 * Method: get
	 *  Compiles the <Database.select> statement based on
	 *  the other functions called and runs the query.
	 *
	 * Parameters:
	 *  table  - table name
	 *  limit  - <Database.limit> clause
	 *  offset - <Database.offset> clause
	 *
	 * Returns:
	 *  The <Database> object
	 */
	public function get($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select(get_object_vars($this));

		$result = $this->query($sql);

		$this->reset_select();
		$this->last_query = $sql;

		return $result;
	}

	/*
	 * Method: getwhere
	 *  Compiles the <Database.select> statement based on
	 *  the other functions called and runs the <Database.query>.
	 *
	 * Parameters:
	 *  table  - table name
	 *  where  - <Database.where> clause
	 *  limit  - <Database.limit) clause
	 *  offset - <Database.offset) clause
	 *
	 * Returns:
	 *  <Database_Result> object
	 */
	public function getwhere($table = '', $where = NULL, $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select(get_object_vars($this));

		$result = $this->query($sql);
		$this->reset_select();
		return $result;
	}

	/*
	 * Method: insert
	 *  Compiles an insert string and runs the <Database.query>.
	 *
	 * Parameters:
	 *  table - table name
	 *  set   - array of key/value pairs to insert
	 *
	 * Returns:
	 *  <Database_Result> object
	 */
	public function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == NULL)
		{
			if ($this->config['show_errors'])
				throw new Kohana_Database_Exception('database.must_use_set');
			else
				return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
			{
				if ($this->config['show_errors'])
					throw new Kohana_Database_Exception('database.must_use_table');
				else
					return FALSE;
			}

			$table = $this->from[0];
		}

		$sql = $this->driver->insert($this->config['table_prefix'].$table, array_keys($this->set), array_values($this->set));

		$this->reset_write();
		return $this->query($sql);
	}

	/*
	 * Method: update
	 *  Compiles an update string and runs the <Database.query>.
	 *
	 * Parameters:
	 *  table - table name
	 *  set   - associative array of update values
	 *  where - <Database.where> clause
	 *
	 * Returns:
	 *  <Database_Result> object
	 */
	public function update($table = '', $set = NULL, $where = NULL)
	{
		if ( is_array($set))
		{
			$this->set($set);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}

		if ($this->set == FALSE)
		{
			if ($this->config['show_errors'])
				throw new Kohana_Database_Exception('database.must_use_set');
			else
				return FALSE;
		}

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
			{
				if ($this->config['show_errors'])
					throw new Kohana_Database_Exception('database.must_use_table');
				else
					return FALSE;
			}

			$table = $this->from[0];
		}
		$sql = $this->driver->update($this->config['table_prefix'].$table, $this->set, $this->where);

		$this->reset_write();
		return $this->query($sql);
	}

	/*
	 * Method: delete
	 *  Compiles a delete string and runs the <Database.query>.
	 *
	 * Parameters:
	 *  table - table name
	 *  where - <Database.where> clause
	 *
	 * Returns:
	 *  <Database_Result> object
	 */
	public function delete($table = '', $where = NULL)
	{
		if ($table == '')
		{
			if ( ! isset($this->from[0]))
			{
				if ($this->config['show_errors'])
					throw new Kohana_Database_Exception('database.must_use_table');
				else
					return FALSE;
			}

			$table = $this->from[0];
		}

		if (! is_null($where))
		{
			$this->where($where);
		}

		if (count($this->where) < 1)
		{
			if ($this->config['show_errors'])
				throw new Kohana_Database_Exception('database.must_use_where');
			else
				return FALSE;
		}

		$sql = $this->driver->delete($this->config['table_prefix'].$table, $this->where);

		$this->reset_write();
		return $this->query($sql);
	}

	/*
	 * Method: last_query
	 *  Returns the last <Database.query> run.
	 *
	 * Returns:
	 *  A string containing the last SQL query
	 */
	public function last_query()
	{
	   return $this->last_query;
	}

	/*
	 * Method: count_records
	 *  Count query records.
	 *
	 * Parameters:
	 *  table - table name
	 *  where - <Database.where> clause (optional)
	 *
	 * Returns:
	 *  Number of records
	 */
	public function count_records($table = FALSE, $where = NULL)
	{
		if (count($this->from) < 1)
		{
			if ($table == FALSE)
				return FALSE;

			$this->from($table);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}

		$query = $this->select('COUNT(*)')->get();

		$column = 'COUNT(*)';
		return $query->current()->$column;
	}

	/*
	 * Method: reset_select
	 *  Resets all private select variables.
	 */
	private function reset_select()
	{
		$this->select   = array();
		$this->from     = array();
		$this->join     = array();
		$this->where    = array();
		$this->like     = array();
		$this->orderby  = array();
		$this->groupby  = array();
		$this->having   = array();
		$this->distinct = FALSE;
		$this->limit    = FALSE;
		$this->offset   = FALSE;
	}

	/*
	 * Method: reset_write
	 *  Resets all private insert and update variables.
	 */
	private function reset_write()
	{
		$this->set   = array();
		$this->from  = array();
		$this->where = array();
	}

	/*
	 * Method: list_tables
	 *  Lists all the tables in the current database.
	 *
	 * Returns:
	 *  An array of table names
	 */
	public function list_tables()
	{
		$this->link OR $this->driver->connect();

		$this->reset_select();

		return $this->driver->list_tables();
	}

	/*
	 * Method: table_exists
	 *  See if a table exists in the database.
	 *
	 * Parameters:
	 *  table_name - table name
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function table_exists($table_name)
	{
		return in_array($table_name, $this->list_tables());
	}

	/*
	 * Method: compile_binds
	 *  Combine a sql statement with the bind values. Used for safe queries.
	 *
	 * Parameters:
	 *  sql   - query to bind to the values
	 *  binds - array of values to bind to the query
	 *
	 * Returns:
	 *  String containing the final <Database.query> to run
	 */
	public function compile_binds($sql, $binds)
	{
		if (strpos($sql, '?') === FALSE)
			return $sql;

		foreach ((array) $binds as $val)
		{
			$val = $this->driver->escape($val);

			// Just in case the replacement string contains the bind
			// character we'll temporarily replace it with a marker
			$val = str_replace('?', '{%bind_marker%}', $val);
			// Replace possible regex vars like $0, $1 etc
			$val = str_replace('$', '\$', $val);

			$sql = preg_replace('/\?/', $val, $sql, 1);
		}

		return str_replace('{%bind_marker%}', '?', $sql);
	}

	/*
	 * Method: field_data
	 *  Get the field data for a database table, along with the field's attributes.
	 *
	 * Parameters:
	 *  table - table name
	 *
	 * Returns:
	 *  Array containing the field data
	 */
	public function field_data($table ='')
	{
		return $this->driver->field_data($table);
	}

	/*
	 * Method: escape
	 *  Escapes a value for a query.
	 *
	 * Parameters:
	 *  value - value to escape
	 *
	 * Returns:
	 *  An escaped version of the value
	 */
	public function escape($value)
	{
		return $this->driver->escape($value);
	}

	/*
	 * Method: escape_str
	 *  Escapes a string for a query.
	 *
	 * Parameters:
	 *  str - string to escape
	 *
	 * Returns:
	 *  An escaped version of the string
	 */
	public function escape_str($str)
	{
		return $this->driver->escape_str($str);
	}

	/*
	* Method: table_prefix
	*  Returns table prefix of current configuration.
	*
	* Returns:
	*  A string containing the table prefix for the database
	*/
	public function table_prefix()
	{
		return $this->config['table_prefix'];
	}

} // End Database Class

/*
 * Class: Kohana Database Exception
 *  Sets the code for a <Database> exception.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Kohana_Database_Exception extends Kohana_Exception {

	protected $code = E_DATABASE_ERROR;

}