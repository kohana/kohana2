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
 * Session Cookie Driver
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Sessions
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/sessions.html
 */
class Session_Cookie extends Session_Driver {

	var $input;
	var $encrypt;
	var $cookie_name;

	/**
	 * Constructor
	 */
	function Session_Cookie($config)
	{
		foreach(((array) $config) as $key => $val)
		{
			$this->$key = $val;
		}
		$this->cookie_name = config_item('cookie_prefix').$this->name;

		// Load necessary classes
		$this->input =& load_class('Input');
		if ($this->encryption == TRUE)
		{
			$this->encrypt =& load_class('Encrypt');
		}

		// Set "no expiration" to two years
		if ($this->expiration == 0)
		{
			$this->expiration = 60*60*24*365*2;
		}

		log_message('debug', 'Session Cookie Driver Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Open the session
	 *
	 * @access	public
	 * @return	bool
	 */
	function open()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Close the session
	 *
	 * @access	public
	 * @return	bool
	 */
	function close()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Read a session
	 *
	 * @access	public
	 * @param	string	session id
	 * @return	string
	 */
	function read($id)
	{
		$data = $this->input->cookie($this->cookie_name);

		if ($this->encryption == TRUE)
		{
			$data = $this->encrypt->decode($data);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Write session data
	 *
	 * @access	public
	 * @param	string	session id
	 * @param	string	session data
	 * @return	bool
	 */
	function write($id, $data)
	{
		if ($this->encryption == TRUE)
		{
			$data = $this->encrypt->encode($data);
		}

		if (strlen($data) > 4048)
		{
			log_message('error', 'Session data exceeds the 4KB limit, ignoring write.');
			return FALSE;
		}

		return $this->_setcookie($data, (time() + $this->expiration));
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the session
	 *
	 * @access	public
	 * @return	bool
	 */
	function destroy()
	{
		unset($_COOKIE[$this->cookie_name]);

		return $this->_setcookie('', (time() - 86400));
	}

	// --------------------------------------------------------------------
	
	function regenerate()
	{
		// We use 13 characters of a hash of the user's IP address for
		// an id prefix to prevent collisions. This should be very safe.
		$sessid = sha1($this->input->ip_address());
		$_start = rand(0, strlen($sessid)-13);
		$sessid = substr($sessid, $_start, 13);

		session_id(uniqid($sessid));
	}

	/**
	 * Collect garbage
	 *
	 * @access	public
	 * @return	bool
	 */
	function gc()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Proxy for setcookie()
	 *
	 * @access	private
	 * @param	string	session data
	 * @param	integer	session expiration
	 * @return	void
	 */
	function _setcookie($data, $expiration)
	{
		return @setcookie
		(
			$this->cookie_name,
			$data,
			$expiration,
			config_item('cookie_path'),
			config_item('cookie_domain'),
			config_item('cookie_secure')
		);
	}
}
// END Session Cookie Driver Class
?>