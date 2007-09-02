<?php defined('SYSPATH') or die('No direct access allowed.');

class cookie {
	
	/**
	 * Set a cookie
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	integer
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @param	boolean
	 * @param	string
	 * @return	boolean
	 */
	public static function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE, $prefix = '')
	{
		$setup = Config::item('cookie');
		
		if (is_array($name))
		{
			$setup = array_merge($setup, $name);
		}
		
		foreach (array('name', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly') as $item)
		{
			if (isset($setup[$item]))
			{
				$$item = $setup[$item];
			}
		}
		
		$expire = (int) ($expire > 0) ? time() + $expire : 0;
		
		return setcookie($prefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Get a cookie
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public static function get($name, $prefix = '', $xss_clean = FALSE)
	{
		if ($prefix == '')
		{
			$prefix = (string) Config::item('cookie.prefix');
		}
		
		return Kohana::instance()->input->cookie($prefix.$name, $xss_clean);
	}
	
	/**
	 * Delete a cookie
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	public static function delete($name, $path = '/', $domain = '', $prefix = '')
	{
		return self::set($name, '', 1, $path, $domain, FALSE, FALSE, $prefix);
	}

} // End cookie class