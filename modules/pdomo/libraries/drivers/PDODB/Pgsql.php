<?php defined('SYSPATH') or die('No direct script access.');

class PDODB_Pgsql_Driver extends PDODB_Driver {

	public static function instance()
	{
		if (self::$instance === NULL)
		{
			$class = __CLASS__;
			self::$instance = new $class;
		}

		return self::$instance;
	}

	public function limit($limit, $offset = NULL)
	{
		return ($offset === NULL) ? sprintf('LIMIT %d', $limit) : sprintf('LIMIT %d OFFSET %d', $limit, $offset);
	}

	public function quote_identifier($str)
	{
		return preg_replace('/[^.*]+/', '"$0"', $str);
	}

} // End PDODB_Mysql_Driver