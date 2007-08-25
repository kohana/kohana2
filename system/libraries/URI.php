<?php defined('SYSPATH') or die('No direct access allowed.');

class URI_Core extends Router {
	
	public function segment($index = 1)
	{
		$index = (int) $index - 1;
		
		return isset(self::$segments[$index]) ? self::$segments[$index] : FALSE;
	}

	public function segment_array()
	{
		$i = 1;
		$segment_array = array();

		foreach (self::$segments as $segment)
		{
			$segment_array[$i++] = $segment;
		}
		
		return $segment_array;
	}
	
	public function uri_string()
	{
		return implode('/', self::$segments);
	}
	
} // End URI Class