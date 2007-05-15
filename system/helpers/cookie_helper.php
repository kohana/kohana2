<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BlueFlame
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		BlueFlame
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeigniter.com/user_guide/license.html
 * @link		http://blueflame.ciforge.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * BlueFlame Cookie Helpers
 *
 * @package		BlueFlame
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/cookie_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Set cookie
 *
 * Accepts six parameter, or you can submit an associative
 * array in the first parameter containing all the values.
 *
 * @access	public
 * @param	mixed
 * @param	string	the value of the cookie
 * @param	int		the number of seconds until expiration
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @param	bool	whether the cookie should be sent ONLY over SSL
 * @return	void
 */
function set_cookie($name, $value = '', $expire = 0, $domain = null, $path = null, $prefix = null, $secure = false)
{
	if (is_array($name))
	{
		foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name') as $item)
		{
			if (isset($name[$item]))
			{
				$$item = $name[$item];
			}
		}
	}

	// Set the config file options
	$CI =& get_instance();
	if ( ! is_numeric($expire))
	{
		$default_cookie_lifetime = $CI->config->item('cookie_lifetime');
		$expire =
			(! is_numeric($default_cookie_lifetime) || $default_cookie_lifetime<=0) ?
				0 :
				(time() + $default_cookie_lifetime);
	}
	else
	{
		$expire =
			($expire==0) ?
				$expire :
				(time() + $expire);
	}
	$default_domain = $CI->config->item('cookie_domain');
	if (is_null($domain) AND !empty($default_domain))
	{
		$domain = $default_domain;
	}
	$default_path = $CI->config->item('cookie_path');
	if (is_null($path) AND ! empty($default_path))
	{
		$path = $default_path;
	}
	$default_prefix = $CI->config->item('cookie_prefix');
	if (is_null($prefix) AND ! empty($default_prefix))
	{
		$prefix = $default_prefix;
	}
	$secure = is_bool($secure) ? $secure : false;

	setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
}

// --------------------------------------------------------------------

/**
 * Fetch an item from the COOKIE array
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	mixed
 */
function get_cookie($name = '', $prefix=null, $xss_clean = FALSE)
{
	$CI =& get_instance();
	$default_prefix = $CI->config->item('cookie_prefix');
	if (is_null($prefix) AND ! empty($default_prefix))
	{
		$prefix = $default_prefix;
	}
	return $CI->input->cookie($prefix.$name, $xss_clean);
}

// --------------------------------------------------------------------

/**
 * Delete a COOKIE
 *
 * @access	public
 * @param	mixed
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @param 	bool
 * @return	void
 */
function delete_cookie($name = '', $domain = null, $path = null, $prefix = null, $remove_live = TRUE)
{
	if($remove_live===TRUE)
	{
		$CI =& get_instance();
		$default_prefix = $CI->config->item('cookie_prefix');
		if (is_null($prefix) AND ! empty($default_prefix))
		{
			$prefix = $default_prefix;
		}
		$cIndex = is_array($name) ? $name['name'] : $name;
		if(isset($_COOKIE[$prefix.$cIndex]))
		{
			unset($_COOKIE[$prefix.$cIndex]);
		}
	}
	set_cookie($name, '', -86500, $domain, $path, $prefix);
}


?>