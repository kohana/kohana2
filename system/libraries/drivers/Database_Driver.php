<?php defined('SYSPATH') or die('No direct access allowed.');
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
	 * @return  boolean
	 */
	public function connect($config);

	/**
	 * Perform a query
	 *
	 * @access  public
	 * @param   string  sql statement
	 * @return  mixed
	 */
	public function query($sql);

	/**
	 * Return a delete statement
	 *
	 * @access  public
	 * @param   string  table name
	 * @param   string  where command
	 * @return  string
	 */
	public function delete($table, $where);

	/**
	 * Return an update statement
	 *
	 * @access  public
	 * @param   string  table name
	 * @param   string  where command
	 * @return  string
	 */
	public function update($table, $val, $where);

	/**
	 * Return an update statement
	 *
	 * @access  public
	 * @param   string  character set
	 * @return  string
	 */
	public function set_charset($charset);

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

	/**
	 * Return a where statement
	 *
	 * @access  public
	 * @param   string  key name
	 * @param   string  value
	 * @param   string  where type
	 * @param   integer previous number of wheres
	 * @param   string  quote (?)
	 * @return  string
	 */
	public function where($key, $value, $type, $num_wheres, $quote);

	/**
	 * Return a like statement
	 *
	 * @access  public
	 * @param   string  field name
	 * @param   string  match value
	 * @param   string  like type
	 * @param   integer previous number of likes
	 * @return  string
	 */
	public function like($field, $match, $type, $num_likes);

	/**
	 * Return an insert statement
	 *
	 * @access  public
	 * @param   string  table name
	 * @param   array   key names
	 * @param   array   values
	 * @return  string
	 */
	public function insert($table, $keys, $values);

	/**
	 * Determines if a string has an operator in it
	 *
	 * @access  public
	 * @param   string  string to test
	 * @return  string
	 */
	public function has_operator($str);

	/**
	 * Escape a arbitrary value
	 *
	 * @access  public
	 * @param   mixed  value
	 * @return  string
	 */
	public function escape($str);

	/**
	 * Escape a string for a query
	 *
	 * @access  public
	 * @param   string  string
	 * @return  string
	 */
	public function escape_str($str);

	/**
	 * Compile the select syntax for a query
	 *
	 * @access  public
	 * @param   array    current database values
	 * @return  string
	 */
	public function compile_select($database);

} // End Database Driver Interface

interface Database_Result {

	public function process($object, $type);
	/**
	 * Assign the result set
	 *
	 * @access  public
	 */
	public function result($object = TRUE, $type = FALSE);

	/**
	 * Get the number of rows in a SELECT query or the number of affected rows in UPDATE
	 *
	 * @access  public
	 * @return  integer
	 */
	public function num_rows();

	/**
	 * Get the insert id from an INSERT statement
	 *
	 * @access  public
	 * @return  integer
	 */
	public function insert_id();

} // End Database Result Interface