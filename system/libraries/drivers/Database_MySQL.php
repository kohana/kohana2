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
	
	public function connect($config)
	{
		return mysql_connect($config['server'], $config['user'], $config['password']);		
	}
	
	public function query($sql)
	{
		$result = mysql_query($sql);
	}
	
	public function delete($sql)
	{
		
	}
	
	public function update($sql)
	{
		
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