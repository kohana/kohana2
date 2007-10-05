<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework for PHP5+
 *
 * $Id$
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 */

require SYSPATH.'core/utf8'.EXT;
require SYSPATH.'core/Event'.EXT;
require SYSPATH.'core/Config'.EXT;
require SYSPATH.'core/Log'.EXT;

Event::add('system.setup', array('Kohana', 'setup'));

/**
 * Kohana class
 *
 * @category    Core
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/core_classes.html
 */
class Kohana {

	// The singleton instance of the controller
	private static $instance = NULL;

	// Human readable error types, for PHP errors
	private static $error_types = array();

	// Error strings that will be displayed by unhandled exceptions
	private static $error_strings = array();

	// Library registery, to prevent multiple loads of libraries
	private static $libraries = array();

	// Output buffering level
	private static $buffer_level = 0;

	// The final output that will displayed by Kohana
	public static $output = '';

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
		if (is_object(self::$instance))
			throw new Kohana_Exception('core.there_can_be_only_one');

		self::$instance = $this;
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
	final public static function setup()
	{
		static $run;

		// This function can only be run once
		if ($run === TRUE) return;

		// Start output buffering
		ob_start(array('Kohana', 'output'));

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Define extra error constants
		defined('E_RECOVERABLE_ERROR')  or define('E_RECOVERABLE_ERROR',  4096);
		// Define Kohana error constants
		defined('E_UNCAUGHT_EXCEPTION') or define('E_UNCAUGHT_EXCEPTION', 'kf_error');
		defined('E_KOHANA_EXCEPTION')   or define('E_KOHANA_EXCEPTION',   'kf_error');
		defined('E_KOHANA_DEVERROR')    or define('E_KOHANA_DEVERROR',    'kf_error');

		// Set error types, format: CONSTANT => array($log_level, $message)
		self::$error_types = array
		(
			E_UNCAUGHT_EXCEPTION => array( 1, 'Uncaught Exception'),
			E_KOHANA_EXCEPTION   => array( 1, 'Kohana Runtime Error'),
			E_KOHANA_DEVERROR    => array( 1, 'Developer Error'),
			E_RECOVERABLE_ERROR  => array( 1, 'Recoverable Error'),
			E_ERROR              => array( 1, 'Fatal Error'),
			E_USER_ERROR         => array( 1, 'Fatal Error'),
			E_PARSE              => array( 1, 'Syntax Error'),
			E_WARNING            => array( 2, 'Warning Message'),
			E_USER_WARNING       => array( 2, 'Warning Message'),
			E_STRICT             => array( 3, 'Strict Mode Error'),
			E_NOTICE             => array( 3, 'Runtime Message')
		);

		// Set error handler
		set_error_handler(array('Kohana', 'error_handler'));

		// Set execption handler
		set_exception_handler(array('Kohana', 'exception_handler'));

		if (function_exists('date_default_timezone_set'))
		{
			// Set default timezone, due to increased validation of date settings
			// which cause massive amounts of E_NOTICEs to be generated
			$timezone = Config::item('core.timezone');
			$timezone = ($timezone == FALSE) ? @date_default_timezone_get() : $timezone;

			date_default_timezone_set($timezone);
		}

		if ($hooks = Config::item('hooks.enable'))
		{
			// All hooks are enabled, we must build an array of filenames
			if ( ! is_array($hooks))
			{
				$hooks = array();
				foreach(Config::include_paths() as $path)
				{
					$files = glob($path.'hooks/*'.EXT);

					if ( ! empty($files))
					{
						$hooks = array_merge($hooks, $files);
					}
				}
			}

			// Loop through all the hooks and load them
			foreach($hooks as $file)
			{
				Log::add('debug', 'Loading hook'.$file);
				include $file;
			}
		}

		// Enable routing
		Event::add('system.routing', array('Router', 'setup'));

		// Enable loading a Kohana instance
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable log writing if the log threshold is enabled
		if(Config::item('log.threshold') > 0)
		{
			Event::add('system.shutdown', array('Log', 'write'));
		}

		Event::add('system.shutdown', array('Kohana', 'display'));

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
	final public static function & instance()
	{
		if (self::$instance == FALSE)
		{
			// Run system.pre_controller
			Event::run('system.pre_controller');

			// Include the Controller file
			require Router::$directory.Router::$controller.EXT;

			// Set controller class name
			$controller = ucfirst(Router::$controller).'_Controller';

			try
			{
				// Load the controller
				$controller = new $controller();
			}
			catch (Kohana_Exception $exception)
			{
				Kohana::show_404();
				return;
			}

			if (method_exists($controller, '_remap'))
			{
				// Change arguments to be $method, $arguments.
				// This makes _remap capable of being a much more effecient dispatcher
				Router::$arguments = array(Router::$method, Router::$arguments);
				// Set the method to _remap
				Router::$method = '_remap';
			}
			elseif (method_exists($controller, Router::$method))
			{
				(Router::$method !== 'kohana_include_view') or trigger_error
				(
					'This method cannot be accessed directly.',
					E_USER_ERROR
				);
			}
			elseif (method_exists($controller, '_default'))
			{
				// Change arguments to be $method, $arguments.
				// This makes _default a much more effecient 404 handler
				Router::$arguments = array(Router::$method, Router::$arguments);
				// Set the method to _default
				Router::$method = '_default';
			}
			else
			{
				$controller->show_404();
			}
			if (count(Router::$arguments) > 0)
			{
				call_user_func_array(array(Kohana::instance(), Router::$method), Router::$arguments);
			}
			else
			{
				call_user_func(array(Kohana::instance(), Router::$method));
			}

			// Run system.pre_controller
			Event::run('system.post_controller');
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
	final public static function output($output)
	{
		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		// Replace the global template variables
		$output = str_replace(
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
			$output
		);

		self::$output = $output;

		// Return the final output
		return $output;
	}

	public static function display()
	{
		// This will flush the Kohana buffer, which sets self::$output
		ob_end_clean();

		// Run the output event
		// --------------------------------------------------------------------
		// This can be used for functions that require completion before headers
		// are sent. One example is cookies, which are sent with the headers,
		// and will trigger errors if you try to set them after headers.
		// --------------------------------------------------------------------
		Event::run('system.output');

		print self::$output;
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
		$file = str_replace('\\', '/', realpath($file));
		$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

		// Log the error
		if (Config::item('log.threshold') >= $level)
		{
			Log::add($error, $message.' in file: '.$file.' on line '.$line);
		}

		if (ob_get_level() > self::$buffer_level)
		{
			// Flush the entire buffer here, to ensure the error is displayed
			while(ob_get_level() > self::$buffer_level) ob_end_clean();
		}

		// Clear out the output buffer
		ob_clean();

		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');

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
		self::error_handler
		(
			// Choose the exception type
			(get_class($exception) == 'Kohana_Exception') ? E_KOHANA_EXCEPTION : E_UNCAUGHT_EXCEPTION,
			// Pass in the exception details
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine()
		);
	}

	public static function show_404()
	{
		$message = Kohana::lang('core.page_not_found', '/'.Router::$current_uri.Config::item('core.url_suffix').Router::$query_string);

		// Log the error
		Log::add('file_not_found', $message);

		if (ob_get_level() > self::$buffer_level)
		{
			// Flush the entire buffer here, to ensure the error is displayed
			while(ob_get_level() > self::$buffer_level) ob_end_clean();
		}

		// Clear out the output buffer
		ob_clean();

		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');

		// Load the error page
		include self::find_file('views', 'kohana_404');

		// Display the buffer and exit
		ob_end_flush();
		exit;
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
		if (class_exists($class)) return true;

		preg_match('/(?<=_).+$/', $class, $type);

		$type = isset($type[0]) ? $type[0] : FALSE;

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

		require self::find_file($type, $file, TRUE);

		if ($type == 'libraries')
		{
			if ($extension = self::find_file('libraries', Config::item('core.extension_prefix').$class))
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
	 * @param  array
	 * @return object
	 */
	public static function load_class($class, $configuration = array())
	{
		if (isset(self::$libraries[$class]))
		{
			return self::$libraries[$class];
		}

		if ($class == 'Controller')
		{
			self::$libraries[$class] = TRUE;
		}
		else
		{
			// Merge the config file and the passed configuration
			$configuration = array_merge(Config::item($class, FALSE, FALSE), $configuration);

			self::$libraries[$class] = new $class($configuration);
		}

		return self::$libraries[$class];
	}

	/**
	 * Find a Resource
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  boolean
	 * @param  boolean
	 * @return mixed
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		static $found = array();

		$search = $directory.'/'.$filename;
		$hash   = md5($search);

		if (isset($found[$hash]))
			return $found[$hash];

		if ($directory == 'config' OR $directory == 'i18n')
		{
			$fnd = array();

			// Search from SYSPATH up
			foreach(array_reverse(Config::include_paths()) as $path)
			{
				if (is_file($path.$search.EXT)) $fnd[] = $path.$search.EXT;
			}

			// If required and nothing was found, throw an exception
			if ($required == TRUE AND $fnd === array())
				throw new Kohana_Exception('core.resource_not_found', $directory, $filename);

			return $found[$hash] = $fnd;
		}
		else
		{
			// Users can define their own extensions, .css, etc
			$ext = ($ext == FALSE) ? EXT : '';

			// Find the file and return its filename
			foreach (Config::include_paths() as $path)
			{
				if (is_file($path.$search.$ext))
				{
					return $found[$hash] = $path.$search.$ext;
				}
			}

			// If the file is required, throw an exception
			if ($required == TRUE)
				throw new Kohana_Exception('core.resource_not_found', $directory, $filename);

			return $found[$hash] = FALSE;
		}
	}

	public static function lang($type, $args = array())
	{
		static $found = array();

		if (strpos($type, '.') !== FALSE)
		{
			list ($type, $name) = explode('.', $type);
		}
		else
		{
			$name = TRUE;
		}

		if ( ! isset($found[$type]))
		{
			// Messages from this file
			$messages = array();

			// The name of the file to search for
			$filename = Config::item('core.locale').'/'.$type;

			// Loop through the files and include each one, so SYSPATH files
			// can be overloaded by more localized files
			foreach(array_reverse(self::find_file('i18n', $filename)) as $filename)
			{
				include $filename;

				// Merge in configuration
				if (isset($lang) AND is_array($lang))
				{
					$messages = array_merge($messages, $lang);
				}
			}

			// Cache the type
			$found[$type] = $messages;
		}

		// Return something
		if ($name === TRUE)
		{
			return $found[$type];
		}
		elseif ($found[$type] == FALSE OR ! isset($found[$type][$name]))
		{
			return FALSE;
		}
		else
		{
			if ( ! is_array($args) OR empty($args))
			{
				$args = func_get_args();
				$args = array_slice($args, 1);
			}

			$line = $found[$type][$name];

			return (empty($args) ? $line : vsprintf($line, $args));
		}
	}

} // End Kohana class

class Kohana_Exception extends Exception {

	protected $message = 'Unknown Exception: ';

	protected $file = '';
	protected $line = 0;
	protected $code = 0;

	function __construct($error)
	{
		$args = func_get_args();
		$args = array_slice($args, 1);

		// Fetch the error message
		$message = Kohana::lang($error, $args);

		// Handle error messages that are not set
		if ($message == '')
		{
			$this->message .= $error;
		}
		else
		{
			$this->message = $message;
		}
	}

	public function __toString()
	{
		return $this->message;
	}

} // End Kohana Exception Class
