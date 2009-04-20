<?php defined('SYSPATH') or die('No direct script access.');

class DB_Core {

	public static function query($sql)
	{
		return new Database_Query($sql);
	}

	public static function build($database = 'default')
	{
		return new Database_Builder($database);
	}

	public static function select($columns = NULL)
	{
		return DB::build()->select($columns);
	}

	public static function insert($table = NULL, $set = NULL)
	{
		return DB::build()->insert($table, $set);
	}

	public static function update($table = NULL, $set = NULL, $where = NULL)
	{
		return DB::build()->update($table, $set, $where);
	}

	public static function delete($sql)
	{
		return new Database_Delete($sql);
	}

	public static function expr($expression)
	{
		return new Database_Expression($expression);
	}

} // End DB