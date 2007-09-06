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
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
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

	// Character set of the database
	private $config  = array();

	// Un-compiled parts of the SQL query
	private $_select    = array();
	private $_set       = array();
	private $_from      = array();
	private $_join      = array();
	private $_where     = array();
	private $_like      = array();
	private $_orderby   = array();
	private $_groupby   = array();
	private $_having    = array();
	private $_distinct  = FALSE;
	private $_limit     = FALSE;
	private $_offset    = FALSE;
	private $_connected = FALSE;

	public function __construct($config = array())
	{
		if ($config == FALSE)
		{
			// Find the active group
			$config = Config::item('database._active');
			// Load the active group
			$config = Config::item('database.'.$config);
		}
		elseif (is_string($config))
		{
			// This checks to see if the config is DSN string, or a config group name
			$config = (strpos($config, '://') == FALSE) ? Config::item('database.'.$config) : array('connection' => $config);;
		}

		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);

		// Parse the DSN into an array and validate it's length
		(count($connection = @parse_url($this->config['connection'])) === 5) or trigger_error
		(
			'Invalid DSN used for database connection: <strong>'.$this->config['connection'].'</strong>',
			E_USER_ERROR
		);

		// Turn the DSN into local variables
		// NOTE: This step has to be done, because the order is defined by parse_url
		list($type, $host, $user, $pass,$database) = array_values($connection);
		$connection_info = array('type' => $type, 
								'host' => $host, 
								'user' => $user, 
								'pass' => $pass,
								'database' =>  trim($database, '/'));
		$this->config = array_merge($this->config, $connection_info);
		
		// The database may contain slash characters when read as a path
		$database = trim($database, '/');

		$driver = 'Database_'.ucfirst(strtolower($this->config['driver']));

		require Kohana::find_file('libraries', 'drivers/'.$driver, TRUE);

		$this->driver = new $driver();

		$implements = class_implements($this->driver);

		if ( ! isset($implements['Database_Driver']))
		{
			/**
			 * @todo This should be an i18n error
			 */
			trigger_error('Database drivers must use the Database_Driver interface.');
		}
		
		$this->connect();
		Log::add('debug', 'Database Class Initialized');
		
		// We only connect if a query will be run
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
		if ($this->driver->connect($this->config))
		{
			// Do we need to do anything?
		}
		else
		{
			/**
			 * @todo This should be an i18n error
			 */
			trigger_error('Database connection failed.');
		}
	}
	
	public function query($sql = '', $object = '')
	{
		if ($sql == '')
			return FALSE;
			
		if (!$this->_connected) $this->connect();
		
		return $this->driver->query($sql, ($object == '') ? $this->config['object'] : $object);
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

			$this->_select[] = $val;
		}

		return $this;
	}

	// --------------------------------------------------------------------

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
		$this->_distict = (bool) $sql;

		return $this;
	}

	// --------------------------------------------------------------------

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

			$this->_from[] = $val;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @access  public
	 * @param   string
	 * @param   string  the join condition
	 * @param   string  the type of join
	 * @return  object
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

		// If a DB prefix is used we might need to add it to the column names
		if ($this->config['table_prefix'])
		{
			// First we remove any existing prefixes in the condition to avoid duplicates
			$cond = preg_replace('|('.$this->config['table_prefix'].')([\w\.]+)([\W\s]+)|', '$2$3', $cond);

			// Next we add the prefixes to the condition
			$cond = preg_replace('|([\w\.]+)([\W\s]+)(.+)|', $this->config['table_prefix'].'$1$2'.$this->config['table_prefix'].'$3', $cond);
		}

		$this->_join[] = $type.'JOIN '.$this->config['table_prefix'].$table.' ON '.$cond;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with AND
	 *
	 * @access public
	 * @param  mixed
	 * @param  mixed
	 * @param  bool
	 * @return object
	 */
	public function where($key, $value = NULL, $quote = TRUE)
	{
		if (func_num_args() < 2 AND ! is_array($key))
		{
			$quote = -1;
		}

		return $this->_where($key, $value, 'AND ', $quote);
	}

	// --------------------------------------------------------------------

	/**
	 * OR Where
	 *
	 * Generates the WHERE portion of the query. Separates
	 * multiple calls with OR
	 *
	 * @access public
	 * @param  mixed
	 * @param  mixed
	 * @param  bool
	 * @return object
	 */
	public function orwhere($key, $value = NULL, $quote = TRUE)
	{
		if (func_num_args() < 2 AND ! is_array($key))
		{
			$quote = -1;
		}

		return $this->_where($key, $value, 'OR ', $quote);
	}

	// --------------------------------------------------------------------

	/**
	 * Where
	 *
	 * Called by where() or orwhere()
	 *
	 * @access private
	 * @param  mixed
	 * @param  mixed
	 * @param  string
	 * @param  bool
	 * @return object
	 */
	public function _where($key, $value = NULL, $type = 'AND ', $quote = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->_where) == 0) ? '' : $type;

			if ($quote === -1)
			{
				$v = '';
			}
			else
			{
				if ($v === NULL)
				{
					if ( ! $this->_has_operator($k))
					{
						$k .= ' IS';
					}

					$v = ' NULL';
				}
				elseif ($v === FALSE OR $v === TRUE)
				{
					if ( ! $this->_has_operator($k))
					{
						$k .= ' =';
					}

					$v = ($v == TRUE) ? ' 1' : ' 0';
				}
				else
				{
					if ( ! $this->_has_operator($k))
					{
						$k .= ' =';
					}

					$v = ' '.(($quote == TRUE) ? $this->escape($v) : $v);
				}
			}

			$this->_where[] = $prefix.$k.$v;
		}
		return $this;
	}

	// --------------------------------------------------------------------

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
		return $this->_like($field, $match, 'AND ');
	}

	// --------------------------------------------------------------------

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
		return $this->_like($field, $match, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Like
	 *
	 * Called by like() or orlike()
	 *
	 * @access	private
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	public function _like($field, $match = '', $type = 'AND ')
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}

		foreach ($field as $k => $v)
		{
			$prefix = (count($this->_like) == 0) ? '' : $type;

			$v = $this->escape_str($v);

			$this->_like[] = $prefix." $k LIKE '%{$v}%'";
		}
		return $this;
	}

	// --------------------------------------------------------------------

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
				$this->_groupby[] = $val;
		}
		return $this;
	}

	// --------------------------------------------------------------------

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
		return $this->_having($key, $value, 'AND');
	}

	// --------------------------------------------------------------------

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
		return $this->_having($key, $value, 'OR');
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the HAVING values
	 *
	 * Called by having() or orhaving()
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function _having($key, $value = '', $type = 'AND')
	{
		$type = trim($type).' ';

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->_having) < 1) ? '' : $type;

			if ($v != '')
			{
				$v = ' '.$this->escape($v);
			}

			$this->_having[] = $prefix.$k.$v;
		}
		return $this;
	}

	// --------------------------------------------------------------------

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
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()'))) ? " $direction" : " ASC";
		}

		$this->_orderby[] = $orderby.$direction;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the LIMIT value
	 *
	 * @access	public
	 * @param	integer	the limit value
	 * @param	integer	the offset value
	 * @return	object
	 */
	public function limit($value, $offset = '')
	{
		$this->_limit = $value;

		if ($offset != '')
		{
			$this->_offset = $offset;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the OFFSET value
	 *
	 * @access	public
	 * @param	integer	the offset value
	 * @return	object
	 */
	public function offset($value)
	{
		$this->_offset = $value;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * The "set" function.  Allows key/value pairs to be set for inserting or updating
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	object
	 */
	public function set($key, $value = '')
	{
		$key = $this->object_to_array($key);

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$this->_set[$k] = $this->escape($v);
		}

		return $this;
	}

	// --------------------------------------------------------------------

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
	public function get($table = '', $limit = null, $offset = null)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select($this);

		$result = $this->query($sql);
		$this->reset_select();
		return $result;
	}

	// --------------------------------------------------------------------

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
	public function getwhere($table = '', $where = null, $limit = null, $offset = null)
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

		$sql = $this->driver->compile_select($this);

		$result = $this->query($sql);
		$this->reset_select();
		return $result;
	}

	// --------------------------------------------------------------------

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

		if ($this->_set == FALSE)
		{
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);
		}

		if ($table == '')
		{
			if ( ! isset($this->_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->_from[0];
		}

		$sql = $this->driver->insert($this->config['table_prefix'].$table, array_keys($this->_set), array_values($this->_set));

		$this->reset_write();
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

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
	public function update($table = '', $set = NULL, $where = null)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->_set == FALSE)
		{
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);
		}

		if ($table == '')
		{
			if ( ! isset($this->_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->_from[0];
		}

		if ($where != null)
		{
			$this->where($where);
		}

		$sql = $this->driver->update($this->config['table_prefix'].$table, $this->_set, $this->_where);

		$this->reset_write();
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

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
			if ( ! isset($this->_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->_from[0];
		}

		if ($where != '')
		{
			$this->where($where);
		}

		if (count($this->_where) < 1)
		{
			return (($this->db_debug) ? $this->display_error('db_del_must_use_where') : FALSE);
		}

		$sql = $this->driver->delete($this->config['table_prefix'].$table, $this->_where);

		$this->reset_write();
		return $this->query($sql);
	}

	// --------------------------------------------------------------------

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
		if (count($this->_from) < 1)
		{
			if ($table == FALSE)
			{
				return FALSE;
			}
			else
			{
				$this->from($table);
			}
		}

		$this->select('COUNT(*)');
		$query  = $this->get();
		$result = array_shift($query->row_array());
		// No one likes a mess
		$query->free_result();

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @access  private
	 * @param   string
	 * @return  boolean
	 */
	public function _has_operator($str)
	{
		return (bool) preg_match('/[\s=<>!]|is /i', trim($str));
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @access  private
	 * @return  string
	 */
	public function _compile_select()
	{
		$sql  = ($this->_distinct == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($this->_select) > 0) ? implode(', ', $this->_select) : '*';

		if (count($this->_from) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= implode(', ', $this->_from);
		}

		if (count($this->_join) > 0)
		{
			$sql .= "\n";
			$sql .= implode("\n", $this->_join);
		}

		if (count($this->_where) > 0 OR count($this->_like) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $this->_where);

		if (count($this->_like) > 0)
		{
			if (count($this->_where) > 0)
			{
				$sql .= " AND ";
			}

			$sql .= implode("\n", $this->_like);
		}

		if (count($this->_groupby) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $this->_groupby);
		}

		if (count($this->_having) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $this->_having);
		}

		if (count($this->_orderby) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $this->_orderby);

			if ($this->_order !== FALSE)
			{
				$sql .= ($this->_order == 'desc') ? ' DESC' : ' ASC';
			}
		}

		if (is_numeric($this->_limit))
		{
			$sql .= "\n";
			$sql = $this->_limit($sql, $this->_limit, $this->_offset);
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @access	public
	 * @param	object
	 * @return	array
	 */
	private function object_to_array($object)
	{
		if ( ! is_object($object))
		{
			return $object;
		}

		$array = array();
		foreach (get_object_vars($object) as $key => $val)
		{
			if ( ! is_object($val) AND ! is_array($val))
			{
				$array[$key] = $val;
			}
		}

		return $array;
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the SQL values, called by get()
	 *
	 * @access  private
	 * @return  void
	 */
	private function reset_select()
	{
		$this->_select   = array();
		$this->_from     = array();
		$this->_join     = array();
		$this->_where    = array();
		$this->_like     = array();
		$this->_orderby  = array();
		$this->_groupby  = array();
		$this->_having   = array();
		$this->_distinct = FALSE;
		$this->_limit    = FALSE;
		$this->_offset   = FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the SQL "write" values, called by insert() and update()
	 *
	 * @access  private
	 * @return  void
	 */
	private function reset_write()
	{
		$this->_set   = array();
		$this->_from  = array();
		$this->_where = array();
	}

} // End Database Class