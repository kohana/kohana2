<?php

namespace Helper;

defined('SYSPATH') or die('No direct script access.');

/**
 * The db helper is a factory class to provide easy access to a new [Database_Builder]
 * instance.
 * For more information about building queries please see the [Database_Builder]
 * documentation.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
 class db {

	/**
	 * Shortcut for starting a new query
	 *
	 * ##### Basic Example
	 * 		//execute a basic query and store result to $result
	 * 		$result = db::query('select * from table')->execute();
	 *
	 * ##### Query Binding Example
	 * 		$result = db::query('select * from table WHERE `id` = :id')->value(':id', 14)->execute();
	 *      // SQL: select * from table WHERE `id` = 14
	 *
	 * For more information see the [Database_Query] documentation.
	 *
	 * @param string $sql SQL query
	 * @return Database_Query
	 */
	public static function query($sql)
	{
		return new \Library\Database_Query($sql);
	}

	/**
	 * Creates a new [Database_Builder] object to your query from.
	 *
	 * ##### Examples
	 * 		//create a builder object
	 * 		$builder = db::build();
	 *
	 * 		//query a table
	 * 		$result = db::build()->select('*')->from('table')->execute();
	 *
	 * @param mixed $database   Database configuration block or array of settings
	 * @return Database_Builder
	 */
	public static function build($database = 'default')
	{
		return new \Library\Database_Builder($database);
	}

	/**
	 * Begins a select query builder
	 *
	 * ##### Example
	 * 		//query a table
	 * 		$result = db::select()->from('table')->execute();
	 * 		//SQL: SELECT * FROM `table`
	 *
	 * 		$result = db::select(array('alias' => 'column_name'))->from('table')->execute();
	 * 		//SQL: SELECT `column_name` AS `alias` FROM `table`
	 *
	 * @param   mixed   $columns   Can be * or array of columns
	 * @return Database_Builder
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
	 * @param string    $table   Table Name
	 * @param array     $set     Values to set
	 * @return Database_Builder
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
	 * @param string   $table    Table to update
	 * @param array    $set      Data to set
	 * @param array    $where    Where statement
	 * @return Database_Builder
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
	 * @param string    $table     Table to delete records from
	 * @param array     $where     Where statement
	 * @return Database_Builder
	 */
	public static function delete($table = NULL, $where = NULL)
	{
		return db::build()->delete($table, $where);
	}

	/**
	 * Creates a new Database Expression
	 *
	 * ##### Example
	 *     // Increment login counter by 1
	 *     $result = db::update('table', array('logins' => db::expr('`logins` + 1')), array('id', '=', 1));
	 *     // SQL: UPDATE `table` SET `logins` = `logins` + 1 WHERE `id` = 1
	 *
	 * Note: It is __not__ necessary to use db::expr() for basic database functions.
	 * For example this will work fine:
	 *
     *     //Get the sum of all numbers in column from table
	 *     $result = db::select(array('sum' => 'SUM("column")'))->from('test');
	 *     // SQL: SELECT SUM(`column`) AS `sum` FROM `test`
	 *
	 * @param string $expression SQL expression
	 * @return Database_Expression
	 */
	public static function expr($expression)
	{
		return new \Library\Database_Expression($expression);
	}

} // End db
