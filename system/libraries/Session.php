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

	var $driver     = 'cookie';
	var $name       = 'kohana_session';
	var $match      =  array('user_agent');
	var $encryption = FALSE;
	var $expiration = 7200;
	var $regenerate = 3;
	var $safe_keys  = array();

	/**
	 * Session Constructor
	 */
	function Core_Session()
	{
		// Load session config
		$_vars = array('driver', 'name', 'match', 'expiration', 'encryption', 'regenerate');
		foreach($_vars as $var)
		{
			$value = config_item('session_'.$var);
			$config[$var] = $this->$var = $value;
		}

		// Set protected keys
		$_vars = array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_');
		foreach($_vars as $var)
		{
			$this->safe_keys[$var] = TRUE;
		}

		// Load driver
		$this->_load_driver($config);

		log_message('debug', 'Session Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Return the session id
	 *
	 * @access public
	 * @return string
	 */
	function id()
	{
		return $_SESSION['session_id'];
	}

	// --------------------------------------------------------------------

	/**
	 * Create a new session
	 *
	 * @access public
	 * @return void
	 */
	function create($vars = NULL)
	{
		$this->_register();

		if ( ! isset($_SESSION['session_id']))
		{
			@session_name($this->name);
			@session_start();
		}
		else
		{
			@session_unset();
		}

		$this->_validate();
		$this->set($vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Save the Session
	 *
	 * @access public
	 * @return void
	 */
	function save($vars = NULL)
	{
		@session_write_close();
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @access public
	 * @return bool
	 */
	function destroy()
	{
		return @session_destroy();
	}

	// --------------------------------------------------------------------

	/**
	 * Regenerate the session id
	 *
	 * @access public
	 * @return void
	 */
	function regenerate()
	{
		if ($this->driver == 'native')
		{
			// PHP5 allows you to delete the old session when calling
			// session_regenerate_id. Naturally, PHP4 doesn't do this,
			// and we have to do it manually. :(
			if (KOHANA_IS_PHP5)
			{
				session_regenerate_id(TRUE);
			}
			else
			{
				$session_file = session_save_path().'/sess_'.session_id();
				// Delete the old session file if regeneration succeded
				if (session_regenerate_id() AND file_exists($session_file))
				{
					@unlink($session_file);
				}
			}
		}
		else
		{
			$this->_driver->regenerate();
		}

		$_SESSION['session_id'] = session_id();
	}

	// --------------------------------------------------------------------

	/**
	 * Set a session variable
	 *
	 * @access public
	 * @param  mixed   array of values, or key
	 * @param  mixed   value (optional)
	 * @return void
	 */
	function set($keys, $val = FALSE)
	{
		if ($keys == FALSE)
			return;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach($keys as $key => $val)
		{
			if (isset($this->safe_keys[$key]))
				continue;

			$_SESSION[$key] = $val;
		}

		// Because the cookie is sent with the headers, we can't register a
		// shutdown event for writing, so we call save() every set()
		if ($this->driver = 'cookie')
		{
			$this->save();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set a flash variable
	 *
	 * @access public
	 * @param  mixed   array of values, or key
	 * @param  mixed   value (optional)
	 * @return void
	 */
	function set_flash($keys, $val = FALSE)
	{
		if ($keys == FALSE)
			return;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach($keys as $key => $val)
		{
			if ($key == FALSE)
				continue;

			$this->set($key, $val);
			$this->flash[$key] = 'new';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Freshen a flash variable
	 *
	 * @access public
	 * @param  string  variable key
	 * @return bool
	 */
	function keep_flash($key)
	{
		if (isset($this->flash[$key]))
		{
			$this->flash[$key] = 'new';
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get a flash variable
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @return mixed
	 */
	function get($key = FALSE)
	{
		if ($key == FALSE)
		{
			return $_SESSION;
		}

		return (isset($_SESSION[$key]) ? $_SESSION[$key] : FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Get a variable, and delete it
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @return mixed
	 */
	function get_once($key)
	{
		$return = $this->get($key);
		$this->del($key);

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a variable
	 *
	 * @access public
	 * @param  string  key
	 * @return void
	 */
	function del($key)
	{
		if (is_array($key))
		{
			foreach($key as $k)
			{
				unset($_SESSION[$k]);
			}
		}
		else
		{
			unset($_SESSION[$key]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load the session driver
	 *
	 * @access private
	 * @param  array   configuration options
	 * @return void
	 */
	function _load_driver($config)
	{
		static $loaded;
		// Driver can only be loaded once
		if ($loaded == TRUE)
			return TRUE;

		// Native backend does not require a driver
		if ($this->driver != 'native')
		{
			$driver = ucfirst(strtolower($this->driver));
			$loader =& load_class('Loader');

			if ( ! $file = $loader->_find_driver('Session', $driver))
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
			$this->_driver =& new $class($config);
			// Make sure that the driver is actually an extension of the API
			if ( ! is_subclass_of($this->_driver, 'Session_Driver'))
			{
				show_error('Invalid Session driver configured: '.$driver);
			}

			// Register driver as the session handler
			$this->_register();
		}
		// Create or load a session
		$this->create();
		// Set up flash variables
		$this->_init_flash();

		// add_shutdown_event('session_write_close');
		$loaded = TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Register a driver as the session handler
	 *
	 * @access	private
	 * @return	void
	 */
	function _register()
	{
		if ($this->driver != 'native')
		{
			// Destroy any auto created sessions
			if (@ini_get('session.auto_start') == TRUE)
			{
				unset($_COOKIE[session_name()]);
				@session_destroy();
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

	// --------------------------------------------------------------------

	/**
	 * Initialize flash variables
	 *
	 * @access	private
	 * @return	void
	 */
	function _init_flash()
	{
		$this->flash =& $_SESSION['_kf_flash_'];

		if (count($this->flash) > 0)
		{
			foreach($this->flash as $key => $state)
			{
				if ($state == 'old')
				{
					$this->del($key);
					unset($this->flash[$key]);
				}
				else
				{
					$this->flash[$key] = 'old';
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Validate the session
	 *
	 * @access	private
	 * @return	bool
	 */
	function _validate()
	{
		// Set defaults
		$input =& load_class('Input');
		if ( ! isset($_SESSION['last_activity']))
		{
			session_unset();
			$this->regenerate();
			// Set default session values
			$_SESSION['user_agent']    = $input->user_agent();
			$_SESSION['ip_address']    = $input->ip_address();
			$_SESSION['last_activity'] = time();
			$_SESSION['total_hits']    = 1;
			$_SESSION['_kf_flash_']    = array();

			return TRUE;
		}

		// Process config defined checks
		foreach($this->match as $var)
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
		if (($_SESSION['total_hits'] % $this->regenerate) === 0)
		{
			$this->regenerate();
		}

		// Update the last activity and add another hit
		$_SESSION['last_activity'] = time();
		$_SESSION['total_hits']   += 1;

		return TRUE;
	}

}
// END Session Class
?>