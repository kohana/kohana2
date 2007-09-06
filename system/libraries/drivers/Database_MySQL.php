<?php defined('SYSPATH') or die('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
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
class Database_MySQL implements Database_Driver {
	
	public function __construct()
	{
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
		if ($link = mysql_connect($config['host'], $config['user'], $config['pass']))
		{
			$database = mysql_select_db($config['database'], $link);
			
			if (!$database)
				return FALSE;
			else
			{
				$this->set_character_set($config['character_set']);
				return TRUE;
			}
		}
		else
			return FALSE;				
	}
	
	/**
	 * Perform a query
	 *
	 * @access  public
	 * @param   string  SQL statement
	 * @return  int
	 */
	public function query($sql, $object)
	{
		$result = mysql_query($sql);
		
		if ($result)
			return FALSE;
			
		// Find out the type of the query
		if (gettype($result) == 'boolean') // It's an update, etc
		{
			return $result;
		}
		else // It's a SELECT type
		{
			$result_function = ($object) ? 'mysql_fetch_object' : 'mysql_fetch_array';
		
			$rows = array();
			while ($row = $result_function($result))
			{
				$rows[] = $row;
			}
			return $rows;
		}
		
	}
	
	public function delete($sql)
	{
		
	}
	
	public function update($sql)
	{
		
	}
	
	public function set_character_set($character_set)
	{
		$this->query("SET NAMES '" . $character_set . "'");
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
	public function compile_select()
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
}

?>