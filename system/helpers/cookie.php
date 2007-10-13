<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Cookie Class
 *
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/helpers/cookie.html
 */
class cookie {

	/**
	 * Set a cookie
	 *
	 * @access  public
	 * @param   mixed    name or config
	 * @param   string   value
	 * @param   integer  expiration (timestamp)
	 * @param   string   URL path
	 * @param   string   domain
	 * @param   boolean  HTTPS only
	 * @param   boolean  HTTP only
	 * @param   string   prefix (to prevent collisions)
	 * @return  boolean
	 */
	public static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE, $prefix = '')
	{
		$config = Config::item('cookie');

		if (is_array($name))
		{
			$config = array_merge($config, $name);
		}

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

	/**
	 * Get a cookie
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @param   boolean
	 * @return  string
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
			$prefix = (string) Config::item('cookie.prefix');
		}

		return $input->cookie($prefix.$name, $xss_clean);
	}

	/**
	 * Delete a cookie
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @param   string
	 * @param   string
	 * @return  boolean
	 */
	public static function delete($name, $path = '/', $domain = '', $prefix = '')
	{
		return self::set($name, '', 1, $path, $domain, FALSE, FALSE, $prefix);
	}

} // End cookie class