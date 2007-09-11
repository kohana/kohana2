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
 * Database API Driver
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Database
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/database.html
 */
interface Database_Driver {

	/**
	 * Connect to the database
	 *
	 * @access  public
	 * @param   string  config array
	 * @return  bool
	 */
	public function connect($config);
	
	/**
	 * Perform a select query
	 *
	 * @access  public
	 * @param   string  config array
	 * @return  mixed
	 */
	public function query($sql);
	
	/**
	 * Perform a delete statement
	 *
	 * @access  public
	 * @param   string  config array
	 * @return  string
	 */
	public function delete($table, $where);
	
	/**
	 * Perform an update statement
	 *
	 * @access  public
	 * @param   string  config array
	 * @return  string
	 */
	public function update($table, $where);
	
	/**
	 * Compile the select syntax for a query
	 *
	 * @access  public
	 * @param   string  config array
	 * @return  bool
	 */
	public function compile_select($database);
	
	/**
	 * Escape the table name for safe queries
	 *
	 * @access  public
	 * @param   string  table name
	 * @return  string
	 */
	public function escape_table($table);
	
	/**
	 * Escape the column name for safe queries
	 *
	 * @access  public
	 * @param   string  column name
	 * @return  string
	 */
	public function escape_column($column);
	
} // End Database Driver Class