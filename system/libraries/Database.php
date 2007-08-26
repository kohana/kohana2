<?php defined('SYSPATH') or die('No direct script access.');

class Database_Core {

	// The PDO database connection
	public $pdo;

	// Character set of the database
	private $config  = array
	(
		'connection'    => '',
		'persistent'    => FALSE,
		'show_errors'   => TRUE,
		'character_set' => 'utf-8',
		'table_prefix'  => ''
	);

	// Un-compiled parts of the SQL query
	private $_select   = array();
	private $_from     = array();
	private $_join     = array();
	private $_where    = array();
	private $_like     = array();
	private $_orderby  = array();
	private $_groupby  = array();
	private $_having   = array();
	private $_distinct = FALSE;
	private $_limit    = FALSE;
	private $_offset   = FALSE;

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

		// The database may contain slash characters when read as a path
		$database = trim($database, '/');

		// Set the PDO configuration
		$config = array
		(
			PDO::ATTR_PERSISTENT => (bool) $this->config['persistent']
		);

		// Initialize the PDO connection
		$this->pdo = new PDO($type.':host='.$host.';dbname='.$database, $user, $pass, $config);
	}

	public function select($sql = '*')
	{
		// Syntax: select('foo', 'bar', 'baz')
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}

		foreach($sql as $val)
		{
			if (($val = trim($val)) == '') continue;

			$this->_select[] = $val;
		}

		return $this;
	}

	public function distinct($sql = TRUE)
	{
		$this->_distict = (bool) $sql;

		return $this;
	}

	public function from($sql)
	{
		foreach((array) $sql as $val)
		{
			if (($val = trim($val)) == '') continue;

			$this->_from[] = $val;
		}

		return $this;
	}


} // End Database Class

/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Active Record Class
 *
 * This is the platform-independent base Active Record implementation class.
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Database
 * @author      Rick Ellis, Kohana Development Team
 */
class Core_DB_active_record extends stdClass {

	var $ar_select   = array();
	var $ar_distinct = FALSE;
	var $ar_from     = array();
	var $ar_join     = array();
	var $ar_where    = array();
	var $ar_like     = array();
	var $ar_groupby  = array();
	var $ar_having   = array();
	var $ar_limit    = FALSE;
	var $ar_offset   = FALSE;
	var $ar_order    = FALSE;
	var $ar_orderby  = array();
	var $ar_set      = array();

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	function select($select = '*')
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}

		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				$this->ar_select[] = $val;
			}
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @access	public
	 * @param	bool
	 * @return	object
	 */
	function distinct($val = TRUE)
	{
		$this->ar_distinct = (is_bool($val)) ? $val : TRUE;
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
	function from($from)
	{
		foreach ((array)$from as $val)
		{
			$this->ar_from[] = $this->dbprefix.$val;
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @param	string	the join condition
	 * @param	string	the type of join
	 * @return	object
	 */
	function join($table, $cond, $type = '')
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
		if ($this->dbprefix)
		{
			// First we remove any existing prefixes in the condition to avoid duplicates
			$cond = preg_replace('|('.$this->dbprefix.')([\w\.]+)([\W\s]+)|', "$2$3", $cond);

			// Next we add the prefixes to the condition
			$cond = preg_replace('|([\w\.]+)([\W\s]+)(.+)|', $this->dbprefix . "$1$2" . $this->dbprefix . "$3", $cond);
		}

		$this->ar_join[] = $type.'JOIN '.$this->dbprefix.$table.' ON '.$cond;
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
	function where($key, $value = NULL, $quote = TRUE)
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
	function orwhere($key, $value = NULL, $quote = TRUE)
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
	function _where($key, $value = NULL, $type = 'AND ', $quote = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->ar_where) == 0) ? '' : $type;

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

			$this->ar_where[] = $prefix.$k.$v;
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
	function like($field, $match = '')
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
	function orlike($field, $match = '')
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
	function _like($field, $match = '', $type = 'AND ')
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}

		foreach ($field as $k => $v)
		{
			$prefix = (count($this->ar_like) == 0) ? '' : $type;

			$v = $this->escape_str($v);

			$this->ar_like[] = $prefix." $k LIKE '%{$v}%'";
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
	function groupby($by)
	{
		if ( ! is_array($by))
		{
			$by = explode(',', (string) $by);
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val != '')
				$this->ar_groupby[] = $val;
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
	function having($key, $value = '')
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
	function orhaving($key, $value = '')
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
	function _having($key, $value = '', $type = 'AND')
	{
		$type = trim($type).' ';

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->ar_having) < 1) ? '' : $type;

			if ($v != '')
			{
				$v = ' '.$this->escape($v);
			}

			$this->ar_having[] = $prefix.$k.$v;
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
	function orderby($orderby, $direction = '')
	{
		$direction = strtoupper(trim($direction));

		if ($direction != '')
		{
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()'))) ? " $direction" : " ASC";
		}

		$this->ar_orderby[] = $orderby.$direction;
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
	function limit($value, $offset = '')
	{
		$this->ar_limit = $value;

		if ($offset != '')
		{
			$this->ar_offset = $offset;
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
	function offset($value)
	{
		$this->ar_offset = $value;
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
	function set($key, $value = '')
	{
		$key = $this->_object_to_array($key);

		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$this->ar_set[$k] = $this->escape($v);
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
	function get($table = '', $limit = null, $offset = null)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->_compile_select();

		$result = $this->query($sql);
		$this->_reset_select();
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
	function getwhere($table = '', $where = null, $limit = null, $offset = null)
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

		$sql = $this->_compile_select();

		$result = $this->query($sql);
		$this->_reset_select();
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
	function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->ar_set == FALSE)
		{
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->ar_from[0];
		}

		$sql = $this->_insert($this->dbprefix.$table, array_keys($this->ar_set), array_values($this->ar_set));

		$this->_reset_write();
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
	function update($table = '', $set = NULL, $where = null)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->ar_set == FALSE)
		{
			return ($this->db_debug ? $this->display_error('db_must_use_set') : FALSE);
		}

		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->ar_from[0];
		}

		if ($where != null)
		{
			$this->where($where);
		}

		$sql = $this->_update($this->dbprefix.$table, $this->ar_set, $this->ar_where);

		$this->_reset_write();
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
	function delete($table = '', $where = '')
	{
		if ($table == '')
		{
			if ( ! isset($this->ar_from[0]))
			{
				return ($this->db_debug ? $this->display_error('db_must_set_table') : FALSE);
			}

			$table = $this->ar_from[0];
		}

		if ($where != '')
		{
			$this->where($where);
		}

		if (count($this->ar_where) < 1)
		{
			return (($this->db_debug) ? $this->display_error('db_del_must_use_where') : FALSE);
		}

		$sql = $this->_delete($this->dbprefix.$table, $this->ar_where);

		$this->_reset_write();
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
	function count_records($table = FALSE)
	{
		if (count($this->ar_from) < 1)
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
	 * Use Table - DEPRECATED
	 *
	 * @deprecated	use $this->db->from instead
	 */
	function use_table($table)
	{
		return $this->from($table);
	}

	// --------------------------------------------------------------------

	/**oh
	 * ORDER BY - DEPRECATED
	 *
	 * @deprecated	use $this->db->orderby() instead
	 */
	function order_by($orderby, $direction = '')
	{
		return $this->orderby($orderby, $direction);
	}

	// --------------------------------------------------------------------

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _has_operator($str)
	{
		$str = trim($str);

		return (bool) preg_match('/(\s|<|>|!|=|is |is not)/i', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @access	private
	 * @return	string
	 */
	function _compile_select()
	{
		$sql  = ( ! $this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
		$sql .= (count($this->ar_select) == 0) ? '*' : implode(', ', $this->ar_select);

		if (count($this->ar_from) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= implode(', ', $this->ar_from);
		}

		if (count($this->ar_join) > 0)
		{
			$sql .= "\n";
			$sql .= implode("\n", $this->ar_join);
		}

		if (count($this->ar_where) > 0 OR count($this->ar_like) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $this->ar_where);

		if (count($this->ar_like) > 0)
		{
			if (count($this->ar_where) > 0)
			{
				$sql .= " AND ";
			}

			$sql .= implode("\n", $this->ar_like);
		}

		if (count($this->ar_groupby) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $this->ar_groupby);
		}

		if (count($this->ar_having) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $this->ar_having);
		}

		if (count($this->ar_orderby) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $this->ar_orderby);

			if ($this->ar_order !== FALSE)
			{
				$sql .= ($this->ar_order == 'desc') ? ' DESC' : ' ASC';
			}
		}

		if (is_numeric($this->ar_limit))
		{
			$sql .= "\n";
			$sql = $this->_limit($sql, $this->ar_limit, $this->ar_offset);
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
	function _object_to_array($object)
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
	 * Resets the active record values.  Called by the get() function
	 *
	 * @access	private
	 * @return	void
	 */
	function _reset_select()
	{
		$this->ar_select   = array();
		$this->ar_distinct = FALSE;
		$this->ar_from     = array();
		$this->ar_join     = array();
		$this->ar_where    = array();
		$this->ar_like     = array();
		$this->ar_groupby  = array();
		$this->ar_having   = array();
		$this->ar_limit    = FALSE;
		$this->ar_offset   = FALSE;
		$this->ar_order    = FALSE;
		$this->ar_orderby  = array();
	}

	// --------------------------------------------------------------------

	/**
	 * Resets the active record "write" values.
	 *
	 * Called by the insert() or update() functions
	 *
	 * @access	private
	 * @return	void
	 */
	function _reset_write()
	{
		$this->ar_set   = array();
		$this->ar_from  = array();
		$this->ar_where = array();
	}

}
