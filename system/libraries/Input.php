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
 *
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

	protected $use_xss_clean   = FALSE;

	public $ip_address = FALSE;
	public $user_agent = FALSE;

	/**
	 * Constructor
	 *
	 * Sets whether to globally enable the XSS processing
	 * and whether to allow the $_GET array
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
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

		// Clean $_GET data
		if (is_array($_GET) AND count($_GET) > 0)
		{
			foreach($_GET as $key => $val)
			{
				$_GET[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			}
		}

		// Clean $_POST data
		if (is_array($_POST) AND count($_POST) > 0)
		{
			foreach($_POST as $key => $val)
			{
				$_POST[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			}
		}

		// Clean $_COOKIE data
		if (is_array($_COOKIE) AND count($_COOKIE) > 0)
		{
			foreach($_COOKIE as $key => $val)
			{
				$_COOKIE[$this->clean_input_keys($key)] = $this->clean_input_data($val);
			}
		}

		Log::add('debug', 'Global POST and COOKIE data sanitized');

		// Use XSS clean?
		$this->use_xss_clean = (bool) Config::item('core.global_xss_filtering');

		Log::add('debug', 'Input Library initialized');
	}

	/**
	 * Fetch an item from a global array
	 *
	 * @access  protected
	 * @param   string
	 * @param   array
	 * @return  string
	 */
	public function __call($global, $args = array())
	{
		// Array to be searched, assigned by reference later
		$array = array();

		// Use XSS cleaning?
		$xss_clean = isset($args[1]) ? (bool) $args[1] : FALSE;

		// Array key and value
		$key = isset($args[0]) ? $args[0] : FALSE;
		$val = FALSE;

		// Set the $array
		switch(strtolower($global))
		{
			case 'get':    $array =& $_GET;    break;
			case 'post':   $array =& $_POST;   break;
			case 'cookie': $array =& $_COOKIE; break;
			case 'server': $array =& $_SERVER; break;
			default:
				throw new Kohana_Exception('core.invalid_method', $global, get_class($this));
		}

		if ($key == FALSE)
			return $array;

		// XSS clean if the data has not already been cleaned.
		if ($this->use_xss_clean == FALSE AND $xss_clean == TRUE AND ! empty($array[$key]))
		{
			if (is_array($array[$key]))
			{
				foreach($array[$key] as $sub_key => $sub_val)
				{
					$array[$key][$sub_key] = $this->xss_clean($sub_val);
				}
			}
			else
			{
				$array[$key] = $this->xss_clean($array[$key]);
			}
		}

		// Return the global value
		return isset($array[$key]) ? $array[$key] : FALSE;
	}

	/**
	 * Clean Input Data
	 *
	 * This is a helper function. It escapes data and
	 * standardizes newline characters to \n
	 *
	 * @access  protected
	 * @param   string
	 * @return  string
	 */
	protected function clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach ($str as $key => $val)
			{
				$new_array[$this->clean_input_keys($key)] = $this->clean_input_data($val);
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
	 * @access  protected
	 * @param   string
	 * @return  string
	 */
	protected function clean_input_keys($str)
	{
		if ( ! preg_match('#^[\pL0-9:_/-]+$#uD', $str))
		{
			exit('Disallowed key characters in global data.');
		}

		return $str;
	}

	/**
	 * Fetch the IP Address
	 *
	 * @access  public
	 * @return  string
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
	 * @access  public
	 * @param   string
	 * @return  string
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
	 * @access  public
	 * @return  string
	 */
	public function user_agent()
	{
		if ($this->user_agent !== FALSE)
			return $this->user_agent;

		$this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;

		return $this->user_agent;
	}

	/**
	 * XSS Clean implemented by HTML Purifier
	 *
	 * Note: This function should only be used to deal with data upon submission.
	 * It's not something that should be used for general runtime processing
	 * since it requires a fair amount of processing overhead.
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	public function xss_clean($string, $tool = '')
	{
		$tool = ($tool != '') ? $tool : Config::item('core.global_xss_filtering');

		switch ($tool)
		{
			case 'htmlpurifier':
				/**
				 * @todo License should go here, http://htmlpurifier.org/
				 */
				require_once Kohana::find_file('vendor', 'htmlpurifier/HTMLPurifier.auto');
				require_once 'HTMLPurifier.func.php';

				// Set configuration
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML', 'TidyLevel', 'none'); // Only XSS cleaning now

				// Run HTMLPurifier
				$string = HTMLPurifier($string, $config);
			break;
			default:
				// http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
				// +----------------------------------------------------------------------+
				// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
				// +----------------------------------------------------------------------+
				// | Licensed under the Apache License, Version 2.0 (the "License");      |
				// | you may not use this file except in compliance with the License.     |
				// | You may obtain a copy of the License at                              |
				// | http://www.apache.org/licenses/LICENSE-2.0                           |
				// | Unless required by applicable law or agreed to in writing, software  |
				// | distributed under the License is distributed on an "AS IS" BASIS,    |
				// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
				// | implied. See the License for the specific language governing         |
				// | permissions and limitations under the License.                       |
				// +----------------------------------------------------------------------+
				// | Author: Christian Stocker <chregu@bitflux.ch>                        |
				// +----------------------------------------------------------------------+
				//
				// Kohana Modifications:
				// * Changed double quotes to single quotes, changed indenting and spacing
				// * Removed magic_quotes stuff
				// * Increased regex readability:
				//   * Used delimeters that aren't found in the pattern
				//   * Removed all unneeded escapes
				//   * Deleted U modifiers and swapped greediness where needed
				// * Increased regex speed:
				//   * Made capturing parentheses non-capturing where possible
				//   * Removed parentheses where possible
				//   * Split up alternation alternatives

				$string = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $string);
				// fix &entitiy\n;

				$string = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $string);
				$string = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $string);
				$string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');

				// remove any attribute starting with "on" or xmlns
				$string = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*>#iu', '$1>', $string);
				// remove javascript: and vbscript: protocol
				$string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $string);
				$string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $string);
				$string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $string);
				//<span style="width: expression(alert('Ping!'));"></span>
				// only works in ie...
				$string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*>#i', '$1>', $string);
				$string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*>#i', '$1>', $string);
				$string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iu', '$1>', $string);
				//remove namespaced elements (we do not need them...)
				$string = preg_replace('#</*\w+:\w[^>]*>#i', '',$string);
				//remove really unwanted tags

				do {
					$oldstring = $string;
					$string = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*>#i', '', $string);
				} while ($oldstring != $string);
			break;
		}

		return $string;
	}

} // End Input Class