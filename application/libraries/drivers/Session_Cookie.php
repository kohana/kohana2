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

	function Session_Cookie($config)
	{
		foreach(((array) $config) as $key => $val)
		{
			$this->$key = $val;
		}

		// Load necessary classes
		$this->input =& load_class('Input');
		if ($this->encryption == TRUE)
		{
			$this->encrypt =& load_class('Encrypt');
		}

		if ($this->expiration == 0)
		{
			// Set "no expiration" to two years
			$this->expiration = 60*60*24*365*2;
		}

		$this->cookie_name = config_item('cookie_prefix').$this->name;

		log_message('debug', 'Session Cookie Driver Initialized');
	}

	function open()
	{
		return TRUE;
	}

	function close()
	{
		$this->gc();
		return TRUE;
	}

	function read($id)
	{
		$data = $this->input->cookie($this->cookie_name);

		if ($this->encryption == TRUE)
		{
			$data = $this->encrypt->decode($data);
		}

		return $data;
	}

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

		return $this->_setcookie($data, ($this->expiration + time()));
	}

	function _setcookie($data, $expiration)
	{
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

	function destroy()
	{
		unset($_COOKIE[$this->cookie_name]);

		return $this->_setcookie('', (time() - 86400));
	}

	function gc()
	{
		return TRUE;

		if ((rand(0, 100)) % 50 == 0)
		{
			log_message('info', 'Session garbage collected');
		}
	}
}

?>