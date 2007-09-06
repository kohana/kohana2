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
		// Import the connect variables
		extract($config['connection']);

		// Persistent connections enabled?
		$connect = ($config['persistent'] == TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		// Make the connection and select the database
		if (($this->link = $connect($host, $user, $pass)) AND mysql_select_db($database, $this->link))
		{
			if ($charset = $config['character_set'])
			{
				$this->set_charset($charset);
			}

			return TRUE;
		}

		/**
		 * @todo this should use $config['show_errors'] and throw exceptions accordingly
		 */
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
		// If the query is a resource, it was a SELECT query
		if (is_resource($result = mysql_query($sql, $this->link)))
		{
			$fetch = ($object == TRUE) ? 'mysql_fetch_object' : 'mysql_fetch_array';
			$rows  = array();

			while ($row = $fetch($result))
			{
				$rows[] = $row;
			}

			return $rows;
		}
		else
		{
			return $result;
		}
	}

	public function delete($sql)
	{

	}

	public function update($sql)
	{

	}

	public function set_charset($charset)
	{
		$this->query('SET NAMES '.mysql_real_escape_string($charset));
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
} // End Database MySQL Driver