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
 * Session Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/session.html
 */
class Session_Core {

	// Number of instances of Session object
	private static $instances = 0;

	// Protected key names (cannot be set by the user)
	protected static $protect = array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_');

	// Configuration and driver
	protected static $config;
	protected static $driver;

	// Flash variables
	protected static $flash;

	// Input library
	protected $input;

	/**
	 * Session Constructor
	 */
	public function __construct()
	{
		$this->input = new Input();

		// This part only needs to be run once
		if (self::$instances === 0)
		{
			// Load config
			self::$config = Config::item('session');

			// Makes a mirrored array, eg: foo=foo
			self::$protect = array_combine(self::$protect, self::$protect);

			if (self::$config['driver'] != 'native')
			{
				try
				{
					// Set the driver name
					$driver = 'Session_'.ucfirst(strtolower(self::$config['driver'])).'_Driver';

					// Manually call auto-loading, for proper exception handling
					Kohana::auto_load($driver);

					// Initialize the driver
					self::$driver = new $driver();
				}
				catch (Kohana_Exception $e)
				{
					throw new Kohana_Exception('session.driver_not_supported', self::$config['driver']);
				}

				// Validate the driver
				if ( ! in_array('Session_Driver', class_implements(self::$driver)))
					throw new Kohana_Exception('session.driver_must_implement_interface');
			}

			// Create a new session
			$this->create();

			// Close the session just before sending the headers, so that
			// the session cookie can be written
			Event::add('system.send_headers', 'session_write_close');
		}

		if (($_SESSION['total_hits'] % self::$config['regenerate']) === 0)
		{
			// Regenerate session ID
			$this->regenerate();
		}

		// New instance
		self::$instances += 1;

		Log::add('debug', 'Session Library initialized');
	}


	/**
	 * Return the session id
	 *
	 * @access public
	 * @return string
	 */
	public function id()
	{
		return $_SESSION['session_id'];
	}

	/**
	 * Create a new session
	 *
	 * @access public
	 * @return void
	 */
	public function create($vars = NULL)
	{
		// Destroy the session
		$this->destroy();

		if (self::$config['driver'] != 'native')
		{
			// Register driver as the session handler
			session_set_save_handler
			(
				array(self::$driver, 'open'),
				array(self::$driver, 'close'),
				array(self::$driver, 'read'),
				array(self::$driver, 'write'),
				array(self::$driver, 'destroy'),
				array(self::$driver, 'gc')
			);
		}

		// Set the session name
		session_name(self::$config['name']);

		// Start the session!
		session_start();

		// Put session_id in the session variable
		$_SESSION['session_id'] = session_id();

		// Set defaults
		if ( ! isset($_SESSION['_kf_flash_']))
		{
			$_SESSION['user_agent'] = $this->input->user_agent();
			$_SESSION['ip_address'] = $this->input->ip_address();
			$_SESSION['_kf_flash_'] = array();
			$_SESSION['total_hits'] = 0;
		}

		// Set up flash variables
		self::$flash =& $_SESSION['_kf_flash_'];

		// Update constant session variables
		$_SESSION['last_activity'] = time();
		$_SESSION['total_hits']   += 1;

		// Validate data only on hits after one
		if ($_SESSION['total_hits'] > 1)
		{
			// Validate the session
			foreach(self::$config['validate'] as $valid)
			{
				switch($valid)
				{
					case 'user_agent':
					case 'ip_address':
						if ($_SESSION[$valid] !== $this->input->$valid())
						{
							session_unset();
							return $this->create();
						}
					break;
				}
			}

			// Remove old flash data
			if ( ! empty(self::$flash))
			{
				foreach(self::$flash as $key => $state)
				{
					if ($state == 'old')
					{
						self::del($key);
						unset(self::$flash[$key]);
					}
					else
					{
						self::$flash[$key] = 'old';
					}
				}
			}
		}

		// Set the new data
		self::set($vars);
	}

	/**
	 * Regenerates the global session id
	 *
	 * @access public
	 * @return void
	 */
	public function regenerate()
	{
		// Thank god for small gifts
		session_regenerate_id(TRUE);

		// Update session with new id
		$_SESSION['session_id'] = session_id();
	}

	/**
	 * Destroy the current session
	 *
	 * @access public
	 * @return bool
	 */
	public function destroy()
	{
		if (isset($_SESSION))
		{
			// Remove all session data
			session_unset();

			// Write the session
			return session_destroy();
		}
	}

	/**
	 * Set a session variable
	 *
	 * @access public
	 * @param  mixed   array of values, or key
	 * @param  mixed   value (optional)
	 * @return void
	 */
	public function set($keys, $val = FALSE)
	{
		if ($keys == FALSE)
			return;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach($keys as $key => $val)
		{
			if (isset(self::$protect[$key]))
				continue;

			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Set a flash variable
	 *
	 * @access public
	 * @param  mixed   array of values, or key
	 * @param  mixed   value (optional)
	 * @return void
	 */
	public function set_flash($keys, $val = FALSE)
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

			self::$flash[$key] = 'new';
			self::set($key, $val);
		}
	}

	/**
	 * Freshen a flash variable
	 *
	 * @access public
	 * @param  string  variable key
	 * @return bool
	 */
	public function keep_flash($key)
	{
		if (isset(self::$flash[$key]))
		{
			self::$flash[$key] = 'new';
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get a variable
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @param  mixed
	 * @return mixed
	 */
	public function get($key = FALSE, $default = FALSE)
	{
		if ($key == FALSE)
			return $_SESSION;

		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Get a variable, and delete it
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @return mixed
	 */
	public function get_once($key)
	{
		$return = self::get($key);
		self::del($key);

		return $return;
	}

	/**
	 * Delete a variable
	 *
	 * @access public
	 * @param  string  key
	 * @return void
	 */
	public function del($key)
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

} // End Session Class