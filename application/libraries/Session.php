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
	var $protected = array('session_id', 'ip_address', 'user_agent', 'last_activity');

	function Core_Session()
	{
		// Load session config
		foreach(array('name', 'match', 'expiration', 'encryption') as $var)
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
		if ($loaded == TRUE OR $driver == 'native')
			return ($loaded = TRUE);

		// If a session already exists, we need to remove it
		if ($id = session_id())
		{
			die('session exists, must die');
			if ($name = session_name() AND isset($_COOKIE[$name]))
			{
				// This will prevent the session from being restored
				setcookie($name, '', time()-86400, '/');
			}
			session_destroy();
		}

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
		$sess =& new $class($this->conf);
		// Make sure that the driver is actually an extension of the API
		if ( ! is_subclass_of($sess, 'Session_Driver'))
		{
			show_error('Invalid Session driver configured: '.$driver);
		}

		// Register driver as the session handler
		session_set_save_handler
		(
			array(&$sess, 'open'),
			array(&$sess, 'close'),
			array(&$sess, 'read'),
			array(&$sess, 'write'),
			array(&$sess, 'destroy'),
			array(&$sess, 'gc')
		);
		// And away we go!
		session_name($this->conf['name']);
		session_start();
		
		// Set defaults
		$input =& load_class('Input');
		if ( ! isset($_SESSION['last_activity']))
		{
			$_SESSION['session_id']    = session_id();
			$_SESSION['user_agent']    = md5($input->user_agent());
			$_SESSION['last_activity'] = time();
			$_SESSION['ip_address']    = $input->ip_address();
			$_SESSION['total_hits']    = 1;
		}
		elseif ($_SESSION['user_agent'] != md5($input->user_agent()))
		{
			session_destroy();
			session_start();
		}
		else
		{
			$_SESSION['last_activity'] = time();
			$_SESSION['total_hits']   += 1;
		}

		add_shutdown_event('session_write_close');
		$loaded = TRUE;
	}

}

?>