<?php defined('SYSPATH') or die('No direct access allowed.');

class str {

	public static function reduce_slashes($str)
	{
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

} // End str Class