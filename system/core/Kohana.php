<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework for PHP5
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 */

// ----------------------------------------------------------------------------

/**
 * Kohana class
 *
 * @category    Core
 * @author      Kohana Development Team
 * @link        http://kohanaphp.com/user_guide/core_classes.html
 */
class Kohana {

	public static $buffer_level = 0;       // Ouput buffering level
	public static $error_types  = array(); // Human readable error types
	public static $registry     = array(); // Library registery

	public static $output = '';

	private static $instance = FALSE;   // Controller instance

	/**
	 * Constructor
	 *
	 * Called by the controller constructor, this piece of magic allows the
	 * controller to be a true singleton class
	 *
	 * @access  protected
	 * @return  void
	 */
	public function __construct()
	{
		if (self::$instance == FALSE)
		{
			self::$instance = $this;
		}
		else
		{
			trigger_error('<em>&#8220;There can be only one [instance of Kohana]!&#8220;</em>', E_USER_ERROR);
		}
	}

	/**
	 * Clone Protector
	 *
	 * @access public
	 * @return error
	 */
	final public function __clone()
	{
		$this->__construct();
	}

	/**
	 * PHP Preparation and Setup Routine
	 *
	 * This function prepares PHP's error/exception handling, output buffering,
	 * and adds an auto-loading method for loading classes
	 *
	 * @access  public
	 * @return  void
	 */
	public static function setup()
	{
		static $run;
		// This function can only be run once
		if ($run === TRUE) return;

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Start output buffering
		ob_start(array('Kohana', 'output'));

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		defined('E_RECOVERABLE_ERROR')  or define('E_RECOVERABLE_ERROR',  4096);
		defined('E_UNCAUGHT_EXCEPTION') or define('E_UNCAUGHT_EXCEPTION', 4097);

		// Set error types, format: CONSTANT => array($log_level, $message)
		self::$error_types = array
		(
			E_UNCAUGHT_EXCEPTION => array( 1, 'Uncaught Exception'),
			E_RECOVERABLE_ERROR  => array( 1, 'Recoverable Error'),
			E_ERROR              => array( 1, 'Fatal Error'),
			E_USER_ERROR         => array( 1, 'Fatal Error'),
			E_PARSE              => array( 1, 'Syntax Error'),
			E_WARNING            => array( 2, 'Warning Message'),
			E_USER_WARNING       => array( 2, 'Warning Warning'),
			E_STRICT             => array( 3, 'Strict Mode Error'),
			E_NOTICE             => array( 3, 'Runtime Message')
		);

		// Set error handler
		set_error_handler(array('Kohana', 'error_handler'));

		// Set execption handler
		set_exception_handler(array('Kohana', 'exception_handler'));

		// Enable log writing if the log threshold is enabled
		(Config::item('log.threshold') > 0) and Event::add('system.shutdown', array('Log', 'write'));

		// Set shutdown handler to run the "system.shutdown" event
		register_shutdown_function(array('Event', 'run'), 'system.shutdown');

		if (function_exists('date_default_timezone_set'))
		{
			// Set default timezone, due to increased validation of date settings
			$timezone = Config::item('core.timezone');
			$timezone = ($timezone == FALSE) ? @date_default_timezone_get() : $timezone;

			date_default_timezone_set($timezone);
		}

		// Setup is complete
		$run = TRUE;
	}

	/**
	 * Controller Initialization
	 *
	 * Loads the controller and instantiates it. The controller object is
	 * cached as Kohana::$instance
	 *
	 * @access public
	 * @return void
	 */
	public static function & instance()
	{
		if (self::$instance == FALSE)
		{
			//
			require (Router::$directory.Router::$controller.EXT);

			// Set controller class name
			$controller = ucfirst(Router::$controller).'_Controller';

			// Load the controller
			$controller = new $controller();
		}

		return self::$instance;
	}

	/**
	 * Output Handler
	 *
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function output($output)
	{
		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		// Bind the output to this output
		self::$output =& $output;

		// Run the pre_output Event
		// This can be used for functions that require completion before headers
		// are sent. One example is cookies, which are sent with the headers.
		Event::run('system.pre_output');

		// Replace the global template variables
		self::$output = str_replace(
			array
			(
				'{kohana_version}',
				'{execution_time}',
				'{memory_usage}'
			),
			array
			(
				KOHANA_VERSION,
				Benchmark::get(SYSTEM_BENCHMARK.'_total_execution_time'),
				number_format($memory, 2)
			),
			self::$output
		);

		return self::$output;
	}

	/**
	 * Error Handler
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  integer
	 * @return void
	 */
	public static function error_handler($error, $message, $file, $line)
	{
		// Do not display E_STRICT notices, they are garbage
		if ($error == E_STRICT) return FALSE;

		if (isset(self::$error_types[$error]))
		{
			list($level, $error) = self::$error_types[$error];
		}
		else
		{
			$level = 1;
			$error = 'Unknown Error';
		}

		// Remove the DOCROOT from the path, as a security precaution
		$file = preg_replace('#^'.preg_quote(DOCROOT).'#', '', $file);

		// Log the error
		if (Config::item('log.threshold') >= $level)
		{
			Log::add($error, $message.' in file: '.$file.' on line '.$line);
		}

		// Flush the entire buffer here, to ensure the error is displayed
		while(ob_get_level()) ob_end_clean();

		// Re-start the buffer
		ob_start(array('Kohana', 'output'));

		// Load the error page
		include self::find_file('views', 'kohana_php_error');

		// Display the buffer and exit
		ob_end_flush();
		exit;
	}

	/**
	 * Exception Handler
	 *
	 * @access public
	 * @param  object
	 * @return void
	 */
	public static function exception_handler($exception)
	{
		/**
		 * @todo This needs to choose a i18n message based on the type of exception + message
		 */
		self::error_handler
		(
			E_UNCAUGHT_EXCEPTION,
			get_class($exception).': '.$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		);
	}

	/**
	 * Autoloader
	 *
	 * @access public
	 * @param  string
	 * @return void
	 */
	public static function auto_load($class)
	{
		preg_match('/_(.+)$/', $class, $type);

		$type = isset($type[1]) ? $type[1] : FALSE;

		switch($type)
		{
			case 'Core':
				$type = 'libraries';
				$file = substr($class, 0, -5);
			break;
			case 'Controller':
				$type = 'controllers';
				$file = substr($class, 0, -11);
			break;
			case 'Model':
				$type = 'models';
				$file = substr($class, 0, -6);
			break;
			case 'Driver':
				$type = 'libraries'.DIRECTORY_SEPARATOR.'drivers';
				$file = $class;
			break;
			default:
				// This can mean either a library or a helper, but libraries must
				// always be capitalized, so we check if the first character is
				// lowercase. If it is, we are loading a helper, not a library.
				$type = (ord($class[0]) > 96) ? 'helpers' : 'libraries';
				$file = $class;
			break;
		}

		require self::find_file($type, $file, TRUE);

		if ($type == 'libraries')
		{
			if ($extension = self::find_file('libraries', Config::item('core.subclass_prefix').$class))
			{
				require $extension;
			}
			else
			{
				eval('class '.$class.' extends '.$class.'_Core { }');
			}
		}
	}

	/**
	 * Class Auto-Loader
	 *
	 * This function is used as an auto-loader, but can also be used directly.
	 * When a class is loaded with this method, it will also cache the resulting
	 * object for later use, to prevent the loading of multiple instances of the
	 * same object.
	 *
	 * @todo Let's re-evaluate the intelligence of a registry, should it cache the object, or the filename?
	 *
	 * @access public
	 * @param  string
	 * @return object
	 */
	public static function load_class($class)
	{
		if (isset(self::$registry[$class]))
		{
			return self::$registry[$class];
		}

		if ($class == 'Controller')
		{
			self::$registry[$class] = TRUE;
		}
		else
		{
			self::$registry[$class] = new $class();
		}

		return self::$registry[$class];
	}

	/**
	 * Find a Resource
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  boolean
	 * @return mixed
	 */
	public static function find_file($directory, $filename, $required = FALSE)
	{
		static $found = array();

		$search = $directory.DIRECTORY_SEPARATOR.$filename;

		if (isset($found[$search]))
			return $found[$search];

		$paths = Config::include_paths();

		if ($directory == 'config' OR $directory == 'i18n')
		{
			// Search from SYSPATH up
			$paths = array_reverse($paths);

			// Create a braced list for glob
			$paths = '{'.implode(',', $paths).'}';

			// Find all matching files, without sorting
			if (($files = glob($paths.$search.EXT, GLOB_BRACE + GLOB_NOSORT)) != FALSE)
			{
				return $found[$search] = $files;
			}
		}
		else
		{
			// Find the file and return it's filename
			foreach ($paths as $path)
			{
				if (is_file($path.$search.EXT))
				{
					return $found[$search] = $path.$search.EXT;
				}
			}
		}

		// If the function gets this far in execution, it means that no file
		// was located. If the file was flagged as required, return an error.
		($required === TRUE) and trigger_error
		(
			'Unable to locate the requested file, <tt>'.$filename.EXT.'</tt>',
			E_USER_ERROR
		);

		// File is not required, return FALSE
		return FALSE;
	}

	/**
	 * Hook Loader
	 *
	 * @access public
	 * @param  string
	 * @return void
	 */
	public static function load_hook($name)
	{
		if (Config::item('core.enable_hooks') AND $hook = self::find_file('hooks', $name))
		{
			require $hook;
		}
	}

	public static function callback($callback, $params = FALSE)
	{
		if (is_string($callback))
		{
			if ($params == FALSE)
			{
				return $callback();
			}
			elseif (is_array($params))
			{
				return call_user_func_array($callback, $params);
			}
			else
			{
				return $callback($params);
			}
		}
		else
		{
			if (is_array($params) AND $params != FALSE)
			{
				return call_user_func_array($callback, $params);
			}
			else
			{
				return call_user_func($callback);
			}
		}
	}

} // End Kohana class