<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Team.
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 * $Id$
 */

// ------------------------------------------------------------------------

/**
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Input
 * @author      Rick Ellis
 * @link        http://kohanaphp.com/user_guide/libraries/input.html
 */
class Input_Core {

	public $use_xss_clean   = FALSE;
	public $ip_address      = FALSE;
	public $user_agent      = FALSE;
	public $allow_get_array = FALSE;

	/**
	 * Constructor
	 *
	 * Sets whether to globally enable the XSS processing
	 * and whether to allow the $_GET array
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->use_xss_clean = (bool) Config::item('core.global_xss_filtering');
		$this->_sanitize_globals();

		Log::add('debug', 'Input Class Initialized');
	}

	/**
	 * Sanitize Globals
	 *
	 * This function does the following:
	 * Unsets $_GET data (if query strings are not enabled)
	 * Unsets all globals if register_globals is enabled
	 * Standardizes newline characters to \n
	 *
	 * @access	private
	 * @return	void
	 */
	private function _sanitize_globals()
	{
		// Unset globals. This is effectively the same as register_globals = off
		foreach (array($_GET, $_POST, $_COOKIE) as $global)
		{
			if ( ! is_array($global))
			{
				global $global;
				$$global = NULL;
			}
			else
			{
				foreach ($global as $key => $val)
				{
					global $$key;
					$$key = NULL;
				}
			}
		}

		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if (is_array($_GET) AND count($_GET) > 0)
		{
			foreach($_GET as $key => $val)
			{
				$_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Clean $_POST Data
		if (is_array($_POST) AND count($_POST) > 0)
		{
			foreach($_POST as $key => $val)
			{
				$_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Clean $_COOKIE Data
		if (is_array($_COOKIE) AND count($_COOKIE) > 0)
		{
			foreach($_COOKIE as $key => $val)
			{
				$_COOKIE[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		Log::add('debug', 'Global POST and COOKIE data sanitized');
	}

	/**
	 * Clean Input Data
	 *
	 * This is a helper function. It escapes data and
	 * standardizes newline characters to \n
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	private function _clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach ($str as $key => $val)
			{
				$new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
			return $new_array;
		}

		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}

		if ($this->use_xss_clean === TRUE)
		{
			$str = $this->xss_clean($str);
		}

		// Standardize newlines
		return str_replace(array("\r\n", "\r"), "\n", $str);
	}

	/**
	 * Clean Keys
	 *
	 * This is a helper function. To prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	private function _clean_input_keys($str)
	{
		if ( ! preg_match('#^[a-z0-9:_/-]+$#iD', $str))
		{
			exit('Disallowed Key Characters.');
		}

		return $str;
	}

	/**
	 * Fetch an item from a global array
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	private function _get_global($global, $index = FALSE, $xss_clean = FALSE)
	{
		$global = '_'.strtoupper(trim($global, '_'));

		global $$global; // For some reason, we have to do this :(
		if ( ! isset($$global))
			return FALSE;

		$array = $$global;

		if ($index === FALSE)
			return $array;

		if ( ! isset($array[$index]))
			return FALSE;

		if ($xss_clean === TRUE)
		{
			if (is_array($array[$index]))
			{
				foreach($array[$index] as $key => $val)
				{
					$array[$index][$key] = $this->xss_clean($val);
				}
			}
			else
			{
				return $this->xss_clean($array[$index]);
			}
		}

		return $array[$index];
	}

	/**
	 * Fetch an item from the GET array
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public function get($index = FALSE, $xss_clean = FALSE)
	{
		return $this->_get_global('GET', $index, $xss_clean);
	}

	/**
	 * Fetch an item from the POST array
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public function post($index = FALSE, $xss_clean = FALSE)
	{
		return $this->_get_global('POST', $index, $xss_clean);
	}

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public function cookie($index = FALSE, $xss_clean = FALSE)
	{
		return $this->_get_global('COOKIE', $index, $xss_clean);
	}

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public function server($index = FALSE, $xss_clean = FALSE)
	{
		return $this->_get_global('SERVER', $index, $xss_clean);
	}

	/**
	 * Fetch the IP Address
	 *
	 * @access	public
	 * @return	string
	 */
	public function ip_address()
	{
		if ($this->ip_address !== FALSE)
			return $this->ip_address;

		if ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			 $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			 $this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			 $this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			 $this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ip_address === FALSE)
		{
			$this->ip_address = '0.0.0.0';
			return $this->ip_address;
		}

		if (strstr($this->ip_address, ','))
		{
			$x = explode(',', $this->ip_address);
			$this->ip_address = end($x);
		}

		if ( ! $this->valid_ip($this->ip_address))
		{
			$this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	/**
	 * Validate IP Address
	 *
	 * Validates an IPv4 address based on RFC specifications
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function valid_ip($ip)
	{
		if ( ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/D', $ip))
			return FALSE;

		$octets = explode('.', $ip);

		for ($i = 1; $i < 5; $i++)
		{
			$octet = intval($octets[($i-1)]);
			if ($i === 1)
			{
				if ($octet > 223 OR $octet < 1)
					return FALSE;
			}
			elseif ($i === 4)
			{
				if ($octet < 1)
					return FALSE;
			}
			else
			{
				if ($octet > 254)
					return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * User Agent
	 *
	 * @access	public
	 * @return	string
	 */
	public function user_agent()
	{
		if ($this->user_agent !== FALSE)
			return $this->user_agent;

		$this->user_agent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

		return $this->user_agent;
	}

	/**
	 * XSS Clean
	 * 
	 * 
	 * 
	 * 
	 * @todo    Replace by HTML Purifier
	 * 
	 * 
	 * 
	 * 
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function xss_clean($str, $charset = 'ISO-8859-1')
	{
		// Temporarily placeholder just return the original string in order not to break
		// other code that relies on this function.
		return $str;
	}

} // End Input Class