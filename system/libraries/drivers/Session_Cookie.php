<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Session_Cookie_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
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

		if ($this->encryption == TRUE AND $data != '')
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

	public function regenerate()
	{
		session_regenerate_id(TRUE);

		// Return new id
		return session_id();
	}

	public function gc()
	{
		return TRUE;
	}

	/**
	 * Method: setcookie
	 *  Proxy for setcookie()
	 *
	 * Parameters:
	 *  data       - session data
	 *  expiration - cookie expiration
	 *
	 * Returns:
	 *  TRUE or FALSE
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