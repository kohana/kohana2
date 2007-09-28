<?php defined('SYSPATH') or die('No direct access allowed.');

class security {

	public static function xss_clean($str, $charset = 'UTF-8')
	{
		return Kohana::instance()->input->xss_clean($str, $charset);
	}

	public static function image_tags($str)
	{
		$str = preg_replace('#<img.*?(?:src\s*=\s*["\'](.*?)["\'].*?)?>#is', '$1', $str);
		$str = preg_replace('#<img.*?(?:src\s*=\s*(.*?).*?)?>#is', '$1', $str);

		return trim($str);
	}

	public static function php_tags($str)
	{
		return str_replace(array('<?', '?>'),  array('&lt;?', '?&gt;'), $str);
	}

} // End security Class