<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Cookie Helpers
 *
 * @package		Kohana
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
 * @param	string	the number of seconds until expiration
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @return	void
 */
function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '')
{
	if (is_array($name))
	{		
		foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'name') as $item)
		{
			if (isset($name[$item]))
			{
				$$item = $name[$item];
			}
		}
	}
	
	// Set the config file options
	$CORE =& get_instance();
	
	if ($prefix == '' AND $CORE->config->item('cookie_prefix') != '')
	{
		$CORE->config->item('cookie_prefix');
	}
	if ($domain == '' AND $CORE->config->item('cookie_domain') != '')
	{
		$CORE->config->item('cookie_domain');
	}
	if ($prefix == '/' AND $CORE->config->item('cookie_path') != '/')
	{
		$CORE->config->item('cookie_path');
	}
		
	if ( ! is_numeric($expire))
	{
		$expire = time() - 86500;
	}
	else
	{
		if ($expire > 0)
		{
			$expire = time() + $expire;
		}
		else
		{
			$expire = 0;
		}
	}
	
	setcookie($prefix.$name, $value, $expire, $path, $domain, 0);
}
	
// --------------------------------------------------------------------

/**
 * Fetch an item from the COOKIE array
 *
 * @access	public
 * @param	string
 * @param	bool
 * @return	mixed
 */
function get_cookie($index = '', $xss_clean = FALSE)
{
	$CORE =& get_instance();
	return $CORE->input->cookie($index, $xss_clean);
}

// --------------------------------------------------------------------

/**
 * Delete a COOKIE
 *
 * @param	mixed
 * @param	string	the cookie domain.  Usually:  .yourdomain.com
 * @param	string	the cookie path
 * @param	string	the cookie prefix
 * @return	void
 */
function delete_cookie($name = '', $domain = '', $path = '/', $prefix = '')
{
	set_cookie($name, '', '', $domain, $path, $prefix);
}


?>