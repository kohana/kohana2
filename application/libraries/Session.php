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
 * Session Class
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Sessions
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/sessions.html
 */
class Core_Session {

	var $conf;
	var $protected = array('session_id', 'ip_address', 'user_agent', 'last_activity', 'total_hits');

	function Core_Session()
	{
		// Load session config
		foreach(array('driver', 'name', 'match', 'expiration', 'encryption', 'regenerate') as $var)
		{
			$this->conf[$var] = config_item('session_'.$var);
		}

		// Load driver
		$driver = strtolower(config_item('session_driver'));
		$this->_load_driver($driver);

		log_message('debug', 'Session Class Initialized');
	}

	function _load_driver($driver)
	{
		static $loaded;
		// We can only load the driver once
		// Native backend does not require a driver
		if ($loaded == TRUE)
			return TRUE;

		if ($driver != 'native')
		{
			$driver = ucfirst($driver);
			$loader =& load_class('Loader');

			if ( ! $file = $loader->_find_driver('Session', ucfirst($driver)))
			{
				show_error('The Session driver you have configured does not exist: '.$driver);
			}
			elseif ( ! $api = $loader->_find_driver('Session', 'Driver'))
			{
				show_error('The Session API must be available for drivers to be loaded');
			}
			else
			{
				require($api);
				require($file);
			}

			// Load the driver
			$class = 'Session_'.$driver;
			$this->_driver =& new $class($this->conf);
			// Make sure that the driver is actually an extension of the API
			if ( ! is_subclass_of($this->_driver, 'Session_Driver'))
			{
				show_error('Invalid Session driver configured: '.$driver);
			}

			// Register driver as the session handler
			$this->_register();
		}
		$this->create();

		add_shutdown_event('session_write_close');
		$loaded = TRUE;
	}

	function _register()
	{
		if ($this->conf['driver'] != 'native')
		{
			// Destroy any auto created sessions
			if (@ini_get('session.auto_start') == TRUE)
			{
				session_destroy();
			}

			// Register driver as the session handler
			session_set_save_handler
			(
				array(&$this->_driver, 'open'),
				array(&$this->_driver, 'close'),
				array(&$this->_driver, 'read'),
				array(&$this->_driver, 'write'),
				array(&$this->_driver, 'destroy'),
				array(&$this->_driver, 'gc')
			);
		}
	}

	function create()
	{
		$this->_register();

		if ( ! isset($_SESSION['session_id']))
		{
			session_name($this->conf['name']);
			session_start();
		}
		else
		{
			session_unset();
		}

		return $this->_validate();
	}

	function destroy()
	{
		return session_destroy();
	}

	function regenerate()
	{
		// We use a 7 character hash of the user's IP address for a id prefix
		// to prevent collisions. This should be very safe.
		$input =& load_class('Input');
		$session_id = substr(sha1($input->ip_address()), 0, 7);

		session_id(uniqid($session_id));

		$_SESSION['session_id'] = session_id();
	}

	function _validate()
	{
		// Set defaults
		$input =& load_class('Input');
		if ( ! isset($_SESSION['last_activity']))
		{
			session_unset();
			$this->regenerate();
			$_SESSION['user_agent']    = $input->user_agent();
			$_SESSION['last_activity'] = time();
			$_SESSION['ip_address']    = $input->ip_address();
			$_SESSION['total_hits']    = 1;

			return TRUE;
		}

		// Process config defined checks
		foreach($this->conf['match'] as $var)
		{
			switch($var)
			{
				case 'user_agent':
				case 'ip_address':
					if ($_SESSION[$var] != $input->$var())
					{
						session_unset();
						return $this->_validate();
					}
				break;
			}
		}

		// Regenerate session ID
		if (($_SESSION['total_hits'] % $this->conf['regenerate']) === 0)
		{
			$this->regenerate();
		}

		// Update the last activity and add another hit
		$_SESSION['last_activity'] = time();
		$_SESSION['total_hits']   += 1;

		return TRUE;
	}



}

?>