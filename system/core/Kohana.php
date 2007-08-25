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

		// Set error types
		self::$error_types = array
		(
			E_UNCAUGHT_EXCEPTION => 'Uncaught Exception',
			E_RECOVERABLE_ERROR  => 'Recoverable Error',
			E_ERROR              => 'Fatal Error',
			E_USER_ERROR         => 'Fatal Error',
			E_PARSE              => 'Syntax Error',
			E_STRICT             => 'Strict Mode Error',
			E_NOTICE             => 'Runtime Message',
			E_WARNING            => 'Warning Message',
			E_USER_WARNING       => 'Warning Warning'
		);

		// Set error handler
		set_error_handler(array('Kohana', 'error_handler'));

		// Set execption handler
		set_exception_handler(array('Kohana', 'exception_handler'));

		// Set shutdown handler to run the "system.shutdown" event
		register_shutdown_function(array('Event', 'run'), 'system.shutdown');

		if (function_exists('date_default_timezone_set'))
		{
			date_default_timezone_set(Config::item('core.timezone'));
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
			$controller = Router::$directory.Router::$controller.EXT;

			// Validate Controller
			if ( ! file_exists($controller))
				throw new controller_not_found(ucfirst(Router::$controller));

			require $controller;

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
		$error = isset(self::$error_types[$error]) ? self::$error_types[$error] : 'Unknown Error';
		$file  = preg_replace('#^'.preg_quote(DOCROOT, '-').'#', '', $file);
		$template = self::find_file('errors', 'php_error');

		// Flush the entire buffer here, to ensure the error is displayed
		while(ob_get_level())
		{
			ob_end_clean();
		}

		ob_start(array('Kohana', 'output'));
		include $template;
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
				$type = 'libraries/drivers';
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

		try
		{
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
		catch (file_not_found $exception)
		{
			print $exception->getMessage().' could not be found in any '.$type.' directory.';
			exit;
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

		$search = $directory.'/'.$filename;

		if (isset($found[$search]))
			return $found[$search];

		$paths = Config::item('core.include_paths');

		if ($directory == 'config' OR $directory == 'i18n')
		{
			// Search from SYSPATH up
			$paths = array_reverse($paths);
			// Create a braced list for glob
			$paths = '{'.implode(',', $paths).'}';
			// Find all matching files, without sorting
			$files = glob($paths.$search.EXT, GLOB_BRACE + GLOB_NOSORT);

			if ( ! empty($files))
			{
				$found[$search] = $files;

				return $files;
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				// File found? Return it!
				if (file_exists($path.$search.EXT) AND is_file($path.$search.EXT))
				{
					$found[$search] = $path.$search.EXT;

					return $path.$search.EXT;
				}
			}
		}

		if ($required == TRUE) throw new file_not_found($filename);
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

} // End Kohana class

/**
 * Exceptions
 */
class file_not_found       extends Exception {}
class library_not_found    extends file_not_found {}
class controller_not_found extends file_not_found {}
class model_not_found      extends file_not_found {}
class helper_not_found     extends file_not_found {}
class invalid_file_format  extends Exception {}