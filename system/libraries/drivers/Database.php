<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface: Database_Driver
 *  Database API driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
abstract class Database_Driver {

	/**
	 * Method: connect
	 *  Connects to the database.
	 *
	 * Returns:
	 *  Database link on success or FALSE on failure
	 */
	abstract public function connect();

	/**
	 * Method: query
	 *  Executes a query.
	 *
	 * Parameters:
	 *  sql - query to execute
	 * 
	 * Returns:
	 *  Database result object
	 */
	abstract public function query($sql);

	/**
	 * Method: delete
	 *  Builds a DELETE query.
	 *
	 * Parameters:
	 *  table - table name
	 *  where - WHERE clause
	 * 
	 * Returns:
	 *  A DELETE sql query string
	 */
	public function delete($table, $where)
	{
		return 'DELETE FROM '.$this->escape_table($table).' WHERE '.implode(' ', $where);
	}

	/**
	 * Method: update
	 *  Builds an UPDATE query.
	 *
	 * Parameters:
	 *  table  - table name
	 *  values - associative array of values
	 *  where  - WHERE clause
	 * 
	 * Returns:
	 *  An UPDATE sql query string
	 */
	public function update($table, $values, $where)
	{
		foreach($values as $key => $val)
		{
			$valstr[] = $this->escape_column($key).' = '.$val;
		}
		return 'UPDATE '.$this->escape_table($table).' SET '.implode(', ', $valstr).' WHERE '.implode(' ',$where);
	}

	/**
	 * Method: set_charset
	 *  Sets the character set for future queries.
	 *
	 * Parameters:
	 *  charset - character set
	 */
	abstract public function set_charset($charset);

	/**
	 * Method: escape_table
	 *  Escape the passed table name.
	 *
	 * Parameters:
	 *  table - table name
	 *
	 * Returns:
	 *  A string containing the escaped table name
	 */
	abstract public function escape_table($table);

	/**
	 * Method: escape_column
	 *  Escape the passed column name.
	 *
	 * Parameters:
	 *  column - column name
	 *
	 * Returns:
	 *  A string containing the escaped column name
	 */
	abstract public function escape_column($column);

	/**
	 * Method: where
	 *  Builds a WHERE portion of a query.
	 *
	 * Parameters:
	 *  key        - key name or array of key => value pairs
	 *  value      - value to match with key
	 *  type       - operator to join multiple wheres with (AND/OR)
	 *  num_wheres - number of existing WHERE clauses
	 *  quote      - disable quoting of WHERE clause
	 *
	 * Returns:
	 *  A WHERE portion of a query
	 */
	abstract public function where($key, $value, $type, $num_wheres, $quote);

	/**
	 * Method: like
	 *  Builds a LIKE portion of a query.
	 *
	 * Parameters:
	 *  field     - field name or array of field => match pairs
	 *  match     - like value to match with field
	 *  type      - operator to join multiple likes with (AND/OR)
	 *  num_likes - number of existing WHERE clauses
	 *
	 * Returns:
	 *  A LIKE portion of a query
	 */
	public function like($field, $match = '', $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = (substr($match, 0, 1) == '%' OR substr($match, (strlen($match)-1), 1) == '%') 
		       ? $this->escape_str($match) 
		       : '%'.$this->escape_str($match).'%';

		return $prefix.' '.$this->escape_column($field).' LIKE \''.$match . '\'';
	}

	/**
	 * Method: notlike
	 *  Builds a NOT LIKE portion of a query.
	 *
	 * Parameters:
	 *  field     - field name or array of field => match pairs
	 *  match     - like value to match with field
	 *  type      - operator to join multiple likes with (AND/OR)
	 *  num_likes - number of existing WHERE clauses
	 *
	 * Returns:
	 *  A NOT LIKE portion of a query
	 */
	public function notlike($field, $match = '', $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = (substr($match, 0, 1) == '%' OR substr($match, (strlen($match)-1), 1) == '%') 
		       ? $this->escape_str($match) 
		       : '%'.$this->escape_str($match).'%';

		return $prefix.' '.$this->escape_column($field).' NOT LIKE \''.$match.'\'';
	}

	/**
	 * Method: regex
	 *  Builds a REGEXP portion of a query.
	 *
	 * Parameters:
	 *  field     - field name or array of field => match pairs
	 *  match     - like value to match with field
	 *  type      - operator to join multiple likes with (AND/OR)
	 *  num_regexs - number of existing WHERE clauses
	 *
	 * Returns:
	 *  A string containing the REGEXP query
	 */
	abstract public function regex($field, $match, $type, $num_regexs);

	/**
	 * Method: notregex
	 *  Builds a NOT REGEXP portion of a query.
	 *
	 * Parameters:
	 *  field     - field name or array of field => match pairs
	 *  match     - like value to match with field
	 *  type      - operator to join multiple likes with (AND/OR)
	 *  num_regexs - number of existing WHERE clauses
	 *
	 * Returns:
	 *  A string containing the NOT REGEXP query
	 */
	abstract public function notregex($field, $match, $type, $num_regexs);

	/**
	 * Method: insert
	 *  Builds an INSERT query.
	 *
	 * Parameters:
	 *  table  - table name
	 *  keys   - array of keys
	 *  values - array of values for the keys
	 *
	 * Returns:
	 *  A string containing the INSERT query
	 */
	public function insert($table, $keys, $values)
	{
		// Escape the column names
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->escape_column($value);
		}
		return 'INSERT INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	/**
	 * Method: limit
	 *  Builds a LIMIT portion of a query.
	 *
	 * Parameters:
	 *  limit  - number of rows to limit result to
	 *  offset - offset in result to start returning rows from
	 *
	 * Returns:
	 *  A string containing the LIMIT query
	 */
	abstract public function limit($limit, $offset = 0);

	/**
	 * Method: compile_select
	 *  Compiles the SELECT statement.
	 *  Generates a query string based on which functions were used.
	 *  Should not be called directly, the get() function calls it.
	 *
	 * Parameters:
	 *  database - all the query parts set from the database library
	 *
	 * Returns:
	 *  A string containing the SELECT query
	 */
	abstract public function compile_select($database);

	/**
	 * Method: has_operator
	 *  Determines if the string has an arithmetic operator in it.
	 *
	 * Parameters:
	 *  str - string to test
	 *
	 * Returns:
	 *  TRUE if the string has an operator in it, FALSE otherwise
	 */
	public function has_operator($str)
	{
		return (bool) preg_match('/[<>!=]|\sIS\s+(?:NOT\s+)?NULL\b/i', trim($str));
	}

	/**
	 * Method: escape
	 *  Escapes a value for a query.
	 *
	 * Parameters:
	 *  value - value to escape
	 *
	 * Returns:
	 *  An escaped version of the value
	 */
	public function escape($value)
	{
		switch (gettype($value))
		{
			case 'string':
				$value = '\''.$this->escape_str($value).'\'';
				break;
			case 'boolean':
				$value = (int) $value;
			break;
			default:
				$value = ($value === NULL) ? 'NULL' : $value;
			break;
		}

		return (string) $value;
	}

	/**
	 * Method: escape_str
	 *  Escapes a string for a query.
	 *
	 * Parameters:
	 *  str - string to escape
	 *
	 * Returns:
	 *  An escaped version of the string
	 */
	abstract public function escape_str($str);

	/**
	 * Method: list_tables
	 *  List all tables in the database.
	 *
	 * Returns:
	 *  An array of table names
	 */
	abstract public function list_tables();

	/**
	 * Method: show_error
	 *  Shows the last database error.
	 *
	 * Returns:
	 *  A string containing the error
	 */
	abstract public function show_error();

	/**
	 * Method: field_data
	 *  Returns field data about a table.
	 *
	 * Parameters:
	 *  table - table name
	 *
	 * Returns:
	 *  An array containing the field data or FALSE if the table doesn't exist.
	 */
	abstract public function field_data($table);

} // End Database Driver Interface

/**
 * Interface: Database_Result
 *  Database Result API driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
interface Database_Result {

	/**
	 * Method: result
	 *  Prepares the query result.
	 *
	 * Parameters:
	 *  object - return objects or arrays
	 *  type   - array type to use (if using arrays) or class name (if using objects)
	 * 
	 * Returns:
	 *  Database result object
	 */
	public function result($object = TRUE, $type = FALSE);

	/**
	 * Method: result_array
	 *  Builds an array of query results.
	 *
	 * Parameters:
	 *  object - return objects or arrays
	 *  type   - array type to use (if using arrays) or class name (if using objects)
	 * 
	 * Returns:
	 *  Database result object
	 */
	public function result_array($object = NULL, $type = FALSE);

	/**
	 * Method: insert_id
	 *  Gets the id of an INSERT statement.
	 * 
	 * Returns:
	 *  The insert id number
	 */
	public function insert_id();

	/**
	 * Method: list_fields
	 *  Gets the fields of an already run query
	 * 
	 * Returns:
	 *  an array containing the fields
	 */
	public function list_fields();

} // End Database Result Interface