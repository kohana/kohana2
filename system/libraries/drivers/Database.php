<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Database_Driver
 *  Database API driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 *
 * $Id$
 */
interface Database_Driver {

	/*
	 * Method: connect
	 *  connects to the database
	 *
	 * Returns:
	 *  the database link on success or FALSE on failure
	 *
	 */
	public function connect();

	/*
	 * Method: query
	 *  perform a query
	 *
	 * Parameters:
	 *  sql - the query to run
	 * 
	 * Returns:
	 *  <Mysql_Result> object
	 *
	 */
	public function query($sql);

	/*
	 * Method: delete
	 *  builds a DELETE query
	 *
	 * Parameters:
	 *  table - the table to delete from
	 *  where - there WHERE clause of the query
	 * 
	 * Returns:
	 *  a DELETE sql string
	 *
	 */
	public function delete($table, $where);

	/*
	 * Method: update
	 *  builds an UPDATE query
	 *
	 * Parameters:
	 *  table - the table to delete from
	 *  values - the values to set
	 *  where - there WHERE clause of the query
	 * 
	 * Returns:
	 *  an UPDATE sql string
	 *
	 */
	public function update($table, $val, $where);

	/*
	 * Method: set_charset
	 *  sets the character set for future queries
	 *
	 * Parameters:
	 *  charset - the character set to use
	 *
	 */
	public function set_charset($charset);

	/*
	 * Method: escape_table
	 *  escape the passed table using backticks
	 *
	 * Parameters:
	 *  table - the table name to escape
	 *
	 * Returns:
	 *  a string containing the escaped table name
	 */
	public function escape_table($table);

	/*
	 * Method: escape_table
	 *  escape the passed column using backticks
	 *
	 * Parameters:
	 *  column - the column name to escape
	 *
	 * Returns:
	 *  a string containing the escaped column name
	 */
	public function escape_column($column);

	/*
	 * Method: where
	 *  builds a WHERE portion of a query
	 *
	 * Parameters:
	 *  key - a key name, or an array of key => value pairs
	 *  value - the value
	 *  type - the value to join multiple wheres with (AND/OR)
	 *  num_wheres - the number of existing WHERE clauses
	 *  quote - disables the quoting of the WHERE clause
	 *
	 * Returns:
	 *  an array of WHERE clauses
	 */
	public function where($key, $value, $type, $num_wheres, $quote);

	/*
	 * Method: like
	 *  builds a LIKE portion of a query
	 *
	 * Parameters:
	 *  field - a field name, or an array of field => value pairs
	 *  match - the value to match
	 *  type - the value to join multiple likes with (AND/OR)
	 *  num_likes - the number of existing LIKE clauses
	 *
	 * Returns:
	 *  an array of WHERE clauses
	 */
	public function like($field, $match, $type, $num_likes);

	/*
	 * Method: insert
	 *  builds an INSERT query
	 *
	 * Parameters:
	 *  table - the table to run the query on
	 *  keys - an array of keys
	 *  values - an array of values to insert with the keys
	 *
	 * Returns:
	 *  a string containing the INSERT query
	 */
	public function insert($table, $keys, $values);

	/*
	 * Method: limit
	 *  builds a LIMIT portion of a query
	 *
	 * Parameters:
	 *  limit - a number to limit the returned data to
	 *  offset - the offset to use
	 *
	 * Returns:
	 *  a string containing the LIMIT query
	 */
	public function limit($limit, $offset = 0);

	/*
	 * Method: compile_select
	 *  Compile the SELECT statement
	 *  Generates a query string based on which functions were used.
	 *  Should not be called directly.  The get() function calls it.
	 *
	 * Parameters:
	 *  database - all the query parts set from the database library
	 *
	 * Returns:
	 *  a string containing the SELECT query
	 */
	public function compile_select($database);

	/*
	 * Method: has_operator
	 *  determines if the string has an arithmetic operator in it
	 *
	 * Parameters:
	 *  str - the string to test
	 *
	 * Returns:
	 *  TRUE if the string has an operator in it, FALSE otherwise
	 */
	public function has_operator($str);

	/*
	 * Method: escape
	 *  escapes a value for a query
	 *
	 * Parameters:
	 *  str - the value to escape
	 *
	 * Returns:
	 *  an escaped version of the value
	 */
	public function escape($str);

	/*
	 * Method: escape_str
	 *  escapes a string for a query
	 *
	 * Parameters:
	 *  str - the string to escape
	 *
	 * Returns:
	 *  an escaped version of the string
	 */
	public function escape_str($str);

	/*
	 * Method: list_tables
	 *  list all tables in the database
	 *
	 * Returns:
	 *  an array of table names
	 */
	public function list_tables();

	/*
	 * Method: show_error
	 *  shows the last MySQL error
	 *
	 * Returns:
	 *  a string containing the error
	 */
	public function show_error();

	/*
	 * Method: field_data
	 *  returns field data about a table
	 *
	 * Parameters:
	 *  table - the table to query
	 *
	 * Returns:
	 *  an array containing the field data
	 */
	public function field_data($table);

} // End Database Driver Interface

interface Database_Result {

	/*
	 * Method: result
	 *  prepares the query result
	 *
	 * Parameters:
	 *  object - use objects or arrays
	 *  type - the array type to use (if using arrays) or an class name (if using objects)
	 * 
	 * Returns:
	 *  <Mysql_Result> object
	 *
	 */
	public function result($object = TRUE, $type = FALSE);

	/*
	 * Method: insert_id
	 *  get the insert id of an INSERT statement
	 * 
	 * Returns:
	 *  the insert id number
	 *
	 */
	public function insert_id();

} // End Database Result Interface