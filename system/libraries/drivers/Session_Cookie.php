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
 * Session Cookie Driver
 *
 * @category    Session
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/session.html
 */
class Session_Cookie_Driver implements Session_Driver {

	protected $cookie_name = '';

	// Libraries
	protected $input;
	protected $encrypt;

	public function __construct()
	{
		$this->cookie_name = Config::item('cookie.prefix').Config::item('session.name').'_data';
		$this->expiration  = Config::item('session.expiration');
		$this->encryption  = Config::item('session.encryption');

		$this->input = new Input();

		if ($this->encryption == TRUE)
		{
			$this->encrypt = new Encrypt();
		}

		Log::add('debug', 'Session Cookie Driver Initialized');
	}

	public function open($path, $name)
	{
		return TRUE;
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		$data = $this->input->cookie($this->cookie_name);

		if ($this->encryption == TRUE)
		{
			$data = $this->encrypt->decode($data);
		}

		return $data;
	}

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

	public function destroy($id)
	{
		unset($_COOKIE[$this->cookie_name]);

		return $this->setcookie(session_id(), (time() - 86400));
	}


	public function gc()
	{
		return TRUE;
	}

	public function regenerate($new_id)
	{
		// Save the session data for re-insertion
		$save_data = $_SESSION;

		// Remove the session_id
		unset($save_data['session_id']);

		// Set the new session ID
		session_id($new_id);

		// Create a new session
		Session::create();

		// Merge in the old data, overwriting everything but the session_id
		$_SESSION = array_merge($_SESSION, $save_data);
	}

	/**
	 * Proxy for setcookie()
	 *
	 * @access	private
	 * @param	string	session data
	 * @param	integer	session expiration
	 * @return	void
	 */
	protected function setcookie($data, $expiration)
	{
		return headers_sent() ? FALSE : setcookie
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