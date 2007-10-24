<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: cookie
 *  Cookie helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class cookie {

	/*
	 * Method: set
	 *  Sets a cookie with the given parameters.
	 *
	 * Parameters:
	 * name     - cookie name or array of config options
	 * value    - cookie value
	 * expire   - number of seconds before the cookie expires
	 * path     - URL path to allow
	 * domain   - URL domain to allow
	 * secure   - HTTPS only
	 * httponly - HTTP only
	 * prefix   - collision-prevention prefix
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	public static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE, $prefix = '')
	{
		// If the name param is an array, we import it
		is_array($name) and extract($name, EXTR_OVERWRITE);

		// Fetch default options
		$config = Config::item('cookie');

		foreach (array('name', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly') as $item)
		{
			if (isset($config[$item]))
			{
				$$item = $config[$item];
			}
		}

		// Expiration timestamp
		$expire = ($expire > 0) ? time() + (int) $expire : 0;

		return setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/*
	 * Method: get
	 *  Fetch a cookie value, using the Input library.
	 *
	 * Parameters:
	 *  name      - cookie name
	 *  prefix    - collision-prevention prefix
	 *  xss_clean - use XSS cleaning on the value
	 *
	 * Returns:
	 *  Value of the requested cookie.
	 */
	public static function get($name, $prefix = '', $xss_clean = FALSE)
	{
		static $input;

		if ($input === NULL)
		{
			$input = new Input();
		}

		if ($prefix == '')
		{
			$prefix = Config::item('cookie.prefix');
		}

		return $input->cookie($prefix.$name, $xss_clean);
	}

	/*
	 * Method: delete
	 *  Nullify and unset a cookie.
	 *
	 * Parameters:
	 *  name   - cookie name
	 *  path   - URL path
	 *  domain - URL domain
	 *  prefix - collision-prevention prefix
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	public static function delete($name, $path = '/', $domain = '', $prefix = '')
	{
		// Sets the cookie value to an empty string, and the expiration to 2 hours ago
		return self::set($name, '', -7200, $path, $domain, FALSE, FALSE, $prefix);
	}

} // End cookie