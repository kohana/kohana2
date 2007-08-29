<?php defined('SYSPATH') or die('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
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
class Session_Cookie implements Session_Driver {

	private $cookie_name = '';

	// Libraries
	private $input;
	private $encrypt;

	public function __construct()
	{
		$this->cookie_name = Config::item('cookie.prefix').Config::item('session.name');
		$this->expiration  = Config::item('session.expiration');
		$this->encryption  = Config::item('session.encryption');

		$this->input   = new Input();
		$this->encrypt = new Encrypt();

		Log::add('debug', 'Session Cookie Driver Initialized');
	}

	// --------------------------------------------------------------------

	public function open($path, $name)
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	public function close()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

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

		return $this->setcookie($data, (time() + $this->expiration));
	}

	// --------------------------------------------------------------------

	public function destroy($id)
	{
		unset($_COOKIE[$this->cookie_name]);

		return $this->setcookie(session_id(), (time() - 86400));
	}

	// --------------------------------------------------------------------

	public function regenerate()
	{
		// We use 13 characters of a hash of the user's IP address for
		// an id prefix to prevent collisions. This should be very safe.
		$sessid = sha1($this->input->ip_address());
		$_start = rand(0, strlen($sessid)-13);
		$sessid = substr($sessid, $_start, 13);
		$sessid = uniqid($sessid);

		// Set the new session id
		session_id($sessid);
	}

	// --------------------------------------------------------------------

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
	private function setcookie($data, $expiration)
	{
		static $sets;

		if (headers_sent())
			die(''.$sets);

		return setcookie
		(
			$this->cookie_name,
			$data,
			$expiration,
			Config::item('cookie.path'),
			Config::item('cookie.domain'),
			Config::item('cookie.secure')
		);
	}

} // End Session Cookie Driver Class