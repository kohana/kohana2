<?php defined('SYSPATH') or die('No direct script access.');

class PDODB_Mysql_Driver extends PDODB_Driver {

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
		return ($offset === NULL) ? sprintf('LIMIT %d', $limit) : sprintf('LIMIT %d, %d', $offset, $limit);
	}

	public function quote_identifier($str)
	{
		static $quoted;

		if (empty($quoted[$str]))
		{
			// Cache the quoted string
			$quoted[$str] = preg_replace('/[^.*]+/', '`$0`', $str);
		}

		return $quoted[$str];
	}

} // End PDODB_Mysql_Driver