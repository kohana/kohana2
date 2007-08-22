<?php defined('SYSPATH') or die('No direct access allowed.');

class URI_Core extends Router {
	
	public function segment($index = 1)
	{
		$index = (int) $index - 1;
		
		return isset(self::$segments[$index]) ? self::$segments[$index] : FALSE;
	}
	
} // End URI Class