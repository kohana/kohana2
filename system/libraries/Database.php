<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Database Class
 *
 * $Id$
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Database
 * @author      Rick Ellis, Kohana Team
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
	protected $connected  = FALSE;
	protected $last_query = '';

	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   array
	 * @return  void
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
			// This checks to see if the config is DSN string, or a config group name
			$config = (strpos($config, '://') === FALSE) ? Config::item('database.'.$config) : array('connection' => $config);
		}
		elseif (is_array($config))
		{
			$config = $config['default'];
		}

		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);

		// Parse the DSN into an array and validate it's length
		if (count($connection = @parse_url($this->config['connection'])) !== 5)
			throw new Kohana_Exception('database.invalid_dsn', $this->config['connection']);

		// Turn the DSN into local variables
		// NOTE: This step has to be done, because the order is defined by parse_url
		list($db['type'], $db['host'], $db['user'], $db['pass'], $db['database']) = array_values($connection);

		// Reset the connection array to the database config
		$this->config['connection'] = $db;

		// The database may contain slash characters when read as a path
		$this->config['connection']['database'] = trim($this->config['connection']['database'], '/');

		$driver = 'Database_'.ucfirst($this->config['connection']['type']);

		try
		{
			require_once Kohana::find_file('libraries', 'drivers/'.$driver, TRUE);
			// Initialize the driver
			$this->driver = new $driver($this->config);
		}
		catch (Kohana_Exception $exception)
		{
			throw new Kohana_Exception('database.driver_not_supported', $this->config['type']);
		}

		if ( ! in_array('Database_Driver', class_implements($this->driver)))
			throw new Kohana_Exception('database.driver_not_supported', 'Database drivers must use the Database_Driver interface.');

		Log::add('debug', 'Database Library initialized');
	}

	/**
	 * Connect
	 *
	 * Performs a connection to the database
	 *
	 * @access  public
	 * @param   mixed
	 * @return  object
	 */
	public function connect()
	{
		$this->connected = $this->driver->connect($this->config);

		if ($this->connected != TRUE)
			throw new Kohana_Exception('database.connection', mysql_error());
	}

	/**
	 * Query
	 *
	 * @access  public
	 * @param   string
	 * @param   array
	 * @param   boolean
	 * @return  mixed
	 */
	public function query($sql = '', $binds = FALSE, $object = FALSE)
	{
		if ($sql == '') return FALSE;

		if ( ! $this->connected) $this->connect();
		$object = (bool) ($object == FALSE) ? $this->config['object'] : $object;

		// Compile binds if needed
		if ($binds !== FALSE)
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		$this->last_query = $sql;
		return $this->driver->query($sql, $object);
	}

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * Several syntax types are supported for calling this method:
	 * - list of strings:        ('foo', 'bar', 'baz')
	 * - comma separated string: ('foo, bar, baz')
	 * - array of strings:       (array('foo', 'bar', 'baz'))
	 *
	 * @access  public
	 * @param   mixed
	 * @return  object
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

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @access  public
	 * @param   boolean
	 * @return  object
	 */
	public function distinct($sql = TRUE)
	{
		$this->distict = (bool) $sql;

		return $this;
	}

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @access	public
	 * @param	mixed	can be a string or array
	 * @return	object
	 */
	public function from($sql)
	{
		foreach((array) $sql as $val)
		{
			if (($val = trim($val)) == '') continue;

			$this->from[] = $val;
		}

		return $this;
	}

	/**
	 * Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access public
	 * @param  mixed
	 * @param  mixed
	 * @param  boolean
	 * @return object
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

	/**
	 * OR Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access public
	 * @param  mixed
	 * @param  mixed
	 * @param  boolean
	 * @return object
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

	/**
	 * Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	public function like($field, $match = '')
	{
		$this->like = array_merge($this->like, $this->driver->like($field, $match, 'AND ', count($this->like)));
		return $this;
	}

	/**
	 * OR Like
	 *
	 * Generates a %LIKE% portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	object
	 */
	public function orlike($field, $match = '')
	{
		$this->like = array_merge($this->like, $this->driver->like($field, $match, 'OR ', count($this->like)));
		return $this;
	}

	/**
	 * GROUP BY
	 *
	 * @access	public
	 * @param	string
	 * @return	object
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

	/**
	 * Sets the HAVING value
	 *
	 * Separates multiple calls with AND
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function having($key, $value = '')
	{
	    $this->like = array_merge($this->like, $this->driver->having($key, $value, 'AND'));
        return $this;
	}

	/**
	 * Sets the OR HAVING value
	 *
	 * Separates multiple calls with OR
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function orhaving($key, $value = '')
	{
		$this->like = array_merge($this->like, $this->driver->having($key, $value, 'OR'));
        return $this;
	}

	/**
	 * Sets the ORDER BY value
	 *
	 * @access	public
	 * @param	string
	 * @param	string	direction: asc or desc
	 * @return	object
	 */
	public function orderby($orderby, $direction = '')
	{
		$direction = strtoupper(trim($direction));

		if ($direction != '')
		{
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()'))) ? ' '.$direction : ' ASC';
		}

		$this->orderby[] = $orderby.$direction;
		return $this;
	}

	/**
	 * Sets the LIMIT value
	 *
	 * @access	public
	 * @param	integer	the limit value
	 * @param	integer	the offset value
	 * @return	object
	 */
	public function limit($value, $offset = FALSE)
	{
		$this->limit  = (int) $value;
		$this->offset = (int) $offset;

		return $this;
	}

	/**
	 * Sets the OFFSET value
	 *
	 * @access	public
	 * @param	integer	the offset value
	 * @return	object
	 */
	public function offset($value)
	{
		$this->offset = (int) $value;
		return $this;
	}

	/**
	 * The 'set' function. Allows key/value pairs to be set for inserting or updating
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	object
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

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @access	public
	 * @param	string	the limit clause
	 * @param	string	the offset clause
	 * @return	object
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

		$start = microtime(TRUE);
		$result = $this->query($sql);
		$stop = microtime(TRUE);

		if ($this->config['benchmark'] == TRUE)
		{
			// Benchmark the query
			self::$benchmarks[] = array('query' => $sql, 'time' => $stop - $start);
		}

		$this->reset_select();
		$this->last_query = $sql;

		return $result;
	}

	/**
	 * GetWhere
	 *
	 * Allows the where clause, limit and offset to be added directly
	 *
	 * @access	public
	 * @param	string	the where clause
	 * @param	string	the limit clause
	 * @param	string	the offset clause
	 * @return	object
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

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of insert values
	 * @return	object
	 */
	public function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == FALSE)
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);

			$table = $this->from[0];
		}

		$sql = $this->driver->insert($this->config['table_prefix'].$table, array_keys($this->set), array_values($this->set));

		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Update
	 *
	 * Compiles an update string and runs the query
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	array	an associative array of update values
	 * @param	mixed	the where clause
	 * @return	object
	 */
	public function update($table = '', $set = NULL, $where = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == FALSE)
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);

			$table = $this->from[0];
		}
		$this->where = $where;
		$sql = $this->driver->update($this->config['table_prefix'].$table, $this->set, $this->where);

		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @access	public
	 * @param	string	the table to retrieve the results from
	 * @param	mixed	the where clause
	 * @return	object
	 */
	public function delete($table = '', $where = '')
	{
		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);

			$table = $this->from[0];
		}

		if ($where != '')
		{
			$this->where($where);
		}

		if (count($this->where) < 1)
			return (($this->db_debug) ? $this->display_error('db_del_must_use_where') : FALSE);

		$sql = $this->driver->delete($this->config['table_prefix'].$table, $this->where);

		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Last query
	 *
	 * @access	public
	 * @return	string
	 */
	public function last_query()
	{
	   return $this->last_query;
	}

	/**
	 * Count Records
	 *
	 * Count table records by using active record conditions
	 *
	 * @access	public
	 * @param	string	name of table
	 * @return	string
	 */
	public function count_records($table = FALSE)
	{
		if (count($this->from) < 1)
		{
			if ($table == FALSE)
				return FALSE;

			$this->from($table);
		}

		$this->select('COUNT(*)');
		$query  = $this->get();
		$result = array_shift($query->row_array());
		// No one likes a mess
		$query->free_result();

		return $result;
	}

	/**
	 * Resets the SQL values, called by get()
	 *
	 * @access  private
	 * @return  void
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

	/**
	 * Resets the SQL 'write' values, called by insert() and update()
	 *
	 * @access  private
	 * @return  void
	 */
	private function reset_write()
	{
		$this->set   = array();
		$this->from  = array();
		$this->where = array();
	}

	/**
	* Returns an array of table names
	*
	* @access      public
	* @return      array
	*/
	public function list_tables()
	{
		if (!$this->connected) $this->driver->connect($this->config);

		return $this->driver->list_tables();
	}

	/**
	* Determine if a particular table exists
	* 
	* @access      public
	* @return      boolean
	*/
	public function table_exists($table_name)
	{
		return (in_array($table_name, $this->list_tables()));
	}

	/**
	 * Compile Bindings
	 *
	 * @access	public
	 * @param	string	the sql statement
	 * @param	array	an array of bind data
	 * @return	string
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

} // End Database Class