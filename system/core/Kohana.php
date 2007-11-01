<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Kohana
 *  Provides Kohana-specific helper functions. This is where the magic happens!
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

// Define Kohana error constant
defined('E_KOHANA') or define('E_KOHANA', 33042);

// Define 404 error constant
defined('E_PAGE_NOT_FOUND') or define('E_PAGE_NOT_FOUND', 33043);

// Define database error constant
defined('E_DATABASE_ERROR') or define('E_DATABASE_ERROR', 33044);

// Define extra E_RECOVERABLE_ERROR for PHP < 5.2
defined('E_RECOVERABLE_ERROR') or define('E_RECOVERABLE_ERROR', 4096);

// Insert Kohana setup
Event::add('system.setup', array('Kohana', 'setup'));

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

	/*
	 * Method: __construct
	 *  Allows the controller to be a true singleton object. This method *must*
	 *  be called by all controllers.
	 *
	 * Throws:
	 *  <Kohana_Exception> when a controller instance already exists.
	 */
	public function __construct()
	{
		if (is_object(self::$instance))
			throw new Kohana_Exception('core.there_can_be_only_one');

		self::$instance = $this;
	}

	/*
	 * Method: __clone
	 *  Protects the Kohana instance from being copied
	 */
	final public function __clone()
	{
		$this->__construct();
	}

	/*
	 * Method: setup
	 *  Sets up the PHP environment. Adds error/exception handling, output
	 *  buffering, and adds an auto-loading method for loading classes.
	 *
	 * Event:
	 *  system.setup
	 */
	final public static function setup()
	{
		static $run;

		// This function can only be run once
		if ($run === TRUE)
			return;

		if (function_exists('date_default_timezone_set'))
		{
			// Set default timezone, due to increased validation of date settings
			// which cause massive amounts of E_NOTICEs to be generated in PHP 5.2+
			$timezone = Config::item('locale.timezone');
			$timezone = ($timezone == FALSE) ? @date_default_timezone_get() : $timezone;

			date_default_timezone_set($timezone);
		}

		// Start output buffering
		ob_start(array('Kohana', 'output_buffer'));

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Set error handler
		set_error_handler(array('Kohana', 'exception_handler'));

		// Set exception handler
		set_exception_handler(array('Kohana', 'exception_handler'));

		// Disable magic_quotes_runtime. The Input library takes care of
		// magic_quotes_gpc later.
		set_magic_quotes_runtime(0);

		// Send default text/html UTF-8 header
		header('Content-type: text/html; charset=UTF-8');

		// Set locale information
		setlocale(LC_ALL, Config::item('locale.language').'.UTF-8');

		if ($hooks = Config::item('hooks.enable'))
		{
			// All hooks are enabled, we must build an array of filenames
			if ( ! is_array($hooks))
			{
				$hooks = array();
				foreach(Config::include_paths() as $path)
				{
					// Find all the hooks in each path
					if ($files = glob($path.'hooks/*'.EXT))
					{
						$hooks = array_merge($hooks, $files);
					}
				}
			}

			foreach($hooks as $file)
			{
				// Log before loading, to make the logs clearer
				Log::add('debug', 'Loading hook '.$file);

				// Load the hook
				include_once $file;
			}
		}

		// Enable Kohana routing
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Kohana controller initialization
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable Kohana output handling
		Event::add('system.shutdown', array('Kohana', 'display'));

		if (Config::item('log.threshold') > 0)
		{
			// Enable log writing if the log threshold is above 0
			register_shutdown_function(array('Log', 'write'));
		}

		// Setup is complete, prevent it from being run again
		$run = TRUE;
	}

	/*
	 * Method: instance
	 *  Loads the controller and initializes it.
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
				/*
				 * Do nothing. Exciting! Honestly, I am surprised having only
				 * a comment here works.
				 */
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

	/*
	 * Method: output_buffer
	 *  Kohana output handler
	 *
	 * Parameters:
	 *  output - current output buffer string
	 *
	 * Returns:
	 *  An empty string. Output is done in <Kohana.display>
	 */
	final public static function output_buffer($output)
	{
		// Run the send_headers event, specifically for cookies being set
		Event::has_run('system.send_headers') or Event::run('system.send_headers');

		// Set final output
		self::$output = $output;

		// Set and return the final output
		return $output;
	}

	/*
	 * Method: display
	 *  Displays the final rendered output
	 */
	public static function display()
	{
		// This will flush the Kohana buffer, which sets self::$output
		(ob_get_level() === self::$buffer_level) and ob_end_clean();

		// Run the output event
		Event::run('system.display');

		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? memory_get_usage() : 0;

		// Replace the global template variables
		self::$output = str_replace(
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
				text::bytes($memory, 'MiB')
			),
			self::$output
		);

		print self::$output;
	}

	/*
	 * Method: exception handler
	 *  Dual-purpose PHP error and exception handler. Uses the kohana_error_page
	 *  View to display the message.
	 *
	 * Parameters:
	 *  exception - object or error code
	 *  message   - error message
	 *  file      - filename
	 *  line      - line number
	 */
	public static function exception_handler($exception, $message = FALSE, $file = FALSE, $line = FALSE)
	{
		// If error_reporting() returns 0, it means the error was supressed by
		// using the @ prefix, e.g. print @$var_does_not_exist. These errors
		// should not be displayed, as per PHP syntax.
		if (error_reporting() === 0)
			return;

		// Error handling will use exactly 5 args, every time
		if (func_num_args() === 5)
		{
			$code     = $exception;
			$template = 'kohana_error_page';
		}
		else
		{
			// Error message, filename, and line number
			$code     = $exception->getCode();
			$message  = $exception->getMessage();
			$file     = $exception->getFile();
			$line     = $exception->getLine();
			$template = $exception->getTemplate();
		}

		// Do not display E_STRICT notices, they are garbage
		if ($code == E_STRICT) return FALSE;

		if (is_numeric($code))
		{
			$codes = Kohana::lang('errors');

			if ( ! empty($codes[$code]))
			{
				list($level, $error, $description) = $codes[$code];
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
			$description = '';
		}

		// Remove the DOCROOT from the path, as a security precaution
		$file = str_replace('\\', '/', realpath($file));
		$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

		if (ob_get_level() >= self::$buffer_level)
		{
			// Flush the entire buffer here, to ensure the error is displayed
			while(ob_get_level() > self::$buffer_level) ob_end_clean();

			// Clear out the output buffer
			ob_clean();
		}

		if (func_num_args() === 5)
		{
			$type = 'Kohana_PHP_Error';

			$description = Kohana::lang('errors.'.E_RECOVERABLE_ERROR);
			$description = $description[2];
		}
		else
		{
			// Exception class
			$type = get_class($exception);

			if ( ! headers_sent() AND method_exists($exception, 'sendHeaders'))
			{
				$exception->sendHeaders();
			}
		}

		// Log the error
		if (Config::item('log.threshold') >= $level)
		{
			Log::add('error', Kohana::lang('core.uncaught_exception', $type, strip_tags($message), $file, $line));
		}

		// Load the error
		include self::find_file('views', empty($template) ? 'kohana_error_page' : $template);

		// Run the system.shutdown event
		Event::has_run('system.shutdown') or Event::run('system.shutdown');

		// Prevent further output
		exit;
	}

	/*
	 * Method: show_404
	 *  Displays a 404 page
	 *
	 * Parameters:
	 *  page     - URI of page
	 *  template - custom template
	 *
	 * Throws:
	 *   <Kohana_404_Exception>
	 */
	public static function show_404($page = FALSE, $template = FALSE)
	{
		throw new Kohana_404_Exception($page, $template);
	}

	/*
	 * Method: show_error
	 *  Show a custom error message
	 *
	 * Parameters:
	 *  title    - error title
	 *  message  - error message
	 *  template - custom template
	 *
	 * Throws:
	 *  <Kohana_User_Exception>
	 */
	public static function show_error($title, $message, $template = FALSE)
	{
		throw new Kohana_User_Exception($title, $message, $template);
	}

	/*
	 * Method: auto_load
	 *  Provides class auto-loading
	 *
	 * Parameters:
	 *  class - name of class
	 *
	 * Throws:
	 *  <Kohana_Exception> if the class is not found.
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
				$file = substr($class, 0, -7);
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

	/*
	 * Method: find_file
	 *  Find a resource file in a given directory
	 *
	 * Parameters:
	 *  directory - directory to search in
	 *  filename  - filename to look for
	 *  required  - is the file required?
	 *  ext       - custom file extension
	 *
	 * Returns:
	 *  An array of filenames for i18n/ or config/ files. Filename if the file
	 *  was found, or FALSE.
	 *
	 * Throws:
	 *  <Kohana_Exception> if the file is required and not found.
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

	/*
	 * Method: list_files
	 *  Lists all files and directories in a resource path
	 *
	 * Parameters:
	 *  directory - directory to search
	 *  recursive - list all files to the maximum depth?
     *  path      - full path to search (used for recursion, *never* set this manually)
	 */
	public static function list_files($directory, $recursive = FALSE, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			foreach(Config::include_paths() as $path)
			{
				$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			foreach(glob($path.'*') as $index => $item)
			{
				$files[] = $item = str_replace('\\', '/', $item);

				// Handle recursion
				if (is_dir($item) AND $recursive == TRUE)
				{
					// Filename should only be the basename
					$item = pathinfo($item, PATHINFO_BASENAME);

					// Append sub-directory search
					$files = array_merge($files, self::list_files($directory, TRUE, $path.$item));
				}
			}
		}

		return $files;
	}

	/*
	 * Method: lang
	 *  Fetch a i18n language item
	 *
	 * Parameters:
	 *  key  - language key to fetch
	 *  args - additional information to insert into the line
	 */
	public static function lang($key, $args = array())
	{
		static $language = array();

		$group = current(explode('.', $key));

		if ( ! isset($language[$group]))
		{
			// Messages from this file
			$messages = array();

			// The name of the file to search for
			$filename = Config::item('locale.language').'/'.$group;

			// Loop through the files and include each one, so SYSPATH files
			// can be overloaded by more localized files
			foreach(self::find_file('i18n', $filename) as $filename)
			{
				include $filename;

				// Merge in configuration
				if ( ! empty($lang) AND is_array($lang))
				{
					foreach($lang as $k => $v)
					{
						$messages[$k] = $v;
					}
				}
			}

			// Cache the type
			$language[$group] = $messages;
		}

		$line = self::key_string($key, $language);

		if ($line === NULL)
			return FALSE;

		if (is_string($line) AND func_num_args() > 1)
		{
			$args = func_get_args();
			$args = array_slice($args, 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/*
	 * Method: key_string
	 *  Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * Parameters:
	 *  keys  - dot-noted string, like 'foo.bar.one'
	 *  array - array to search
	 *
	 * Returns:
	 *  Value from array, or NULL.
	 */
	public static function key_string($keys, $array)
	{
		// No array to search
		if (empty($keys) OR empty($array))
			return;

		// Prepare for loop
		$keys = explode('.', $keys);

		// Loop down and find the key
		do
		{
			// Get the current key
			$key = array_shift($keys);

			// Value is set, dig deeper or return
			if (isset($array[$key]))
			{
				// If the key is an array, and we haven't hit bottom, prepare
				// for the next loop by re-referencing to the next child
				if (is_array($array[$key]) AND ! empty($keys))
				{
					$array =& $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		// We return NULL, because it's less common than FALSE
		return;
	}

	/*
	 * Method: debug
	 *  Quick debugging of any variable.
	 *
	 * Parameters:
	 *  Variables you want debugged.
	 *
	 * Returns:
	 *  HTML output string.
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach($params as $var)
		{
			$output[] = '<pre>'.html::specialchars(print_r($var, TRUE)).'</pre>';
		}

		return implode("\n", $output);
	}

} // End Kohana

/*
 * Class: Kohana_Exception
 *  Creates a generic i18n exception.
 */
class Kohana_Exception extends Exception {

	// Template file
	protected $template = 'kohana_error_page';

	// Message
	protected $message = 'Unknown Exception: ';

	// Header
	protected $header = FALSE;

	// Error code, filename, line number
	protected $code = E_KOHANA;
	protected $file = FALSE;
	protected $line = FALSE;

	/*
	 * Method: __construct
	 *
	 * Parameters:
	 *  error - i18n language key for the message
	 *  args  - addition line parameters
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

	/*
	 * Method: __toString
	 *  Magic method for converting an object to a string.
	 *
	 * Returns:
	 *  Exception message string.
	 */
	public function __toString()
	{
		return $this->message;
	}

	/*
	 * Method: getTemplate
	 *  Fetch the template name.
	 *
	 * Returns:
	 *  Template name string.
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/*
	 * Method: sendHeaders()
	 *  Sends a Internal Server Error header.
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception

/*
 * Class: Kohana_User_Exception
 *  Creates a custom exception.
 */
class Kohana_User_Exception extends Kohana_Exception {

	/*
	 * Method: __construct
	 *
	 * Parameters:
	 *  title    - exception title string
	 *  message  - exception message string
	 *  template - custom error template
	 */
	public function __construct($title, $message, $template = FALSE)
	{
		$this->code     = $title;
		$this->message  = $message;

		if ($template != FALSE)
		{
			$this->template = $template;
		}
	}

} // End Kohana PHP Exception

/*
 * Class: Kohana_404_Exception
 *  Creates a Page Not Found exception.
 */
class Kohana_404_Exception extends Kohana_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/*
	 * Method: __construct
	 *
	 * Parameters:
	 *  page     - URL of page
	 *  template - custom error template
	 */
	public function __construct($page = FALSE, $template = FALSE)
	{
		if ($page === FALSE)
		{
			$page = Router::$current_uri.Config::item('core.url_suffix').Router::$query_string;
		}

		$this->message = Kohana::lang('core.page_not_found', $page);
		$this->file    = FALSE;
		$this->line    = FALSE;

		$this->template = $template;
	}

	/*
	 * Method: sendHeaders
	 *  Sends a Page Not Found header.
	 */
	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception