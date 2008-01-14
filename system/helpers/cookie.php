<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie helper class.
 *
 * $Id:$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class cookie_Core {

	/**
	 * Method: set
	 *  Sets a cookie with the given parameters.
	 *
	 * Parameters:
	 *  name     - cookie name or array of config options
	 *  value    - cookie value
	 *  expire   - number of seconds before the cookie expires
	 *  path     - URL path to allow
	 *  domain   - URL domain to allow
	 *  secure   - HTTPS only
	 *  httponly - HTTP only (requires PHP 5.2 or higher)
	 *  prefix   - collision-prevention prefix
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	public static function set($name, $value = NULL, $expire = NULL, $path = NULL, $domain = NULL, $secure = NULL, $httponly = NULL, $prefix = NULL)
	{
		// If the name param is an array, we import it
		is_array($name) and extract($name, EXTR_OVERWRITE);

		// Fetch default options
		$config = Config::item('cookie');

		foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly') as $item)
		{
			if ($$item === NULL AND isset($config[$item]))
			{
				$$item = $config[$item];
			}
		}

		// Expiration timestamp
		$expire = ($expire == 0) ? 0 : time() + (int) $expire;

		// Only set httponly if possible
		return (version_compare(PHP_VERSION, '5.2', '>='))
			? setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly)
			: setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
	}

	/**
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
	public static function get($name, $prefix = NULL, $xss_clean = FALSE)
	{
		static $input;

		if ($input === NULL)
		{
			$input = new Input;
		}

		if ($prefix === NULL)
		{
			$prefix = (string) Config::item('cookie.prefix');
		}

		return $input->cookie($prefix.$name, $xss_clean);
	}

	/**
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
	public static function delete($name, $path = NULL, $domain = NULL, $prefix = NULL)
	{
		// Sets the cookie value to an empty string, and the expiration to 24 hours ago
		return self::set($name, '', -86400, $path, $domain, FALSE, FALSE, $prefix);
	}

} // End cookie