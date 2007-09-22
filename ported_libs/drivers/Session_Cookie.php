<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
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
	public function __construct($config)
	{
		parent::Session_Driver($config);

		$this->cookie_name = config_item('cookie_prefix').$this->name;

		Log::add('debug', 'Session Cookie Driver Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Open the session
	 *
	 * @access	public
	 * @return	bool
	 */
	public function open()
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
	public function close()
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
	public function read($id)
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
	public function write($id, $data)
	{
		if ($this->encryption == TRUE)
		{
			$data = $this->encrypt->encode($data);
		}

		if (strlen($data) > 4048)
		{
			Log::add('error', 'Session data exceeds the 4KB limit, ignoring write.');
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
	public function destroy()
	{
		unset($_COOKIE[$this->cookie_name]);

		return $this->_setcookie(session_id(), (time() - 86400));
	}

	// --------------------------------------------------------------------

	public function regenerate()
	{
		session_id(parent::regenerate());
	}

	/**
	 * Collect garbage
	 *
	 * @access	public
	 * @return	bool
	 */
	public function gc()
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
	private function _setcookie($data, $expiration)
	{
		if (headers_sent())
			return;

		return setcookie
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