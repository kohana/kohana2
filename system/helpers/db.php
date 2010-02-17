<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
 class db_Core {

	/**
	 * Shortcut for starting a new query
	 * 
	 * ##### Example
	 * 		//execute a basic query and store result to $result
	 * 		$result = db::query('select * from table')->execute();
	 * 
	 * @param string $sql SQL query
	 * @return Database_Query
	 * @chainable
	 */
	public static function query($sql)
	{
		return new Database_Query($sql);
	}

	/**
	 * Creates a new Datbase_Builder object to chain from
	 * 
	 * ##### Examples
	 * 		//create a builder object
	 * 		$builder = db::build();
	 * 		
	 * 		//query a table
	 * 		$result = db::build()->select('*')->from('table')->execute();
	 * 
	 * @param string|array $database Database configuration block or array of settings
	 * @return Database_Builder
	 * @chainable
	 */
	public static function build($database = 'default')
	{
		return new Database_Builder($database);
	}

	/**
	 * Begins a select query builder
	 * 
	 * ##### Example
	 * 		//query a table
	 * 		$result = db::select()->from('table')->execute();
	 * 		//SQL: SELECT * FROM `table`
	 * 
	 * @param string|array $columns can be * or array of columns
	 * @return Database_Builder
	 * @chainable
	 */
	public static function select($columns = NULL)
	{
		return db::build()->select($columns);
	}

	/**
	 * Begin a insert query builder
	 * 
	 * ##### Example
	 * 		//insert to a table
	 * 		db::insert('table', array('name' => 'Kohana'))->execute();
	 * 		//SQL: INSERT INTO `table` SET name = 'Kohana'
	 *  
	 * @param string $table [optional]
	 * @param array $set [optional]
	 * @return Database_Builder
	 * @chainable
	 */
	public static function insert($table = NULL, $set = NULL)
	{
		return db::build()->insert($table, $set);
	}

	/**
	 * Begin a update query builder
	 * 
	 * ##### Example
	 * 		//insert to a table
	 * 		db::update('table', array('name' => 'Kohana'), array('id', '=', 1)->execute();
	 * 		//SQL: INSERT INTO `table` SET name = 'Kohana'
	 *  
	 * @param string $table table to update
	 * @param array $set data to set
	 * @param array $where where statement
	 * @return Database_Builder
	 * @chainable
	 */
	public static function update($table = NULL, $set = NULL, $where = NULL)
	{
		return db::build()->update($table, $set, $where);
	}

	/**
	 * Delete records from the database
	 * 
	 * ##### Example
	 * 		//Delete a single record
	 * 		db::delete('table', array('id', '=', 1))->limit(1)->execute();
	 * 
	 * 		//Delete multiple records
	 * 		db::delete('table', array('status', '=', 'inactive'))->execute();
	 * 
	 * @param string $table table to delete records from
	 * @param array $where where statement
	 * @return Database_Builder
	 * @chainable
	 */
	public static function delete($table = NULL, $where = NULL)
	{
		return db::build()->delete($table, $where);
	}

	/**
	 * Creates a new Database Expression
	 * 
	 * ##### Example
	 * 		//Get the sum of all numbers in column from table
	 * 		$result = db::select(db::expr('SUM(column)'))->from('table')->execute();
	 * 
	 * @param string $expression SQL expression
	 * @return Database_Expression
	 * @chainable
	 */
	public static function expr($expression)
	{
		return new Database_Expression($expression);
	}

} // End db
