<?php

namespace Driver\Session

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Session cookie driver.
 *
 * $Id: Cookie.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Cookie implements \Driver\Session {

	protected $cookie_name;
	protected $encrypt; // Library

	public function __construct()
	{
		$this->cookie_name = Kohana::config('session.name').'_data';

		if (\Kernel\Kohana::config('session.encryption'))
		{
			$this->encrypt = \Library\Encrypt::instance();
		}

		\Library\Kohana_Log::add('debug', 'Session Cookie Driver Initialized');
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
		$data = (string) \Helper\cookie::get($this->cookie_name);

		if ($data == '')
			return $data;

		return empty($this->encrypt) ? base64_decode($data) : $this->encrypt->decode($data);
	}

	public function write($id, $data)
	{
		if ( ! Session::$should_save)
			return TRUE;

		$data = empty($this->encrypt) ? base64_encode($data) : $this->encrypt->encode($data);

		if (strlen($data) > 4048)
		{
			\Library\Kohana_Log::add('error', 'Session ('.$id.') data exceeds the 4KB limit, ignoring write.');
			return FALSE;
		}

		return \Helper\cookie::set($this->cookie_name, $data, \Kernel\Kohana::config('session.expiration'));
	}

	public function destroy($id)
	{
		return \Helper\cookie::delete($this->cookie_name);
	}

	public function regenerate()
	{
		session_regenerate_id(TRUE);

		// Return new id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		return TRUE;
	}

} // End Session Cookie Driver Class