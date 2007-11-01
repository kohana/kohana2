<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Database MySQL Driver
 *
 * @category    Database
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/database.html
 */
class Database_Mysql_Driver implements Database_Driver {

	// Database connection link
	protected $link;
	protected $db_config;

	public function __construct($config)
	{
		$this->db_config = $config;

		Log::add('debug', 'MySQL Database Driver Initialized');
	}

	/**
	 * Connect to the database
	 *
	 * @access  public
	 * @param   array  config array
	 * @return  bool
	 */
	public function connect($config)
	{
		// Import the connect variables
		extract($config['connection']);

		// Persistent connections enabled?
		$connect = ($config['persistent'] == TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		// Build the connection info
		$host = (isset($host)) ? $host : $socket;
		$port = (isset($port)) ? ':'.$port : '';

		// Make the connection and select the database
		if (($this->link = $connect($host.$port, $user, $pass)) AND mysql_select_db($database, $this->link))
		{
			if ($charset = $config['character_set'])
			{
				$this->set_charset($charset);
			}

			return $this->link;
		}

		return FALSE;
	}

	/**
	 * Perform a query
	 *
	 * @access  public
	 * @param   string  SQL statement
	 * @return  int
	 */
	public function query($sql)
	{
		return new Mysql_Result(mysql_query($sql, $this->link), $this->link, $this->db_config['object'], $sql);
	}

	public function delete($table, $where)
	{
		return 'DELETE FROM '.$this->escape_table($table).' WHERE '.implode(' ', $where);
	}

	public function update($table, $values, $where)
	{
		foreach($values as $key => $val)
		{
			$valstr[] = $this->escape_column($key)." = ".$val;
		}
		return 'UPDATE '.$this->escape_table($table).' SET '.implode(', ', $valstr).' WHERE '.implode(' AND ',$where);
	}

	public function set_charset($charset)
	{
		$this->query('SET NAMES '.$this->escape_str($charset));
	}

	public function escape_table($table)
	{
		return str_replace('.', '`.`', $table);
	}

	public function escape_column($column)
	{
		if (strtolower($column) == 'count(*)')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column))
		{
			if (stripos($column, ' AS ') !== FALSE)
			{
				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			if (strpos($column, '.') !== FALSE)
			{
				$column = str_replace('.', '`.`', $column);
			}

			return '`'.$column.'`';
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++)
		{
			// The column is always last
			if ($i == ($c - 1))
			{
				$column .= '`'.$parts[$i].'`';
			}
			else // otherwise, it's a modifier
			{
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	public function where($key, $value, $type, $num_wheres, $quote)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		$wheres = array();
		foreach ($key as $k => $v)
		{
			$prefix = ($num_wheres++ == 0) ? '' : $type;

			if ($quote === -1)
			{
				$v = '';
			}
			else
			{
				if ($v === NULL)
				{
					if ( ! $this->has_operator($k))
					{
						$k .= ' IS';
					}

					$v = ' NULL';
				}
				elseif (is_bool($v))
				{
					if ( ! $this->has_operator($k))
					{
						$k .= ' =';
					}

					$v = ($v == TRUE) ? ' 1' : ' 0';
				}
				else
				{
					if ( ! $this->has_operator($k))
					{
						$k = $this->escape_column($k).' =';
					}
					else
					{
						preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $k, $matches);
						$k = $this->escape_column(trim($matches[1])).' '.trim($matches[2]);
					}

					$v = ' '.(($quote == TRUE) ? $this->escape($v) : $v);
				}
			}
			$wheres[] = $prefix.$k.$v;
		}
		return $wheres;
	}

	public function like($field, $match = '', $type = 'AND ', $num_likes)
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}

		$likes = array();
		foreach ($field as $k => $v)
		{
			$prefix = ($num_likes++ == 0) ? '' : $type;

			$v = (substr($v, 0, 1) == '%' OR substr($v, (strlen($v)-1), 1) == '%') ? $this->escape_str($v) : '%'.$this->escape_str($v).'%';

			$likes[] = $prefix." ".$k." LIKE '".$v . "'";
		}
		return $likes;
	}

	public function insert($table, $keys, $values)
	{
		// Escape the column names
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->escape_column($value);
		}
		return 'INSERT INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	public function limit($limit, $offset = 0)
	{
		return 'LIMIT '.$offset.', '.$limit;
	}

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @access  private
	 * @return  string
	 */
	public function compile_select($database)
	{
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= implode(', ', $database['from']);
		}

		if (count($database['join']) > 0)
		{
			$sql .= ' '.implode("\n", $database['join']);
		}

		if (count($database['where']) > 0 OR count($database['like']) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['like']) > 0)
		{
			if (count($database['where']) > 0)
			{
				$sql .= ' ';
			}

			$sql .= implode("\n", $database['like']);
		}

		if (count($database['groupby']) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit']))
		{
			$sql .= "\n";
			$sql .= $this->limit($database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function has_operator($str)
	{
		return (bool) preg_match('/[<>!=]|\sIS(?:\s+NULL)\b/i', trim($str));
	}

	public function escape($str)
	{
		switch (gettype($str))
		{
			case 'string':
				$str = "'".$this->escape_str($str)."'";
				break;
			case 'boolean':
				$str = (int) $str;
			break;
			default:
				$str = ($str === NULL) ? 'NULL' : $str;
			break;
		}

		return (string) $str;
	}

	/**
	* Escape String
	*
	* @access      public
	* @param       string
	* @return      string
	*/
	public function escape_str($str)
	{
		is_resource($this->link) or $this->connect($this->db_config);

		return mysql_real_escape_string($str, $this->link);
	}

	/**
 	* List table query
 	*
 	* Generates a platform-specific query string so that the table names can be fetched
 	*
 	* @access      private
	* @return      string
 	*/
	public function list_tables()
	{
		$sql    = 'SHOW TABLES FROM `'.$this->db_config['connection']['database'].'`';
		$result = $this->query($sql)->result(FALSE, MYSQL_ASSOC);

		$retval = array();
		foreach($result as $row)
		{
			$retval[] = current($row);
		}

		return $retval;
	}

	public function show_error()
	{
		return mysql_error($this->link);
	}

	public function field_data($table)
	{
		$query  = mysql_query('SELECT * FROM '.$this->escape_table($table).' LIMIT 1', $this->link);
		$fields = mysql_num_fields($query);
		$table  = array();

		for ($i=0; $i < $fields; $i++)
		{
			$table[$i]['type']  = mysql_field_type($query, $i);
			$table[$i]['name']  = mysql_field_name($query, $i);
			$table[$i]['len']   = mysql_field_len($query, $i);
			$table[$i]['flags'] = mysql_field_flags($query, $i);
		}

		return $table;
	}

} // End Database_Mysql Class

// Iterator behavior taken from:
// http://www.php.net/~helly/php/ext/spl/interfaceIterator.html#20bbada11975a50f67f09c89b701b62a
class Mysql_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	// Result resource
	protected $result = NULL;

	// Total rows and current row
	protected $total_rows  = FALSE;
	protected $current_row = FALSE;

	// Insert id
	protected $insert_id = FALSE;

	// Data fetching types
	protected $fetch_type  = 'mysql_fetch_object';
	protected $return_type = MYSQL_ASSOC;

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
	public function __construct($result, $link, $object = TRUE, $sql)
	{
		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result))
		{
			$this->current_row = 0;
			$this->total_rows  = mysql_num_rows($this->result);
		}
		elseif (is_bool($result))
		{
			if ($result == FALSE)
			{
				// SQL error
				throw new Kohana_Database_Exception('database.error', mysql_error().' - '.$sql);
			}
			else
			{
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = mysql_insert_id($link);
				$this->total_rows = mysql_affected_rows($link);
			}
		}

		// Set result type
		$this->result($object);
	}

	/**
	 * Magic __destruct function, frees the result
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct()
	{
		if (is_resource($this->result))
		{
			mysql_free_result($this->result);
		}
	}

	public function result($object = TRUE, $type = MYSQL_ASSOC)
	{
		$this->fetch_type = (bool) $object ? 'mysql_fetch_object' : 'mysql_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE: The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'mysql_fetch_object')
		{
			$this->return_type = class_exists($type, FALSE) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}
	// End Interface

	public function result_array($object = TRUE, $type = MYSQL_ASSOC)
	{
		$rows = array();

		switch($object)
		{
			case NULL:  $fetch = $this->fetch_type;    break;
			case TRUE:  $fetch = 'mysql_fetch_object'; break;
			default:    $fetch = 'mysql_fetch_array';  break;
		}

		// This check has to be outside the previous switch(), because we do not
		// know the state of $fetch when $object = NULL
		// NOTE: The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($fetch == 'mysql_fetch_object')
		{
			$type = class_exists($type, FALSE) ? $type : 'stdClass';
		}

		while ($row = $fetch($this->result, $type))
		{
			$rows[] = $row;
		}
		return $rows;
	}

	// Interface: Database_Result
	public function insert_id()
	{
		return $this->insert_id;
	}
	// End Interface

	// Interface: Countable
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
		// Go to the offset
		mysql_data_seek($this->result, $offset);

		// Return the row
		$fetch = $this->fetch_type;
		return $fetch($this->result, $this->return_type);
	}

	public function offsetSet($offset, $value)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	public function offsetUnset($offset)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
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