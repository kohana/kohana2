<?php defined('SYSPATH') or die('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * $Id$
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Database MySQL Driver
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Database
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/database.html
 */
class Database_Mysql implements Database_Driver {

	// Database connection link
	private $link;
    private $db_config;

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

		// Make the connection and select the database
		if (($this->link = $connect($host, $user, $pass)) AND mysql_select_db($database, $this->link))
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
	public function query($sql, $object = TRUE)
	{
		return new Mysql_Result(mysql_query($sql, $this->link), $this->link, $object);
	}

	public function delete($table, $where)
	{
    	return 'DELETE FROM '.$this->escape_table($table).' WHERE '.implode(' ', $where);
	}

	public function update($table, $where)
	{
		return 'UPDATE '.$this->escape_table($table).' WHERE '.implode(' ',$where);
	}

	public function set_charset($charset)
	{
		$this->query('SET NAMES '.mysql_real_escape_string($charset));
	}

	public function escape_table($table)
	{
		return str_replace('.', '`.`', $table);
	}

	public function escape_column($column)
	{
		return '`'.$column.'`';
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
			$prefix = ($num_wheres == 0) ? '' : $type;

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
				elseif ($v === FALSE OR $v === TRUE)
				{
					if ( ! $this->has_operator($k))
					{
						$k .= ' =';
					}

					$v = ($v == TRUE) ? ' 1' : ' 0';
				}
				elseif ($this->has_operator($v))
				{
				    $k = '';
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
			$sql = $database->limit($sql, $database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function has_operator($str)
	{
		return (bool) preg_match('/[\s=<>!]|is /i', trim($str));
	}

	public function escape($str)
	{
		switch (gettype($str))
		{
			case 'string':
				$str = "'".$this->escape_str($str)."'";
 			break;
			case 'boolean':
				$str = ($str === FALSE) ? 0 : 1;
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
} // End Database MySQL Driver

class Mysql_Result implements Database_Result, Iterator
{
	private $link      = FALSE;
	private $result    = FALSE;
	private $insert_id = NULL;
	private $num_rows  = 0;
	private $rows      = array();
	private $object    = TRUE;

	public function __construct($result, $link, $object = TRUE)
	{
		$this->object = (bool) $object;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result))
		{
			$this->result = $result;
		}
		else
		{
			if ($result == FALSE)
			{
				throw new Kohana_Exception('database.error', mysql_error());
			}
			else if ($result == TRUE) // Its an DELETE, INSERT, REPLACE, or UPDATE query
			{
				$this->insert_id = mysql_insert_id($link);
				$this->num_rows  = mysql_affected_rows($link);
			}
		}
	}

	public function result()
	{
		$fetch = ($this->object == TRUE) ? 'mysql_fetch_object' : 'mysql_fetch_array';

		while ($row = $fetch($this->result))
		{
			$this->rows[] = $row;
		}

		$this->num_rows = mysql_num_rows($this->result);
	}

	public function num_rows()
	{
		return $this->num_rows;
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
}
?>