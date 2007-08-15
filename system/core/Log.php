<?php defined('SYSPATH') or die('No direct access allowed.');

final class Log {
	
	public static $messages = array();
	
	public static function add($type, $message)
	{
		self::$messages[$type][] = $message;
	}
	
} // End Log Class