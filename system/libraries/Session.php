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

	protected $config;
	protected $driver;
	protected $protect;

	/**
	 * Session Constructor
	 */
	public function __construct()
	{
		$this->config = Config::item('session');

		// Set protected keys
		$this->protect = array_combine
		(
			array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_'),
			array_fill(0, 6, TRUE)
		);
		// Native backend does not require a driver
		if ($this->config['driver'] != 'native')
		{
			$driver = 'Session_'.ucfirst(strtolower($this->config['driver']));

			require Kohana::find_file('libraries', 'drivers/'.$driver, TRUE);

			$this->driver = new $driver();

			$implements = class_implements($this->driver);

			if ( ! isset($implements['Session_Driver']))
			{
				/**
				 * @todo This should be an i18n error
				 */
				trigger_error('Session drivers must be use the Session_Driver interface.');
			}

			// Destroy any auto created sessions
			if (@ini_get('session.auto_start') == TRUE)
			{
				unset($_COOKIE[session_name()]);
				session_destroy();
			}

			// Register driver as the session handler
			session_set_save_handler
			(
				array($this->driver, 'open'),
				array($this->driver, 'close'),
				array($this->driver, 'read'),
				array($this->driver, 'write'),
				array($this->driver, 'destroy'),
				array($this->driver, 'gc')
			);
		}

		// Create or load a session
		$this->create();

		Event::add('system.pre_output', 'session_write_close');

		Log::add('debug', 'Session Class Initialized');
	}

	// --------------------------------------------------------------------

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

	// --------------------------------------------------------------------

	/**
	 * Create a new session
	 *
	 * @access public
	 * @return void
	 */
	public function create($vars = NULL)
	{
		if (isset($_SESSION) AND isset($_SESSION['session_id']))
		{
			session_unset();
		}
		else
		{
			session_name($this->config['name']);
			session_start();
		}

		// Set up flash variables
		$this->flash = $_SESSION['_kf_flash_'] = array();

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

		$this->validate();
		$this->set($vars);
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @access public
	 * @return bool
	 */
	public function destroy()
	{
		return session_destroy();
	}

	// --------------------------------------------------------------------

	/**
	 * Regenerate the session id
	 *
	 * @access public
	 * @return void
	 */
	public function regenerate()
	{
		if ($this->config['driver'] == 'native')
		{
			session_regenerate_id(TRUE);
		}
		else
		{
			$this->driver->regenerate();
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
			if (isset($this->safe_keys[$key]))
				continue;

			$_SESSION[$key] = $val;
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

			$this->flash[$key] = 'new';
			$this->set($key, $val);
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
	public function keep_flash($key)
	{
		if (isset($this->flash[$key]))
		{
			$this->flash[$key] = 'new';
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

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

	// --------------------------------------------------------------------

	/**
	 * Get a variable, and delete it
	 *
	 * @access public
	 * @param  string  key (optional)
	 * @return mixed
	 */
	public function get_once($key)
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

	// --------------------------------------------------------------------

	/**
	 * Validate the session
	 *
	 * @access  private
	 * @return  bool
	 */
	private function validate()
	{
		$input = new Input();

		// Set defaults
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
		foreach($this->config['validate'] as $var)
		{
			switch($var)
			{
				case 'user_agent':
				case 'ip_address':
					if ($_SESSION[$var] != $input->$var())
					{
						session_unset();
						return $this->validate();
					}
				break;
			}
		}

		// Regenerate session ID
		if (($_SESSION['total_hits'] % $this->config['regenerate']) === 0)
		{
			$this->regenerate();
		}

		// Update the last activity and add another hit
		$_SESSION['last_activity'] = time();
		$_SESSION['total_hits']   += 1;

		return TRUE;
	}

} // End Session Class