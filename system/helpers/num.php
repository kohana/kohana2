<?php defined('SYSPATH') or die('No direct script access.');

class num_Core {

	public static function round($number, $nearest = 5)
	{
		return round($number / $nearest) * $nearest;
	}

}