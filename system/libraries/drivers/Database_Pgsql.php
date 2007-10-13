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
 * Database Postgre Driver
 *
 * @category    Database
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/database.html
 */
class Database_Pgsql implements Database_Driver {

	// Database connection link
	private $link;
	private $db_config;

	public function __construct($config)
	{
		$this->db_config = $config;

		Log::add('debug', 'PgSQL Database Driver Initialized');
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
		$connect = ($config['persistent'] == TRUE) ? 'pg_pconnect' : 'pg_connect';

		// Build the connection info
		$port = (isset($port)) ? 'port=\''.$port.'\'' : '';
		$host = (isset($host)) ? 'host=\''.$host.'\' '.$port : ''; // if no host, connect with the socket
		
		$connection_string = $host.' dbname=\''.$database.'\' user=\''.$user.'\' pass=\''.$pass.'\'';
		// Make the connection and select the database
		if ($this->link = $connect($connection_string))
		{
			if ($charset = $config['character_set'])
			{
				echo $this->set_charset($charset);
			}

			return TRUE;
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
		return new Pgsql_Result(pg_query($this->link, $sql), $this->link, $this->db_config['object'], $sql);
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
		return 'UPDATE '.$this->escape_table($table).' SET '.implode(', ', $valstr).' WHERE '.implode(' AND ',$this->where($where, NULL, 'AND', 0, TRUE));
	}

	public function set_charset($charset)
	{
		$this->query('SET client_encoding TO '.pg_escape_string($this->link, $charset));
	}

	public function escape_table($table)
	{
		return str_replace('.', '`.`', $table);
	}

	public function escape_column($column)
	{
		return '\''.$column.'\'';
	}

	public function where($key, $value, $type, $num_wheres, $quote)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		$wheres = array();
		$count = 1;
		foreach ($key as $k => $v)
		{

			$prefix = (($num_wheres > 0) or ($count++ > 1)) ? $type : '';

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
					   $k .= ' =';
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
			$prefix = (count($num_likes) == 0) ? '' : $type;

			$v = (substr($v, 0, 1) == '%' OR substr($v, (strlen($v)-1), 1) == '%') ? $this->escape_str($v) : '%'.$this->escape_str($v).'%';

			$likes[] = $prefix." ".$k." LIKE '".$v . "'";
		}
		return $likes;
	}

	public function insert($table, $keys, $values)
	{
		return 'INSERT INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	public function limit($limit, $offset = 0)
	{
		return 'LIMIT '.$limit.' OFFSET '.$offset;
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
		$sql  = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= implode(', ', $database['from']);
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

			if ($database['order'] !== FALSE)
			{
				$sql .= ($database['order'] == 'desc') ? ' DESC' : ' ASC';
			}
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
		return (bool) preg_match('/!?[=<>]|\sIS\s/i', trim($str));
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
		if ( ! is_resource($this->link))
		{
			$this->connect($this->db_config);
		}

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
		$sql = 'SHOW TABLES FROM `'.$this->db_config['connection']['database'].'`';
		$query = $this->query($sql);
		$query = $query->result();
		
		$retval = array();
		foreach($query as $row)
		{
			$column = 'Tables_in_'.$this->db_config['connection']['database'];
			$retval[] = $row->$column;
		}
		
		return $retval;
	}

	function show_error()
	{
		return pg_last_error($this->link);
	}
} // End Database_Pgsql Class

class Pgsql_Result implements Database_Result, Iterator
{
	private $link      = FALSE;
	private $result    = FALSE;
	private $insert_id = NULL;
	private $num_rows  = 0;
	private $rows      = array();
	private $object    = TRUE;

	public function __construct($result, $link, $object = TRUE, $sql)
	{
		$this->object = (bool) $object;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result))
		{
			$this->result   = $result;
			$this->num_rows = pg_num_rows($this->result);
		}
		else
		{
			if ($result == FALSE)
			{
				throw new Kohana_Exception('database.error', pg_last_error($this->link).' - '.$sql);
			}
			else if ($result == TRUE) // Its an DELETE, INSERT, REPLACE, or UPDATE query
			{
				//$this->insert_id = mysql_insert_id($link);
				$this->num_rows  = pg_affected_rows($link);
			}
		}
	}

	public function result($object = TRUE, $type = PGSQL_ASSOC)
	{
		$fetch = ($object == TRUE) ? 'pg_fetch_object' : 'pg_fetch_array';
		$type  = ($object == TRUE) ? 'stdClass' : $type;

		while ($row = $fetch($this->result, $type))
		{
			$this->rows[] = $row;
		}

		return $this;
	}

	public function num_rows()
	{
		return $this->num_rows;
	}

	public function get_rows()
	{
		return $this->rows;
	}

	public function insert_id()
	{
		return $this->insert_id;
	}

	public function current()
	{
		return current($this->rows);
	}

	public function next()
	{
		return next($this->rows);
	}

	public function key()
	{
		return key($this->rows);
	}

	public function valid()
	{
		return ($this->current() !== FALSE);
	}

	public function rewind()
	{
		reset($this->rows);
	}
} // End Pgsql_Result Class