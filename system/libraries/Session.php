<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Team.
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
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
	 * Generate a secure session id based on the user IP address
	 *
	 * @access public
	 * @return string
	 */
	public static function secure_id()
	{
		$input = new Input();

		// We use 13 characters of a hash of the user's IP address for
		// an id prefix to prevent collisions. This should be very safe.
		$sessid = sha1($input->ip_address());

		// Use 13 characters starting from a random point in the string, within
		// 13 places of the end, to prevent short strings
		$sessid = substr($sessid, rand(0, strlen($sessid)-13), 13);

		// Return the unique id
		return uniqid($sessid);
	}

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
				// Set the driver name
				$driver = 'Session_'.ucfirst(strtolower(self::$config['driver']));

				// Include the driver
				require Kohana::find_file('libraries', 'drivers/'.$driver, TRUE);

				// Initialize the driver
				self::$driver = new $driver();

				// Validate the driver
				if ( ! in_array('Session_Driver', class_implements(self::$driver)))
				{
					throw new Kohana_Exception('session.driver_must_implement_interface');
				}
			}

			// Create a new session
			$this->create();

			// Close the session just before flushing the output buffer
			Event::add('system.output', 'session_write_close');
		}

		// Regenerate session ID
		if (($_SESSION['total_hits'] % self::$config['regenerate']) === 0)
		{
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
		if (isset($_SESSION))
		{
			$this->destroy();
		}

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
		if ($_SESSION['total_hits'] === 1)
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
	 * Get a flash variable
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @return mixed
	 */
	public function get($key = FALSE)
	{
		if ($key == FALSE)
		{
			return $_SESSION;
		}

		return (isset($_SESSION[$key]) ? $_SESSION[$key] : FALSE);
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