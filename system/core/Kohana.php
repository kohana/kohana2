<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * $Id$
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 */
require SYSPATH.'core/utf8'.EXT;

// Define Kohana error constant
defined('E_KOHANA') or define('E_KOHANA', 33042);

// Define 404 error constant
defined('E_PAGE_NOT_FOUND') or define('E_PAGE_NOT_FOUND', 33043);

// Define extra E_RECOVERABLE_ERROR for PHP < 5.2
defined('E_RECOVERABLE_ERROR') or define('E_RECOVERABLE_ERROR', 4096);

// Insert Kohana setup
Event::add('system.setup', array('Kohana', 'setup'));

/**
 * Config Class
 *
 * @category  Core
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/controllers.html
 */
final class Config {

	// Entire configuration
	public static $conf;

	// Include paths
	private static $include_paths;

	/**
	 * Return a config item
	 *
	 * @access  public
	 * @param   string
	 * @param   boolean
	 * @param   boolean
	 * @return  mixed
	 */
	public static function item($key, $slash = FALSE, $required = TRUE)
	{
		// Configuration autoloading
		if (self::$conf === NULL)
		{
			require APPPATH.'config/config'.EXT;

			// Invalid config file
			(isset($config) AND is_array($config)) or die
			(
				'Core configuration file is not valid.'
			);

			// Start setting include paths, APPPATH first
			self::$include_paths = array(APPPATH);

			// Normalize all paths to be absolute and have a trailing slash
			foreach($config['include_paths'] as $path)
			{
				if (($path = str_replace('\\', '/', realpath($path))) == '') continue;

				self::$include_paths[] = $path.'/';
			}

			// Finish setting include paths by adding SYSPATH
			self::$include_paths[] = SYSPATH;

			// Load config into self
			self::$conf['core'] = $config;
		}

		// Find the requested key
		$key  = explode('.', strtolower($key), 2);
		// Find type and reset the key
		$type = $key[0];
		$key  = isset($key[1]) ? $key[1] : FALSE;

		// Load config arrays
		if ( ! isset(self::$conf[$type]))
		{
			self::$conf[$type] = self::load($type, $required);
		}

		$value = FALSE;

		// Fetch config groups
		if ($key === FALSE)
		{
			$value = self::$conf[$type];
		}
		// Fetch config items
		elseif (isset(self::$conf[$type][$key]))
		{
			$value = self::$conf[$type][$key];

			// Add ending slashes
			if ($slash == TRUE AND $value != '')
			{
				$value = rtrim($value, '/').'/';
			}
		}

		return $value;
	}

	/**
	 * Return the include paths
	 *
	 * @access  public
	 * @return  array
	 */
	public static function include_paths()
	{
		return self::$include_paths;
	}

	/**
	 * Load a config file
	 *
	 * @access  public
	 * @param   string
	 * @param   boolean
	 * @return  array
	 */
	public static function load($name, $required = TRUE)
	{
		$configuration = array();

		foreach(Kohana::find_file('config', $name, $required) as $filename)
		{
			include $filename;

			// Merge in configuration
			if (isset($config) AND is_array($config))
			{
				$configuration = array_merge($configuration, $config);
			}
		}

		return $configuration;
	}

} // End Config class

/**
 * Event Class
 *
 * @category  Core
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/controllers.html
 */
final class Event {

	private static $events = array();

	public static $data;

	/**
	 * Add an event
	 *
	 * @access  public
	 * @param   string
	 * @param   callback
	 * @return  void
	 */
	public static function add($name, $callback)
	{
		if ($name == FALSE OR $callback == FALSE)
			return FALSE;

		// Make sure that the event name is defined
		if ( ! isset(self::$events[$name]))
		{
			self::$events[$name] = array();
		}

		// Make sure the event is not already in the queue
		if ( ! in_array($callback, self::$events[$name]))
		{
			self::$events[$name][] = $callback;
		}
	}

	/**
	 * Fetch an event
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(self::$events[$name]) ? array() : self::$events[$name];
	}

	/**
	 * Clear an event
	 *
	 * @access  public
	 * @param   string
	 * @param   callback
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback == FALSE)
		{
			self::$events[$name] = array();
		}
		elseif (isset(self::$events[$name]))
		{
			foreach(self::$events[$name] as $i => $event_callback)
			{
				if ($callback == $event_callback)
				{
					unset(self::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * Run an event
	 *
	 * @access  public
	 * @param   string
	 * @param   array
	 * @return  void
	 */
	public static function run($name, & $data = NULL)
	{
		if ($name == FALSE)
			return FALSE;

		// So callbacks can access Event::$data
		self::$data =& $data;

		foreach(self::get($name) as $callback)
		{
			call_user_func($callback);
		}

		// Do this to prevent data from getting 'stuck'
		$clear_data = '';
		self::$data =& $clear_data;
	}

} // End Event Class

/**
 * Log Class
 *
 * @category  Core
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/controllers.html
 */
final class Log {

	public static $messages = array();

	/**
	 * Add a log message
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  void
	 */
	public static function add($type, $message)
	{
		self::$messages[$type][] = array
		(
			date(Config::item('log.format')),
			$message
		);
	}

	/**
	 * Write log messages to file
	 *
	 * @access  public
	 * @return  void
	 */
	public static function write()
	{
		$filename = Config::item('log.directory');

		// Don't log if there is nothing to log to
		if (count(self::$messages) == 0 OR $filename == FALSE) return;

		// Make sure that the log directory is absolute
		$filename = (substr($filename, 0, 1) !== '/') ? APPPATH.$filename : $filename;

		// Make sure there is an ending slash
		$filename = rtrim($filename, '/').'/';

		/**
		 * @todo i18n error
		 */
		is_writable($filename) or trigger_error
		(
			'Your log.directory config setting does not point to a writable directory. '.
			'Please correct the permissions and refresh the page.',
			E_USER_ERROR
		);

		// Attach the filename to the directory
		$filename .= date('Y-m-d').'.log'.EXT;

		$messages = '';

		// Get messages
		foreach(self::$messages as $type => $data)
		{
			foreach($data as $date => $text)
			{
				list($date, $message) = $text;
				$messages .= $date.' --- '.$type.': '.$message."\r\n";
			}
		}

		// No point in logging nothing
		if ($messages == '') return;

		// Create the log file if it doesn't exist yet
		if ( ! file_exists($filename))
		{
			touch($filename);
			chmod($filename, 0644);

			// Add our PHP header to the log file to prevent URL access
			$messages = "<?php defined('SYSPATH') or die('No direct script access.'); ?>\r\n\r\n".$messages;
		}

		// Append the messages to the log
		file_put_contents($filename, $messages, FILE_APPEND) or trigger_error
		(
			'The log file could not be written to. Please correct the permissions and refresh the page.',
			E_USER_ERROR
		);
	}

} // End Log Class

/**
 * Kohana Class
 *
 * @category  Core
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/controllers.html
 */
class Kohana {

	// The singleton instance of the controller
	private static $instance = NULL;

	// Library registery, to prevent multiple loads of libraries
	private static $libraries = array();

	// Output buffering level
	private static $buffer_level = 0;

	// Error codes
	private static $error_codes = array();

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

		// Set error types
		self::$error_codes = self::lang('error_codes');

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

		Event::add('system.shutdown', array('Kohana', 'display'));

		// Enable log writing if the log threshold is enabled
		if (Config::item('log.threshold') > 0)
		{
			register_shutdown_function(array('Log', 'write'));
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
	final public static function & instance()
	{
		if (self::$instance == FALSE)
		{
			// Include the Controller file
			require Router::$directory.Router::$controller.EXT;

			// Run system.pre_controller
			Event::run('system.pre_controller');

			// Set controller class name
			$controller = ucfirst(Router::$controller).'_Controller';

			// Controller methods
			$methods = get_class_methods($controller);
			$methods = array_combine($methods, $methods);

			if (isset($methods['_remap']))
			{
				// Change arguments to be $method, $arguments.
				// This makes _remap capable of being a much more effecient dispatcher
				Router::$arguments = array(Router::$method, Router::$arguments);
				// Set the method to _remap
				Router::$method = '_remap';
			}
			elseif (isset($methods[Router::$method]) AND Router::$method != 'kohana_include_view')
			{
				/* Do nothing. Exciting! */
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
				Kohana::show_404();
			}

			// Load the controller
			$controller = new $controller();

			// Make sure the controller extends this class
			is_subclass_of($controller, __CLASS__) or exit
			(
				'Kohana controllers must have the Kohana class as an ancestor. '."\n".
				'Please make sure Controller is defined with <tt>Controller_Core extends Kohana</tt>.'
			);

			// Call the controller method
			if (is_array(Router::$arguments) AND ! empty(Router::$arguments))
			{
				call_user_func_array(array($controller, Router::$method), Router::$arguments);
			}
			else
			{
				call_user_func(array($controller, Router::$method));
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
		// Run the send_headers event, specifically for cookies being set
		Event::run('system.send_headers');

		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		// Replace the global template variables
		$output = str_replace(
			array
			(
				'{kohana_version}',
				'{kohana_codename}',
				'{execution_time}',
				'{memory_usage}'
			),
			array
			(
				KOHANA_VERSION,
				KOHANA_CODENAME,
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
	 * Exception Handler
	 *
	 * @access public
	 * @param  object
	 * @return void
	 */
	public static function exception_handler($exception)
	{
		// Error message, filename, and line number
		$code    = $exception->getCode();
		$message = $exception->getMessage();
		$file    = $exception->getFile();
		$line    = $exception->getLine();

		// Do not display E_STRICT notices, they are garbage
		if ($code == E_STRICT) return FALSE;

		if (is_numeric($code))
		{
			if (isset(self::$error_codes[$code]))
			{
				list($level, $error) = self::$error_codes[$code];
			}
			else
			{
				$level = 1;
				$error = 'Unknown Error';
			}
		}
		else
		{
			// Custom error message, this will never be logged
			$level = 5;
			$error = $code;
		}

		// Remove the DOCROOT from the path, as a security precaution
		$file = str_replace('\\', '/', realpath($file));
		$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

		// Exception class
		$type = get_class($exception);

		// Log the error
		if (Config::item('log.threshold') >= $level)
		{
			Log::add('error', Kohana::lang('core.uncaught_exception', $type, strip_tags($message), $file, $line));
		}

		if (ob_get_level() > self::$buffer_level)
		{
			// Flush the entire buffer here, to ensure the error is displayed
			while(ob_get_level() > self::$buffer_level) ob_end_clean();

			// Clear out the output buffer
			ob_clean();
		}

		if ( ! headers_sent())
		{
			$exception->sendHeaders();
		}

		// Load the error page
		include self::find_file('views', 'kohana_error_page');
		exit;
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
	public static function error_handler($code, $message, $file, $line)
	{
		throw new Kohana_PHP_Exception($code, $message, $file, $line);
	}

	/**
	 * Show 404
	 *
	 * @access public
	 * @return void
	 */
	public static function show_404()
	{
		throw new Kohana_404_Exception(Router::$current_uri.Config::item('core.url_suffix').Router::$query_string);
	}

	/**
	 * Show Error
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public static function show_error($title, $message)
	{
		throw new Kohana_PHP_Exception($title, $message, FALSE, FALSE);
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
		if (class_exists($class, FALSE))
			return TRUE;

		preg_match('/_([^_]+)$/', $class, $type);

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
				// Models are always lowercase
				$file = strtolower($file);
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
					return $found[$hash] = $path.$search.$ext;
			}

			// If the file is required, throw an exception
			if ($required == TRUE)
				throw new Kohana_Exception('core.resource_not_found', $directory, $filename);

			return $found[$hash] = FALSE;
		}
	}

	/**
	 * Fetch a i18n language item
	 *
	 * @access public
	 * @param  string
	 * @param  array
	 * @return mixed
	 */
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
				if ( ! empty($lang) AND is_array($lang))
				{
					foreach($lang as $key => $val)
					{
						$messages[$key] = $val;
					}
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

/**
 * Kohana Exception Class
 *
 * @category  Exceptions
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/exceptions.html
 */
class Kohana_Exception extends Exception {

	// Message
	protected $message = 'Unknown Exception: ';

	// Header
	protected $header = FALSE;

	// Error code, filename, line number
	protected $code = E_KOHANA;
	protected $file = '';
	protected $line = 0;

	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
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

	/**
	 * Magic toString
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString()
	{
		return $this->message;
	}

	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception Class

/**
 * Kohana PHP Exception Class
 *
 * @category  Exceptions
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/exceptions.html
 */
class Kohana_PHP_Exception extends Kohana_Exception {

	public function __construct($code, $message, $file, $line)
	{
		$this->code    = $code;
		$this->message = $message;
		$this->file    = $file;
		$this->line    = $line;
	}

} // End Kohana PHP Exception Class

/**
 * Kohana 404 Exception Class
 *
 * @category  Exceptions
 * @author    Kohana Team
 * @link      http://kohanaphp.com/user_guide/en/general/exceptions.html
 */
class Kohana_404_Exception extends Kohana_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	public function __construct($uri)
	{
		$this->message = Kohana::lang('core.page_not_found', $uri);
		$this->file    = FALSE;
		$this->line    = FALSE;
	}

	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception Class