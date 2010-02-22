<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The security helper provides convenience methods for common
 * security practices; such as, XSS cleaning.
 *
 * #### Using the security helper
 *
 *     // Lets sanitize a string with the default method
 *     echo Kohana::debug(security::xss_clean("'';!--\"<XSS>=&{()}"));
 *     
 *     // Output:
 *     (string) &#039;&#039;;!--&quot;&lt;XSS&gt;=&amp;{()}
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class security_Core {

	/**
	 * This method sanitizes a string to be XSS safe.
	 *
	 * The second function argument is a string denoting which tool
	 * you wish to use (possibly *htmlpurifier*), the default is
	 * Kohana's built in XSS sanitizing method found in the Input
	 * library.
	 *
	 * @link [class:input]
	 *
	 * ###### Example
	 *
	 *     // Sanitize using *htmlpurifier*
	 *     echo Kohana::debug(security::xss_clean("'';!--\"<XSS>=&{()}", 'htmlpurifier'));
	 *     
	 *     // Output:
	 *     (string) &#039;&#039;;!--&quot;&lt;XSS&gt;=&amp;{()}
	 *
	 * @param   string  $str  String to sanitize
	 * @param   string  $tool Sanitization method to use, default is Kohana's xss_clean method
	 * @return  string
	 */
	public static function xss_clean($str, $tool = NULL)
	{
		return Input::instance()->xss_clean($str, $tool);
	}

	/**
	 * Convert a string containing image tags into a string with the
	 * image tags as html entities.
	 *
	 * ###### Example
	 *
	 *     echo Kohana::debug(security::strip_image_tags('<image src="rambo-kitteh.png" alt="Rambo Kitteh!" />'));
	 *     
	 *     // Output:
	 *     (string) &lt;image src=&quot;rambo-kitteh.png&quot; alt=&quot;Rambo Kitteh!&quot; /&gt;
	 *
	 * @param   string  $str String to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

} // End security