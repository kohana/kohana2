<?php defined('SYSPATH') or die('No direct access allowed.');

class security {

	public static function xss_clean($str)
	{
		return Kohana::instance()->input->xss_clean($str);
	}

	public static function image_tags($str)
	{
		$str = preg_replace('#<img\b.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);

		return trim($str);
	}

	public static function php_tags($str)
	{
		return str_replace(array('<?', '?>'),  array('&lt;?', '?&gt;'), $str);
	}

} // End security Class