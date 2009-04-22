<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Provides Kohana-specific helper functions. This is where the magic happens!
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

// Test of Kohana is running in Windows
define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

abstract class Kohana_Core {

	const VERSION  = '2.4';
	const CODENAME = 'no_codename';
	const CHARSET  = 'UTF-8';

	// The singleton instance of the controller
	public static $instance;

	// Output buffering level
	private static $buffer_level;

	// Will be set to TRUE when an exception is caught
	public static $has_error = FALSE;

	// The final output that will displayed by Kohana
	public static $output = '';

	// The current user agent
	public static $user_agent;

	// The current locale
	public static $locale;

	// Configuration
	private static $configuration;

	// Include paths
	private static $include_paths;

	// Cache lifetime
	private static $cache_lifetime;

	// Internal caches and write status
	private static $internal_cache = array();
	private static $write_cache;
	private static $internal_cache_path;

	/**
	 * Sets up the PHP environment. Adds error/exception handling, output
	 * buffering, and adds an auto-loading method for loading classes.
	 *
	 * This method is run immediately when this file is loaded, and is
	 * benchmarked as environment_setup.
	 *
	 * For security, this function also destroys the $_REQUEST global variable.
	 * Using the proper global (GET, POST, COOKIE, etc) is inherently more secure.
	 * The recommended way to fetch a global variable is using the Input library.
	 * @see http://www.php.net/globals
	 *
	 * @return  void
	 */
	public static function setup()
	{
		static $run;

		// This function can only be run once
		if ($run === TRUE)
			return;

		// Start the environment setup benchmark
		Benchmark::start(SYSTEM_BENCHMARK.'_environment_setup');

		// Define Kohana error constant
		define('E_KOHANA', 42);

		// Define 404 error constant
		define('E_PAGE_NOT_FOUND', 43);

		// Define database error constant
		define('E_DATABASE_ERROR', 44);

		if (self::$cache_lifetime = self::config('core.internal_cache'))
		{
			// Set the directory to be used for the internal cache
			if ( ! self::$internal_cache_path = self::config('core.internal_cache_path'))
			{
				self::$internal_cache_path = APPPATH.'cache/';
			}

			// Load cached configuration and language files
			self::$internal_cache['configuration'] = self::cache('configuration', self::$cache_lifetime);
			self::$internal_cache['language']      = self::cache('language', self::$cache_lifetime);

			// Load cached file paths
			self::$internal_cache['find_file_paths'] = self::cache('find_file_paths', self::$cache_lifetime);

			// Enable cache saving
			Event::add('system.shutdown', array(__CLASS__, 'internal_cache_save'));
		}

		// Disable notices and "strict" errors
		$ER = error_reporting(~E_NOTICE & ~E_STRICT);

		// Set the user agent
		self::$user_agent = ( ! empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');

		if (function_exists('date_default_timezone_set'))
		{
			$timezone = self::config('locale.timezone');

			// Set default timezone, due to increased validation of date settings
			// which cause massive amounts of E_NOTICEs to be generated in PHP 5.2+
			date_default_timezone_set(empty($timezone) ? date_default_timezone_get() : $timezone);
		}

		// Restore error reporting
		error_reporting($ER);

		// Start output buffering
		ob_start(array(__CLASS__, 'output_buffer'));

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Send default text/html UTF-8 header
		header('Content-Type: text/html; charset='.Kohana::CHARSET);

		// Load i18n
		new I18n;

		// Enable exception handling
		Kohana_Exception::enable();

		// Enable error handling
		Kohana_PHP_Exception::enable();

		// Load locales
		$locales = self::config('locale.language');

		// Make first locale UTF-8
		$locales[0] .= '.UTF-8';

		// Set locale information
		self::$locale = setlocale(LC_ALL, $locales);

		// Enable Kohana routing
		Event::add('system.routing', array('Router', 'find_uri'));
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Kohana controller initialization
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable Kohana 404 pages
		Event::add('system.404', array('Kohana_404_Exception', 'trigger'));

		// Enable Kohana output handling
		Event::add('system.shutdown', array('Kohana', 'shutdown'));

		if (self::config('core.enable_hooks') === TRUE)
		{
			// Find all the hook files
			$hooks = self::list_files('hooks', TRUE);

			foreach ($hooks as $file)
			{
				// Load the hook
				include $file;
			}
		}

		// Setup is complete, prevent it from being run again
		$run = TRUE;

		// Stop the environment setup routine
		Benchmark::stop(SYSTEM_BENCHMARK.'_environment_setup');
	}

	/**
	 * Loads the controller and initializes it. Runs the pre_controller,
	 * post_controller_constructor, and post_controller events. Triggers
	 * a system.404 event when the route cannot be mapped to a controller.
	 *
	 * This method is benchmarked as controller_setup and controller_execution.
	 *
	 * @return  object  instance of controller
	 */
	public static function & instance()
	{
		if (self::$instance === NULL)
		{
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

			if (Router::$method[0] === '_')
			{
				// Do not allow access to hidden methods
				Event::run('system.404');
			}

			// Include the Controller file
			require Router::$controller_path;

			try
			{
				// Start validation of the controller
				$class = new ReflectionClass(ucfirst(Router::$controller).'_Controller');
			}
			catch (ReflectionException $e)
			{
				// Controller does not exist
				Event::run('system.404');
			}

			if ($class->isAbstract() OR (IN_PRODUCTION AND $class->getConstant('ALLOW_PRODUCTION') == FALSE))
			{
				// Controller is not allowed to run in production
				Event::run('system.404');
			}

			// Run system.pre_controller
			Event::run('system.pre_controller');

			// Create a new controller instance
			$controller = $class->newInstance();

			// Controller constructor has been executed
			Event::run('system.post_controller_constructor');

			try
			{
				// Load the controller method
				$method = $class->getMethod(Router::$method);

				if ($method->isProtected() or $method->isPrivate())
				{
					// Do not attempt to invoke protected methods
					throw new ReflectionException('protected controller method');
				}

				// Default arguments
				$arguments = Router::$arguments;
			}
			catch (ReflectionException $e)
			{
				// Use __call instead
				$method = $class->getMethod('__call');

				// Use arguments in __call format
				$arguments = array(Router::$method, Router::$arguments);
			}

			// Stop the controller setup benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_setup');

			// Start the controller execution benchmark
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

			// Execute the controller method
			$method->invokeArgs($controller, $arguments);

			// Controller method has been executed
			Event::run('system.post_controller');

			// Stop the controller execution benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');
		}

		return self::$instance;
	}

	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the SYSPATH.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = FALSE)
	{
		if ($process === TRUE)
		{
			// Add APPPATH as the first path
			self::$include_paths = array(APPPATH);

			foreach (self::$configuration['core']['modules'] as $path)
			{
				if ($path = str_replace('\\', '/', realpath($path)))
				{
					// Add a valid path
					self::$include_paths[] = $path.'/';
				}
			}

			// Add SYSPATH as the last path
			self::$include_paths[] = SYSPATH;
		}

		return self::$include_paths;
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = TRUE)
	{
		if (self::$configuration === NULL)
		{
			// Load core configuration
			self::$configuration['core'] = self::config_load('core');

			// Re-parse the include paths
			self::include_paths(TRUE);
		}

		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if ( ! isset(self::$configuration[$group]))
		{
			// Load the configuration group
			self::$configuration[$group] = self::config_load($group, $required);
		}

		// Get the value of the key string
		$value = self::key_string(self::$configuration, $key);

		if ($slash === TRUE AND is_string($value) AND $value !== '')
		{
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		self::config($key);

		if (substr($key, 0, 7) === 'routes.')
		{
			// Routes cannot contain sub keys due to possible dots in regex
			$keys = explode('.', $key, 2);
		}
		else
		{
			// Convert dot-noted key string to an array
			$keys = explode('.', $key);
		}

		// Used for recursion
		$conf =& self::$configuration;
		$last = count($keys) - 1;

		foreach ($keys as $i => $k)
		{
			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}

		if ($key === 'core.modules')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array
	 */
	public static function config_load($name, $required = TRUE)
	{
		if ($name === 'core')
		{
			// Load the application configuration file
			require APPPATH.'config/config'.EXT;

			if ( ! isset($config['site_domain']))
			{
				// Invalid config file
				die('Your Kohana application configuration file is not valid.');
			}

			return $config;
		}

		if (isset(self::$internal_cache['configuration'][$name]))
			return self::$internal_cache['configuration'][$name];

		// Load matching configs
		$configuration = array();

		if ($files = self::find_file('config', $name, $required))
		{
			foreach ($files as $file)
			{
				require $file;

				if (isset($config) AND is_array($config))
				{
					// Merge in configuration
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		if ( ! isset(self::$write_cache['configuration']))
		{
			// Cache has changed
			self::$write_cache['configuration'] = TRUE;
		}

		return self::$internal_cache['configuration'][$name] = $configuration;
	}

	/**
	 * Clears a config group from the cached configuration.
	 *
	 * @param   string  config group
	 * @return  void
	 */
	public static function config_clear($group)
	{
		// Remove the group from config
		unset(self::$configuration[$group], self::$internal_cache['configuration'][$group]);

		if ( ! isset(self::$write_cache['configuration']))
		{
			// Cache has changed
			self::$write_cache['configuration'] = TRUE;
		}
	}

	/**
	 * Load data from a simple cache file. This should only be used internally,
	 * and is NOT a replacement for the Cache library.
	 *
	 * @param   string   unique name of cache
	 * @param   integer  expiration in seconds
	 * @return  mixed
	 */
	public static function cache($name, $lifetime)
	{
		if ($lifetime > 0)
		{
			$path = self::$internal_cache_path.'kohana_'.$name;

			if (is_file($path))
			{
				// Check the file modification time
				if ((time() - filemtime($path)) < $lifetime)
				{
					// Cache is valid
					return unserialize(file_get_contents($path));
				}
				else
				{
					// Cache is invalid, delete it
					unlink($path);
				}
			}
		}

		// No cache found
		return NULL;
	}

	/**
	 * Save data to a simple cache file. This should only be used internally, and
	 * is NOT a replacement for the Cache library.
	 *
	 * @param   string   cache name
	 * @param   mixed    data to cache
	 * @param   integer  expiration in seconds
	 * @return  boolean
	 */
	public static function cache_save($name, $data, $lifetime)
	{
		if ($lifetime < 1)
			return FALSE;

		$path = self::$internal_cache_path.'kohana_'.$name;

		if ($data === NULL)
		{
			// Delete cache
			return (is_file($path) and unlink($path));
		}
		else
		{
			// Write data to cache file
			return (bool) file_put_contents($path, serialize($data));
		}
	}

	/**
	 * Kohana output handler.
	 *
	 * @param   string  current output buffer
	 * @return  string
	 */
	public static function output_buffer($output)
	{
		if ( ! Event::has_run('system.send_headers'))
		{
			// Run the send_headers event, specifically for cookies being set
			Event::run('system.send_headers');
		}

		// Set final output
		self::$output = $output;

		// Set and return the final output
		return $output;
	}

	/**
	 * Closes all open output buffers, either by flushing or cleaning all
	 * open buffers, including the Kohana output buffer.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @return  void
	 */
	public static function close_buffers($flush = TRUE)
	{
		if (ob_get_level() >= self::$buffer_level)
		{
			// Set the close function
			$close = ($flush === TRUE) ? 'ob_end_flush' : 'ob_end_clean';

			while (ob_get_level() > self::$buffer_level)
			{
				// Flush or clean the buffer
				$close();
			}

			// This will flush the Kohana buffer, which sets self::$output
			ob_end_clean();

			// Reset the buffer level
			self::$buffer_level = ob_get_level();
		}
	}

	/**
	 * Triggers the shutdown of Kohana by closing the output buffer, runs the system.display event.
	 *
	 * @return  void
	 */
	public static function shutdown()
	{
		// Close output buffers
		self::close_buffers(TRUE);

		// Run the output event
		Event::run('system.display', self::$output);

		// Render the final output
		self::render(self::$output);
	}

	/**
	 * Inserts global Kohana variables into the generated output and prints it.
	 *
	 * @param   string  final output that will displayed
	 * @return  void
	 */
	public static function render($output)
	{
		if (self::config('core.render_stats') === TRUE)
		{
			// Fetch memory usage in MB
			$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

			// Fetch benchmark for page execution time
			$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');

			// Replace the global template variables
			$output = str_replace(
				array
				(
					'{kohana_version}',
					'{kohana_codename}',
					'{execution_time}',
					'{memory_usage}',
					'{included_files}',
				),
				array
				(
					KOHANA::VERSION,
					KOHANA::CODENAME,
					$benchmark['time'],
					number_format($memory, 2).'MB',
					count(get_included_files()),
				),
				$output
			);
		}

		if ($level = self::config('core.output_compression') AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
		{
			if ($level < 1 OR $level > 9)
			{
				// Normalize the level to be an integer between 1 and 9. This
				// step must be done to prevent gzencode from triggering an error
				$level = max(1, min($level, 9));
			}

			if (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
			{
				$compress = 'gzip';
			}
			elseif (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE)
			{
				$compress = 'deflate';
			}
		}

		if (isset($compress) AND $level > 0)
		{
			switch ($compress)
			{
				case 'gzip':
					// Compress output using gzip
					$output = gzencode($output, $level);
				break;
				case 'deflate':
					// Compress output using zlib (HTTP deflate)
					$output = gzdeflate($output, $level);
				break;
			}

			// This header must be sent with compressed content to prevent
			// browser caches from breaking
			header('Vary: Accept-Encoding');

			// Send the content encoding header
			header('Content-Encoding: '.$compress);

			// Sending Content-Length in CGI can result in unexpected behavior
			if (stripos(PHP_SAPI, 'cgi') === FALSE)
			{
				header('Content-Length: '.strlen($output));
			}
		}

		echo $output;
	}

	/**
	 * Provides class auto-loading.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class)
	{
		if (class_exists($class, FALSE))
			return TRUE;

		if (($suffix = strrpos($class, '_')) > 0)
		{
			// Find the class suffix
			$suffix = substr($class, $suffix + 1);
		}
		else
		{
			// No suffix
			$suffix = FALSE;
		}

		if ($suffix === 'Core')
		{
			$type = 'libraries';
			$file = substr($class, 0, -5);
		}
		elseif ($suffix === 'Controller')
		{
			$type = 'controllers';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -11));
		}
		elseif ($suffix === 'Model')
		{
			$type = 'models';
			// Lowercase filename
			$file = strtolower(substr($class, 0, -6));
		}
		elseif ($suffix === 'Driver')
		{
			$type = 'libraries/drivers';
			$file = str_replace('_', '/', substr($class, 0, -7));
		}
		else
		{
			// This could be either a library or a helper, but libraries must
			// always be capitalized, so we check if the first character is
			// uppercase. If it is, we are loading a library, not a helper.
			$type = ($class[0] < 'a') ? 'libraries' : 'helpers';
			$file = $class;
		}

		if ($filename = self::find_file($type, $file))
		{
			// Load the class
			require $filename;
		}
		else
		{
			// The class could not be found
			return FALSE;
		}

		if ($filename = self::find_file($type, self::$configuration['core']['extension_prefix'].$class))
		{
			// Load the class extension
			require $filename;
		}
		elseif ($suffix !== 'Core' AND class_exists($class.'_Core', FALSE))
		{
			// Class extension to be evaluated
			$extension = 'class '.$class.' extends '.$class.'_Core { }';

			// Start class analysis
			$core = new ReflectionClass($class.'_Core');

			if ($core->isAbstract())
			{
				// Make the extension abstract
				$extension = 'abstract '.$extension;
			}

			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it gets the job done.
			eval($extension);
		}

		return TRUE;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. Configuration and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Kohana_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for (without extension)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if ($ext == '')
		{
			// Use the default extension
			$ext = EXT;
		}
		else
		{
			// Add a period before the extension
			$ext = '.'.$ext;
		}

		// Search path
		$search = $directory.'/'.$filename.$ext;

		if (isset(self::$internal_cache['find_file_paths'][$search]))
			return self::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = self::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config' OR $directory === 'messages')
		{
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				// Directory i18n key
				$directory = 'core.'.inflector::singular($directory);

				// If the file is required, throw an exception
				throw new Kohana_Exception('The requested :resource:, :file:, could not be found', array(':resource:' => self::message($directory), ':file:' =>$filename));
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		if ( ! isset(self::$write_cache['find_file_paths']))
		{
			// Write cache at shutdown
			self::$write_cache['find_file_paths'] = TRUE;
		}

		return self::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(self::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path))
			{
				$items = (array) glob($path.'*');

				if ( ! empty($items))
				{
					foreach ($items as $index => $item)
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
			}
		}

		return $files;
	}

	/**
	 * Fetch a message item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function message($key, $args = array())
	{
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Get locale name
		$locale = self::config('locale.language.0');

		if ( ! isset(self::$internal_cache['messages'][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($file = self::find_file('messages', $group))
			{
				include $file[0];
			}

			if ( ! isset(self::$write_cache['messages']))
			{
				// Write language cache
				self::$write_cache['messages'] = TRUE;
			}

			self::$internal_cache['messages'][$group] = $messages;
		}

		// Get the line from cache
		$line = self::key_string(self::$internal_cache['messages'], $key);

		if ($line === NULL)
		{
			Kohana_Log::add('error', 'Missing messages entry '.$key.' for message '.$group);

			// Return the key string as fallback
			return $key;
		}

		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  void    if the key is not found
	 */
	public static function key_string($array, $keys)
	{
		if (empty($array))
			return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		do
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
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

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = NULL)
	{
		if (is_object($array) AND ($array instanceof ArrayObject))
		{
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			// Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					// Make the value an array
					$row[$key] = array();
				}
				else
				{
					// Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}

	/**
	 * Retrieves current user agent information:
	 * keys:  browser, version, platform, mobile, robot, referrer, languages, charsets
	 * tests: is_browser, is_mobile, is_robot, accept_lang, accept_charset
	 *
	 * @param   string   key or test name
	 * @param   string   used with "accept" tests: user_agent(accept_lang, en)
	 * @return  array    languages and charsets
	 * @return  string   all other keys
	 * @return  boolean  all tests
	 */
	public static function user_agent($key = 'agent', $compare = NULL)
	{
		static $info;

		// Return the raw string
		if ($key === 'agent')
			return self::$user_agent;

		if ($info === NULL)
		{
			// Parse the user agent and extract basic information
			$agents = self::config('user_agents');

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos(self::$user_agent, $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', self::$user_agent, $match))
						{
							// Set the browser version
							$info['version'] = $match[1];
						}

						// Set the agent name
						$info[$type] = $name;
						break;
					}
				}
			}
		}

		if (empty($info[$key]))
		{
			switch ($key)
			{
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$return = ! empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					{
						if (preg_match_all('/[-a-z]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'charsets':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
					{
						if (preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'referrer':
					if ( ! empty($_SERVER['HTTP_REFERER']))
					{
						// Found a result
						$return = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			// Cache the return value
			isset($return) and $info[$key] = $return;
		}

		if ( ! empty($compare))
		{
			// The comparison must always be lowercase
			$compare = strtolower($compare);

			switch ($key)
			{
				case 'accept_lang':
					// Check if the lange is accepted
					return in_array($compare, self::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, self::user_agent('charsets'));
				break;
				default:
					// Invalid comparison
					return FALSE;
				break;
			}
		}

		// Return the key, if set
		return isset($info[$key]) ? $info[$key] : NULL;
	}

	/**
	 * Quick debugging of any variable. Any number of parameters can be set.
	 *
	 * @return  string
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach ($params as $var)
		{
			$output[] = '<pre>('.gettype($var).') '.html::specialchars(print_r($var, TRUE)).'</pre>';
		}

		return implode("\n", $output);
	}

	/**
	 * Simplifies [back trace][ref-btr] information.
	 *
	 * [ref-btr]: http://php.net/debug_backtrace
	 *
	 * @param   array   backtrace generated by an exception or debug_backtrace
	 * @return  string
	 */
	public static function read_trace(array $trace_array)
	{
		$file = NULL;

		$ouput = array();
		foreach ($trace_array as $trace)
		{
			if (isset($trace['file']))
			{
				$line = '<strong>'.Kohana::debug_path($trace['file']).'</strong>';

				if (isset($trace['line']))
				{
					$line .= ', line <strong>'.$trace['line'].'</strong>';
				}

				$output[] = $line;
			}

			if (isset($trace['function']))
			{
				// Is this an inline function?
				$inline = in_array($trace['function'], array('require', 'require_once', 'include', 'include_once', 'echo', 'print'));

				$line = array();

				if (isset($trace['class']))
				{
					$line[] = $trace['class'];

					if (isset($trace['type']))
					{
						$line[] .= $trace['type'];
					}
				}

				$line[] = $trace['function'].($inline ? ' ' : '(');

				$args = array();

				if ( ! empty($trace['args']))
				{
					foreach ($trace['args'] as $arg)
					{
						if (is_string($arg) AND file_exists($arg))
						{
							// Sanitize path
							$arg = Kohana::debug_path($arg);
						}

						$args[] = '<code>'.text::limit_chars(html::specialchars(self::debug_var($arg)), 50, '...').'</code>';
					}
				}

				$line[] = implode(', ', $args).($inline ? '' : ')');

				$output[] = "\t".implode('', $line);
			}
		}

		return $output;
	}

	/**
	 * Removes APPPATH, SYSPATH, MODPATH, and DOCROOT from filenames, replacing
	 * them with the plain text equivalents.
	 *
	 * @param   string  path to sanitize
	 * @return  string
	 */
	public static function debug_path($file)
	{
		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, SYSPATH) === 0)
		{
			$file = 'SYSPATH/'.substr($file, strlen(SYSPATH));
		}
		elseif (strpos($file, MODPATH) === 0)
		{
			$file = 'MODPATH/'.substr($file, strlen(MODPATH));
		}
		elseif (strpos($file, DOCROOT) === 0)
		{
			$file = 'DOCROOT/'.substr($file, strlen(DOCROOT));
		}

		return $file;
	}

	/**
	 * Similar to print_r or var_dump, generates a string representation of
	 * any variable.
	 *
	 * @param   mixed    variable to dump
	 * @param   boolean  internal recursion
	 * @return  string
	 */
	public static function debug_var($var, $recursion = FALSE)
	{
		static $objects;

		if ($recursion === FALSE)
		{
			$objects = array();
		}

		switch (gettype($var))
		{
			case 'object':
				// Unique hash of the object
				$hash = spl_object_hash($var);

				$object = new ReflectionObject($var);
				$more = FALSE;
				$out = 'object '.$object->getName().' { ';

				if ($recursion === TRUE AND in_array($hash, $objects))
				{
					$out .= '*RECURSION*';
				}
				else
				{
					// Add the hash to the objects, to detect later recursion
					$objects[] = $hash;

					foreach ($object->getProperties() as $property)
					{
						$out .= ($more === TRUE ? ', ' : '').$property->getName().' => ';
						if ($property->isPublic())
						{
							$out .= self::debug_var($property->getValue($var), TRUE);
						}
						elseif ($property->isPrivate())
						{
							$out .= '*PRIVATE*';
						}
						else
						{
							$out .= '*PROTECTED*';
						}
						$more = TRUE;
					}
				}
				return $out.' }';
			case 'array':
				$more = FALSE;
				$out = 'array (';
				foreach ((array) $var as $key => $val)
				{
					if ( ! is_int($key))
					{
						$key = self::debug_var($key, TRUE).' => ';
					}
					else
					{
						$key = '';
					}
					$out .= ($more ? ', ' : '').$key.self::debug_var($val, TRUE);
					$more = TRUE;
				}
				return $out.')';
			case 'string':
				return "'$var'";
			case 'float':
				return number_format($var, 6).'&hellip;';
			case 'boolean':
				return $var === TRUE ? 'TRUE' : 'FALSE';
			default:
				return (string) $var;
		}
	}

	/**
	 * Saves the internal caches: configuration, include paths, etc.
	 *
	 * @return  boolean
	 */
	public static function internal_cache_save()
	{
		if ( ! is_array(self::$write_cache))
			return FALSE;

		// Get internal cache names
		$caches = array_keys(self::$write_cache);

		// Nothing written
		$written = FALSE;

		foreach ($caches as $cache)
		{
			if (isset(self::$internal_cache[$cache]))
			{
				// Write the cache file
				self::cache_save($cache, self::$internal_cache[$cache], self::$configuration['core']['internal_cache']);

				// A cache has been written
				$written = TRUE;
			}
		}

		return $written;
	}

} // End Kohana

class Kohana_Exception extends Exception {

	// Generate HTML errors
	public static $html_output = TRUE;

	// Show stack traces in errors
	public static $trace_output = TRUE;

	// Show source code in errors
	public static $source_output = TRUE;

	// Error resources have not been loaded
	protected static $error_resources = FALSE;

	// To hold unique identifier to distinguish error output
	protected $instance_identifier;

	// Error code
	protected $code = E_KOHANA;

	/**
	 * Creates a new translated exception.
	 *
	 * @param string error message
	 * @param array translation variables
	 * @return void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		$this->instance_identifier = uniqid();

		// Translate the error message
		$message = __($message, $variables);

		// Sets $this->message the proper way
		parent::__construct($message, $code);
	}

	/**
	 * Enable Kohana exception handling.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		set_exception_handler(array(__CLASS__, 'handle'));
	}

	/**
	 * Disable Kohana exception handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		restore_exception_handler();
	}

	/**
	 * PHP exception handler.
	 *
	 * @param   object  Exception instance
	 * @return  void
	 */
	public static function handle($exception)
	{
		// An error has been triggered
		Kohana::$has_error = TRUE;

		// Display (and log the error message)
		echo $exception;

		// Exceptions must halt execution
		exit;
	}

	/**
	 * Outputs an inline error message.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		try
		{
			// Load the error message information
			if (is_numeric($this->code))
			{
				$errors = Kohana::message('core.errors');
				if ( ! empty($errors[$this->code]))
				{
					list($level, $type, $description) = $errors[$this->code];
				}
				else
				{
					$level = 1;
					$type = 'Unknown Error';
					$description = '';
				}
			}
			else
			{
				// Custom error message, this will never be logged
				$level = 5;
				$type = $this->code;
				$description = '';
			}

			if ($level <= Kohana::config('log.log_threshold'))
			{
				// Log the error
				Kohana_Log::add('error', __('Uncaught %type %message% in file :file on line %line%',
				                array('%type%' => $type, '%message%' => $this->message, '%file%' => $this->file, '%line%' => $this->line)));
			}

			if (Kohana::config('core.display_errors') === FALSE)
			{
				// Get the i18n messages
				$this->error   = __('Unable to Complete Request');
				$this->message = __('You can go to the <a href="%site%">home page</a> or <a href="%uri%">try again</a>.',
				                    array('%site%' => url::site(), '%uri%' => url::site(Router::$current_uri)));

				// Do not show the file or line
				$this->file = $this->line = NULL;

				require Kohana::find_file('views', 'kohana/error_disabled', TRUE);
			}
			else
			{
				$message = $this->message;
				// Sanitize filepath for greater security
				$file  = Kohana::debug_path($this->file);
				$line  = $this->line;
				$instance_identifier = $this->instance_identifier;

				if (Kohana_Exception::$html_output)
				{
					if ( ! empty($this->file))
					{
						// Source code
						$source = '';

						if (Kohana_Exception::$source_output)
						{
							// Lines to read from the source
							$start_line = $line - 4;
							$end_line   = $line + 3;

							$file_source = fopen($this->file, 'r');
							$file_line   = 1;

							while ($read_line = fgets($file_source))
							{
								if ($file_line >= $start_line)
								{
									if ($file_line === $line)
									{
										// Wrap the text of this line in <span> tags, for highlighting
										$read_line = '<span>'.html::specialchars($read_line).'</span>';
									}
									else
									{
										$read_line = html::specialchars($read_line);
									}
									$source .= $read_line;
								}

								if (++$file_line > $end_line)
								{
									// Stop reading lines
									fclose($file_source);
									break;
								}
							}
						}

						if (Kohana_Exception::$trace_output)
						{
							$trace = $this->getTrace();

							// Read trace
							$trace = Kohana::read_trace($trace);
						}
					}

					if (method_exists($this, 'sendHeaders') AND ! headers_sent())
					{
						// Send the headers if they have not already been sent
						$this->sendHeaders();
					}
				}
				else
				{
					// Show only the error text
					return $type.': '.$this->message.' [ '.$file.', '.$line.' ] '."\n";
				}

				ob_start();

				if ( ! self::$error_resources)
				{
					// Include error style
					echo '<style type="text/css">', "\n";
					include Kohana::find_file('views', 'kohana/error_style', FALSE, 'css');
					echo "\n", '</style>', "\n";

					// Include error js
					echo '<script type="text/javascript">', "\n";
					include Kohana::find_file('views', 'kohana/error_script', FALSE, 'js');
					echo "\n", '</script>', "\n";

					// Error resources have been loaded
					self::$error_resources = TRUE;
				}

				require Kohana::find_file('views', 'kohana/error', TRUE);
			}

			return ob_get_clean();
		}
		catch (Exception $e)
		{
			// This shouldn't happen unless Kohana files are missing
			if ( ! IN_PRODUCTION)
			{
				die('Exception thrown inside '.__CLASS__.': '.$e->getMessage());
			}
			else
			{
				die('Unknown Error');
			}
		}
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception

class Kohana_PHP_Exception extends Kohana_Exception {

	/**
	 * Enable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		set_error_handler(array(__CLASS__, 'handle'));
	}

	/**
	 * Disable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		restore_error_handler();
	}

	/**
	 * Create a new PHP error exception.
	 *
	 * @return  void
	 */
	public function __construct($code, $error, $file, $line, $context = NULL)
	{
		parent::__construct($error);

		// Set the error code, file, line, and context manually
		$this->code = $code;
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 * PHP error handler.
	 *
	 * @throws  Kohana_PHP_Exception
	 * @return  void
	 */
	public static function handle($code, $error, $file, $line, $context = NULL)
	{
		if ((error_reporting() & $code) === 0)
		{
			// Respect error_reporting settings
			return;
		}

		// An error has been triggered
		Kohana::$has_error = TRUE;

		// Create an exception
		$exception = new Kohana_PHP_Exception($code, $error, $file, $line, $context);

		echo $exception;

		if (Kohana::config('core.display_errors') === FALSE)
		{
			// Execution must halt
			exit;
		}
	}
} // End Kohana PHP Exception

/**
 * Creates a custom exception message.
 */
class Kohana_User_Exception extends Kohana_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($title, $message, array $variables = NULL)
	{
		parent::__construct($message, $variables);

		// Code is the error title
		$this->code = $title;
	}

} // End Kohana PHP Exception

/**
 * Creates a "Page Not Found" exception.
 */
class Kohana_404_Exception extends Kohana_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URI of page
	 * @param  string  custom error template
	 */
	public function __construct($page = NULL)
	{
		if ($page === NULL)
		{
			// Use the complete URI
			$page = Router::$complete_uri;
		}

		parent::__construct(__('The page you requested, :page, could not be found.', array(':page' => $page)));
	}

	/**
	 * Throws a new 404 exception.
	 *
	 * @throws  Kohana_404_Exception
	 * @return  void
	 */
	public static function trigger($page = NULL)
	{
		throw new Kohana_404_Exception($page);
	}

	/**
	 * Sends 404 headers, to emulate server behavior.
	 *
	 * @return void
	 */
	public function sendHeaders()
	{
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Kohana 404 Exception
