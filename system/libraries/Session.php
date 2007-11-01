<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Session
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
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

	/*
	 * Method: __construct
	 *  On first session instance creation, sets up the driver and creates session.
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

	/*
	 * Method: id
	 *  Get the session id.
	 *
	 * Returns:
	 *  Session id
	 */
	public function id()
	{
		return $_SESSION['session_id'];
	}

	/*
	 * Method: create
	 *  Create a new session.
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

	/*
	 * Method: regenerate
	 *  Regenerates the global session id.
	 */
	public function regenerate()
	{
		// Thank god for small gifts
		session_regenerate_id(TRUE);

		// Update session with new id
		$_SESSION['session_id'] = session_id();
	}

	/*
	 * Method: destroy
	 *  Destroys the current session.
	 *
	 * Returns:
	 *  TRUE or FALSE
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

	/*
	 * Method: set
	 *  Set a session variable.
	 *
	 * Parameters:
	 *  keys - array of values, or key
	 *  val  - value (if keys is not an array)
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

	/*
	 * Method: set_flash
	 *  Set a flash variable.
	 *
	 * Parameters:
	 *  keys - array of values, or key
	 *  val  - value (if keys is not an array)
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

	/*
	 * Method: keep_flash
	 *  Freshen a flash variable.
	 *
	 * Parameters:
	 *  key - variable key
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

	/*
	 * Method: get
	 *  Get a variable.
	 *
	 * Parameters:
	 *  key     - variable key (optional)
	 *  default - default value returned if variable does not exist
	 *
	 * Returns:
	 *   Variable data if key specified, otherwise array containing all session data
	 */
	public function get($key = FALSE, $default = FALSE)
	{
		if ($key == FALSE)
			return $_SESSION;

		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/*
	 * Method: get_once
	 *  Get a variable, and delete it.
	 *
	 * Parameters:
	 *  key - variable key (optional)
	 *
	 * Returns:
	 *   Variable data if key specified, otherwise array containing all session data
	 */
	public function get_once($key)
	{
		$return = self::get($key);
		self::del($key);

		return $return;
	}

	/*
	 * Method: del
	 *  Delete a variable.
	 *
	 * Parameters:
	 *  key - variable key
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