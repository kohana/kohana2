<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Security helper class.
 *
 * $Id:$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class security_Core {

	/**
	 * Method: xss_clean
	 *  Sanitize a string with the xss_clean method.
	 *
	 * Parameters:
	 *  str - string to sanitize
	 *
	 * Returns:
	 *  Sanitized string.
	 */
	public static function xss_clean($str)
	{
		static $input;

		if ($input === NULL)
		{
			$input = new Input();
		}

		return $input->xss_clean($str);
	}

	/**
	 * Method: strip_image_tags
	 *  Remove image tags from a string.
	 *
	 * Parameters:
	 *  str - string to sanitize
	 *
	 * Returns:
	 *  Sanitized string.
	 */
	public static function strip_image_tags($str)
	{
		$str = preg_replace('#<img\b.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);

		return trim($str);
	}

	/**
	 * Method: encode_php_tags
	 *  Remove PHP tags from a string.
	 *
	 * Parameters:
	 *  str - string to sanitize
	 *
	 * Returns:
	 *  Sanitized string.
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'),  array('&lt;?', '?&gt;'), $str);
	}

} // End security