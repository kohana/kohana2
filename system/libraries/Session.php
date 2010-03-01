<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Session library.
 *
 * ##### Starting a session and loading the library
 *
 *     // This is the idiomatic and preferred method of starting a session
 *     $session = Session::instance();
 *
 *     // If any current session data exists, it will become available. If 
 *     // no session data exists, a new session is automatically started.
 *
 *     //----------Storage
 *
 *     // By default, session data is stored in a cookie. You can change 
 *     // this in the file "config/session.php".
 *
 *     // Note: the cookie driver limits session data to 4KB, while the database 
 *     // driver limits session data to 64KB.
 *
 *     //----------Storage: The Database Driver
 *
 *     // When using the database session driver, a session table must exist. The
 *     // session library will, by default, use the database you have configured
 *     // in your default database group.
 *     // The name of the session table can be configured in the config file.
 *
 *     // Example configuration for using the database:
 *     $config['driver']  = 'database';
 *     $config['storage'] =  array(
 *         'group' => 'default',  // or use 'default'
 *         'table' => 'sessions'  // or use 'sessions'
 *     );
 *
 *     // A recommended MySQL CREATE TABLE statement:
 *     CREATE TABLE sessions
 *     (
 *         session_id VARCHAR(127) NOT NULL,
 *         last_activity INT(10) UNSIGNED NOT NULL,
 *         DATA TEXT NOT NULL,
 *         PRIMARY KEY (session_id)
 *     );
 *
 *     // A recommended PostgreSQL CREATE TABLE statement:
 *     // Note: in 2.4 the PostgreSQL db driver is not supported natively, it is however, available as a module.
 *     CREATE TABLE session (
 *       session_id varchar(127) NOT NULL,
 *       last_activity integer NOT NULL,
 *       "data" text NOT NULL,
 *       CONSTRAINT session_id_pkey PRIMARY KEY (session_id),
 *       CONSTRAINT last_activity_check CHECK (last_activity >= 0)
 *     );
 *
 *     //----------Storage: The Cache Driver
 *
 *     // Example configuration for using the cache driver:
 *     $config['driver']  = 'cache';
 *     $config['storage'] = array(
 *        'driver'   => 'apc',
 *        'requests' => 10000
 *     );
 *
 *     // The available cache storage containers are:
 *     // APC, eAccelerator, File, Memcache, Sqlite,
 *     // and Xcache.
 *
 *     //----------Storage: The Native Driver
 *
 *     // You may use the native PHP sessions which stores session data
 *     // on the filesystem using PHP's default facilities.
 *
 *     // Note: if you are using Debian/Ubuntu and default storage directory /var/lib/php5, then set gc_probability 
 *     // to 0 and let the Debian/Ubuntu cron job clean the directory.
 *
 *     //----------AJAX
 *
 *     // A note on making AJAX calls and the session library: special care must be taken in regards to the 
 *     // “regenerate” option, which is set to 3 by default (as of Kohana 2.3).
 *     // What can happen is, since AJAX calls are asynchronous, Kohana will consider certain AJAX requests 
 *     // that come “out of order” to have expired sessions, since a new session ID is generated every 3 calls.
 *
 *     // To avoid this, include the following line in your application's config/sessions.php:
 *     $config['regenerate'] = 0;
 *
 * $Id$
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Session_Core {

	// Session singleton
	protected static $instance;

	// Protected key names (cannot be set by the user)
	protected static $protect = array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_');

	// Configuration and driver
	protected static $config;
	protected static $driver;

	// Flash variables
	protected static $flash;

	// Input library
	protected $input;

	// Automatically save the session by default
	public static $should_save = true;

	/**
	 * Singleton instance of Session.
	 *
	 * ##### Example
	 *
	 *     // This is the idiomatic and preferred method of starting a session
	 *     $session = Session::instance();
	 *
	 * @param string Force a specific session_id
	 */
	public static function instance($session_id = NULL)
	{
		if (Session::$instance == NULL)
		{
			// Create a new instance
			new Session($session_id);
		}
		elseif( ! is_null($session_id) AND $session_id != session_id() )
		{
			throw new Kohana_Exception('A session (SID: :session:) is already open, cannot open the specified session (SID: :new_session:).', array(':session:' => session_id(), ':new_session:' => $session_id));
		}

		return Session::$instance;
	}

	/**
	 * Be sure to block the use of __clone.
	 */
	private function __clone(){}

	/**
	 * On first session instance creation, sets up the driver and creates session.
	 *
	 * @param string Force a specific session_id
	 */
	protected function __construct($session_id = NULL)
	{
		$this->input = Input::instance();

		// This part only needs to be run once
		if (Session::$instance === NULL)
		{
			// Load config
			Session::$config = Kohana::config('session');

			// Makes a mirrored array, eg: foo=foo
			Session::$protect = array_combine(Session::$protect, Session::$protect);

			// Configure garbage collection
			ini_set('session.gc_probability', (int) Session::$config['gc_probability']);
			ini_set('session.gc_divisor', 100);
			ini_set('session.gc_maxlifetime', (Session::$config['expiration'] == 0) ? 86400 : Session::$config['expiration']);

			// Create a new session
			$this->create(NULL, $session_id);

			if (Session::$config['regenerate'] > 0 AND ($_SESSION['total_hits'] % Session::$config['regenerate']) === 0)
			{
				// Regenerate session id and update session cookie
				$this->regenerate();
			}
			else
			{
				// Always update session cookie to keep the session alive
				cookie::set(Session::$config['name'], $_SESSION['session_id'], Session::$config['expiration']);
			}

			// Close the session on system shutdown (run before sending the headers), so that
			// the session cookie(s) can be written.
			Event::add('system.shutdown', array($this, 'write_close'));

			// Singleton instance
			Session::$instance = $this;
		}

		Kohana_Log::add('debug', 'Session Library initialized');
	}

	/**
	 * Get the session id.
	 *
	 * ##### Example
	 *
	 *     // Retrieve the current session id
	 *     $session_id	= $session->id(); // Alternatively, you may use Session::instance()->id();
	 *
	 * @return  string
	 */
	public function id()
	{
		return $_SESSION['session_id'];
	}

	/**
	 * Create a new session.
	 *
	 * ##### Example
	 *
	 *     // Use this method to create a new session. It also destroys any current session data.
	 *     $session->create(); // Alternatively, you may use (preferred method) Session::instance();
	 *
	 *     // Note: you do not need to call this method to enable sessions. Simply loading the Session 
	 *     // library is enough to create a new session, or retrieve data from an existing session.
	 *
	 * @param   array  variables to set after creation
	 * @param   string Force a specific session_id
	 * @return  void
	 */
	public function create($vars = NULL, $session_id = NULL)
	{
		// Destroy any current sessions
		$this->destroy();

		if (Session::$config['driver'] !== 'native')
		{
			// Set driver name
			$driver = 'Session_'.ucfirst(Session::$config['driver']).'_Driver';

			// Load the driver
			if ( ! Kohana::auto_load($driver))
				throw new Kohana_Exception('The :driver: driver for the :library: library could not be found',
										   array(':driver:' => Session::$config['driver'], ':library:' => get_class($this)));

			// Initialize the driver
			Session::$driver = new $driver();

			// Validate the driver
			if ( ! (Session::$driver instanceof Session_Driver))
				throw new Kohana_Exception('The :driver: driver for the :library: library must implement the :interface: interface',
										   array(':driver:' => Session::$config['driver'], ':library:' => get_class($this), ':interface:' => 'Session_Driver'));

			// Register non-native driver as the session handler
			session_set_save_handler
			(
				array(Session::$driver, 'open'),
				array(Session::$driver, 'close'),
				array(Session::$driver, 'read'),
				array(Session::$driver, 'write'),
				array(Session::$driver, 'destroy'),
				array(Session::$driver, 'gc')
			);
		}

		// Validate the session name
		if ( ! preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', Session::$config['name']))
			throw new Kohana_Exception('The session_name, :session:, is invalid. It must contain only alphanumeric characters and underscores. Also at least one letter must be present.', array(':session:' => Session::$config['name']));

		// Name the session, this will also be the name of the cookie
		session_name(Session::$config['name']);

		// Set the session cookie parameters
		session_set_cookie_params
		(
			Session::$config['expiration'],
			Kohana::config('cookie.path'),
			Kohana::config('cookie.domain'),
			Kohana::config('cookie.secure'),
			Kohana::config('cookie.httponly')
		);

		$cookie = cookie::get(Session::$config['name']);
		
		if ($session_id === NULL)
		{
			// Reopen session from signed cookie value.
			$session_id = $cookie;
		}

		// Reopen an existing session if supplied
		if ( ! is_null($session_id))
		{
			session_id($session_id);
		}

		// Start the session!
		session_start();
		
		// Put session_id in the session variable
		$_SESSION['session_id'] = session_id();

		// Set defaults
		if ( ! isset($_SESSION['_kf_flash_']))
		{
			$_SESSION['total_hits'] = 0;
			$_SESSION['_kf_flash_'] = array();

			$_SESSION['user_agent'] = request::user_agent();
			$_SESSION['ip_address'] = $this->input->ip_address();
		}

		// Set up flash variables
		Session::$flash =& $_SESSION['_kf_flash_'];

		// Increase total hits
		$_SESSION['total_hits'] += 1;

		// Validate data only on hits after one
		if ($_SESSION['total_hits'] > 1)
		{
			// Validate the session
			foreach (Session::$config['validate'] as $valid)
			{
				switch ($valid)
				{
					// Check user agent for consistency
					case 'user_agent':
						if ($_SESSION[$valid] !== request::user_agent())
							return $this->create();
					break;

					// Check ip address for consistency
					case 'ip_address':
						if ($_SESSION[$valid] !== $this->input->$valid())
							return $this->create();
					break;

					// Check expiration time to prevent users from manually modifying it
					case 'expiration':
						if (time() - $_SESSION['last_activity'] > ini_get('session.gc_maxlifetime'))
							return $this->create();
					break;
				}
			}
		}

		// Expire flash keys
		$this->expire_flash();

		// Update last activity
		$_SESSION['last_activity'] = time();

		// Set the new data
		Session::set($vars);
	}

	/**
	 * Regenerates the global session id.
	 *
	 * ##### Example
	 *
	 *     // The session ID to be regenerated whilst keeping all the current session data intact with:
	 *     $session->regenerate(); // Alternatively, you may use Session::instance()->regenerate();
	 *
	 *     // This can be done automatically by setting the session.regenerate config value to an integer 
	 *     // greater than 0 (default value is 0). However, automatic session regeneration isn't recommended 
	 *     // because it can cause a race condition when you have multiple session requests while regenerating 
	 *     // the session id (most commonly noticed with ajax requests). For security reasons it's recommended 
	 *     // that you manually call regenerate() whenever a visitor's session privileges are escalated 
	 *     // (e.g. they logged in, accessed a restricted area, etc).
	 *
	 * @return  void
	 */
	public function regenerate()
	{
		if (Session::$config['driver'] === 'native')
		{
			// Generate a new session id
			// Note: also sets a new session cookie with the updated id
			session_regenerate_id(TRUE);

			// Update session with new id
			$_SESSION['session_id'] = session_id();
		}
		else
		{
			// Pass the regenerating off to the driver in case it wants to do anything special
			$_SESSION['session_id'] = Session::$driver->regenerate();
		}

		// Get the session name
		$name = session_name();

		if (isset($_COOKIE[$name]))
		{
			// Change the cookie value to match the new session id to prevent "lag"
			cookie::set($name, $_SESSION['session_id']);
		}
	}

	/**
	 * Destroys the current session.
	 *
	 * ##### Example
	 *
	 *     // To destroy all session data, including the browser cookie that is used to identify the session, use:
	 *     $session->destroy(); // Alternatively, you may use Session::instance()->destroy();
	 * 
	 * @return  void
	 */
	public function destroy()
	{
		if (session_id() !== '')
		{
			// Get the session name
			$name = session_name();

			// Destroy the session
			session_destroy();

			// Re-initialize the array
			$_SESSION = array();

			// Delete the session cookie
			cookie::delete($name);
		}
	}

	/**
	 * Runs the system.session_write event, then calls session_write_close.
	 *
	 * ##### Example
	 *
	 *     // This method is only be useful in special cases
	 *     $session->write_close(); // Alternatively, you may use Session::instance()->write_close();
	 *
	 * @return  void
	 */
	public function write_close()
	{
		static $run;

		if ($run === NULL)
		{
			$run = TRUE;

			// Run the events that depend on the session being open
			Event::run('system.session_write');

			// Expire flash keys
			$this->expire_flash();

			// Close the session
			session_write_close();
		}
	}

	/**
	 * Set a session variable.
	 *
	 * ##### Example
	 *
	 *     // This method can be used to set a session key with a value, however it is *not* idiomatic nor preferred
	 *     $session->set('username', 'zombocom'); // Alternatively, you may use Session::instance()->set('username', 'zombocom');
	 *
	 *     // The preferred method is to make use of the native $_SESSION global; the Kohana Session library uses
	 *     // the session_set_save_handler() function to enable the use of the $_SESSION global with database storage,
	 *     // cache, cookies, and native.
	 *     $_SESSION['username'] = 'zombocom'; // If using the database storage driver, Kohana will transparently set and retrieve this data from the DB
	 *
	 * @param   string|array  key, or array of values
	 * @param   mixed         value (if keys is not an array)
	 * @return  void
	 */
	public function set($keys, $val = FALSE)
	{
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val)
		{
			if (isset(Session::$protect[$key]))
				continue;

			// Set the key
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Set a flash variable.
	 *
	 * ##### Example
	 *
	 *     // “Flash” session data is data that persists only until the next request. It could, for example, be used to show a message to a user only once.
	 *     // As with other session data, you retrieve flash data using the $_SESSION global or Session::instance()->get().
	 *
	 *     // set user_message flash session data
	 *     $session->set_flash('user_message', 'Hello, how are you?'); // Alternatively, you may use Session::instance()->set_flash();
	 *
	 *     // set several pieces of flash session data at once by passing an array
     *     $session->set_flash(array('user_message' => 'How are you?', 'fish' => 5));
	 * 
	 * @param   string|array  key, or array of values
	 * @param   mixed         value (if keys is not an array)
	 * @return  void
	 */
	public function set_flash($keys, $val = FALSE)
	{
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val)
		{
			if ($key == FALSE)
				continue;

			Session::$flash[$key] = 'new';
			Session::set($key, $val);
		}
	}

	/**
	 * Freshen one, multiple or all flash variables.
	 *
	 * ##### Example
	 *
     *     // Usually, flash data is automatically deleted after the next request. Sometimes this 
	 *     // behaviour is not desired, though. For example, the next request might be an AJAX 
	 *     // request for some data. In this case, you wouldn't want to delete your user_message 
	 *     // in the example above because it wouldn't have been shown to the user by the AJAX request.
	 *
     *     // Don't delete the user_message this request
     *     $session->keep_flash('user_message'); // Alternatively, you may use Session::instance()->keep_flash();
	 *
	 *     // Don't delete messages 1-3
     *     $session->keep_flash('message1', 'message2', 'message3');
	 *
	 *     // Don't delete any flash variable
     *     $this->session->keep_flash();
	 *
	 * @param   string  variable key(s)
	 * @return  void
	 */
	public function keep_flash($keys = NULL)
	{
		$keys = ($keys === NULL) ? array_keys(Session::$flash) : func_get_args();

		foreach ($keys as $key)
		{
			if (isset(Session::$flash[$key]))
			{
				Session::$flash[$key] = 'new';
			}
		}
	}

	/**
	 * Expires old flash data and removes it from the session.
	 *
	 * ##### Example
	 *
     *     // This method can be used to artificially expire flash data.
	 *     $session->expire_flash(); // Alternatively, you may use Session::instance()->expire_flash();
	 *
	 * @return  void
	 */
	public function expire_flash()
	{
		static $run;

		// Method can only be run once
		if ($run === TRUE)
			return;

		if ( ! empty(Session::$flash))
		{
			foreach (Session::$flash as $key => $state)
			{
				if ($state === 'old')
				{
					// Flash has expired
					unset(Session::$flash[$key], $_SESSION[$key]);
				}
				else
				{
					// Flash will expire
					Session::$flash[$key] = 'old';
				}
			}
		}

		// Method has been run
		$run = TRUE;
	}

	/**
	 * Get a variable. Access to sub-arrays is supported with key.subkey.
	 *
	 * ##### Example
	 *
	 *     // This method can be used to get a session value by its key, however it is *not* idiomatic nor preferred
	 *     echo $session->get('username'); // Alternatively, you may use Session::instance()->get('username');
	 *
	 *     // Outputs:
	 *     zombocom
	 *
	 *     // You may also specify a default value to be returned if the provided session key is not set
	 *     echo $session->get('username', 'default_value');
	 *
	 *     // Outputs (if "username" is not set):
	 *     default_value
	 *
	 *     // The preferred method is to make use of the native $_SESSION global; the Kohana Session library uses
	 *     // the session_set_save_handler() function to enable the use of the $_SESSION global with database storage,
	 *     // cache, cookies, and native.
	 *     echo $_SESSION['username']; // If using the database storage driver, Kohana will transparently set and retrieve this data from the DB
	 *
	 *     // Outputs:
	 *     zombocom
	 *
	 * @param   string  variable key
	 * @param   mixed   default value returned if variable does not exist
	 * @return  mixed   Variable data if key specified, otherwise array containing all session data.
	 */
	public function get($key = FALSE, $default = FALSE)
	{
		if (empty($key))
			return $_SESSION;

		$result = isset($_SESSION[$key]) ? $_SESSION[$key] : Kohana::key_string($_SESSION, $key);

		return ($result === NULL) ? $default : $result;
	}

	/**
	 * Get a variable, and delete it.
	 *
	 * ##### Example
	 *
	 *     // Retrieve a value from the session using a key and remove it immediately
	 *     echo $session->get_once('username'); // Alternatively, you may use Session::get_once();
	 *
	 *     // Outputs:
	 *     zombocom
	 *
	 *     // Like get(), you may specify a default value to be returned
	 *     echo $session->get_once('username', 'default_value');
	 *
	 *     // Outputs (if "username" is not set):
	 *     default_value
	 *
	 * @param   string  variable key
	 * @param   mixed   default value returned if variable does not exist
	 * @return  mixed
	 */
	public function get_once($key, $default = FALSE)
	{
		$return = Session::get($key, $default);
		Session::delete($key);

		return $return;
	}

	/**
	 * Delete one or more variables.
	 *
	 * ##### Example
	 *
	 *     // Delete an item from the session (not recommended). A variable number of arguments may be provided to delete multiple items.
	 *     $session->delete('username'); // Alternatively, you may use Session::delete()
	 *
	 *     // It is idiomatic and preferred to use the native $_SESSION global array and unset()
	 *     unset($_SESSION['username']);
	 *
	 * @param   string  variable key(s)
	 * @return  void
	 */
	public function delete($keys)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			if (isset(Session::$protect[$key]))
				continue;

			// Unset the key
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Do not save this session.
	 * This is a performance feature only, if using the native
	 * session "driver" the save will NOT be aborted.
	 *
	 * ##### Example
	 *
	 *     // This method is only be useful in special cases
	 *     $session->abort_save(); // Alternatively, you may use Session::abort_save();
	 *
	 * @return  void
	 */
	public function abort_save()
	{
		Session::$should_save = FALSE;
	}

} // End Session Class
