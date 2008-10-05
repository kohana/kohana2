<?php
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
final class Kohana {

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

	// Logged messages
	private static $log;

	// Cache lifetime
	private static $cache_lifetime;

	// Log levels
	private static $log_levels = array
	(
		'error' => 1,
		'alert' => 2,
		'info'  => 3,
		'debug' => 4,
	);

	// Internal caches and write status
	private static $internal_cache = array();
	private static $write_cache;

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

		// Define Kohana error constant
		define('E_KOHANA', 42);

		// Define 404 error constant
		define('E_PAGE_NOT_FOUND', 43);

		// Define database error constant
		define('E_DATABASE_ERROR', 44);

		if (self::$cache_lifetime = self::config('core.internal_cache'))
		{
			// Load cached configuration and language files
			self::$internal_cache['configuration'] = self::cache('configuration', self::$cache_lifetime);
			self::$internal_cache['language']      = self::cache('language', self::$cache_lifetime);

			// Load cached file paths
			self::$internal_cache['find_file_paths'] = self::cache('find_file_paths', self::$cache_lifetime);

			// Enable cache saving
			Event::add('system.shutdown', array(__CLASS__, 'internal_cache_save'));
		}

		// Send default text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// Enable exception handling
		Kohana_Exception::enable();

		// Enable error handling
		Kohana_PHP_Exception::enable();

		if (self::$configuration['core']['log_threshold'] > 0)
		{
			// Set the log directory
			self::log_directory(self::$configuration['core']['log_directory']);

			// Enable log writing at shutdown
			register_shutdown_function(array(__CLASS__, 'log_save'));
		}

		// Disable notices and "strict" errors, to prevent some oddities in
		// PHP 5.2 and when using Kohana under CLI
		$ER = error_reporting(~E_NOTICE & ~E_STRICT);

		// Set the user agent
		self::$user_agent = trim($_SERVER['HTTP_USER_AGENT']);

		if ( ! ($timezone = Kohana::config('locale.timezone')))
		{
			// Get the default timezone
			$timezone = date_default_timezone_get();
		}

		// Restore error reporting
		error_reporting($ER);

		// Set the default timezone
		date_default_timezone_set($timezone);

		// Start output buffering
		ob_start(array(__CLASS__, 'output_buffer'));

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));

		// Load locales
		$locales = Kohana::config('locale.language');

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

		if ($config = Kohana::config('core.enable_hooks'))
		{
			// Start the loading_hooks routine
			Benchmark::start(SYSTEM_BENCHMARK.'_loading_hooks');

			$hooks = array();

			if ( ! is_array($config))
			{
				// All of the hooks are enabled, so we use list_files
				$hooks = Kohana::list_files('hooks', TRUE);
			}
			else
			{
				// Individual hooks need to be found
				foreach ($config as $name)
				{
					if ($hook = Kohana::find_file('hooks', $name, FALSE))
					{
						// Hook was found, add it to loaded hooks
						$hooks[] = $hook;
					}
					else
					{
						// This should never happen
						Kohana::log('error', 'Hook not found: '.$name);
					}
				}
			}

			// Length of extension, for offset
			$ext = -(strlen(EXT));

			foreach ($hooks as $hook)
			{
				// Validate the filename extension
				if (substr($hook, $ext) === EXT)
				{
					// Hook was found, include it
					include $hook;
				}
				else
				{
					// This should never happen
					Kohana::log('error', 'Hook not found: '.$hook);
				}
			}

			// Stop the loading_hooks routine
			Benchmark::stop(SYSTEM_BENCHMARK.'_loading_hooks');
		}

		// Setup is complete, prevent it from being run again
		$run = TRUE;
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
			// Routing has been completed
			Event::run('system.post_routing');

			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

			// Log the current routing state for debugging purposes
			Kohana::log('debug', 'Routing "'.Router::$current_uri.'" using the "'.Router::$current_route.'" route, '.Router::$controller.'::'.Router::$method);

			if (Router::$controller === NULL OR Router::$method[0] === '_')
			{
				// Do not allow access to hidden methods
				Event::run('system.404');
			}

			try
			{
				// Start validation of the controller
				$class = new ReflectionClass('Controller_'.ucfirst(Router::$controller));
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
					throw new ReflectionException('invalid router method');
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
			// Get standard PHP include paths
			// $php_paths = get_include_path();

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

			// New PHP include paths
			// $new_paths = array_diff(self::$include_paths, explode(PATH_SEPARATOR, $php_paths));

			// set_include_path($php_paths.PATH_SEPARATOR.implode(PATH_SEPARATOR, $new_paths));
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
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function log($type, $message)
	{
		if (self::$log_levels[$type] <= self::$configuration['core']['log_threshold'])
		{
			self::$log[] = array(date('Y-m-d H:i:s P'), $type, $message);
		}
	}

	/**
	 * Save all currently logged messages.
	 *
	 * @return  void
	 */
	public static function log_save()
	{
		if (empty(self::$log))
			return;

		// Filename of the log
		$filename = self::log_directory().date('Y-m-d').'.log'.EXT;

		if ( ! is_file($filename))
		{
			// Write the SYSPATH checking header
			file_put_contents($filename,
				'<?php defined(\'SYSPATH\') or die(\'No direct access.\'); ?>'.PHP_EOL.PHP_EOL);

			// Prevent external writes
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();

		do
		{
			// Load the next mess
			list ($date, $type, $text) = array_shift(self::$log);

			// Add a new message line
			$messages[] = $date.' --- '.$type.': '.$text;
		}
		while ( ! empty(self::$log));

		// Write messages to log file
		file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);
	}

	/**
	 * Get or set the logging directory.
	 *
	 * @param   string  new log directory
	 * @return  string
	 */
	public static function log_directory($dir = NULL)
	{
		static $directory;

		if ( ! empty($dir))
		{
			// Get the directory path
			$dir = realpath($dir);

			if (is_dir($dir) AND is_writable($dir))
			{
				// Change the log directory
				$directory = str_replace('\\', '/', $dir).'/';
			}
			else
			{
				// Log directory is invalid
				throw new Kohana_Exception('core.log_dir_unwritable', $dir);
			}
		}

		return $directory;
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
			$path = APPPATH.'cache/kohana_'.$name;

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

		$path = APPPATH.'cache/kohana_'.$name;

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
	 * open buffers, and optionally, the Kohana output buffer.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @param   boolean  close the kohana output buffer
	 * @return  void
	 */
	public static function close_buffers($flush = TRUE, $kohana_buffer = TRUE)
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

			if ($kohana_buffer === TRUE)
			{
				// This will flush the Kohana buffer, which sets self::$output
				ob_end_clean();

				// Reset the buffer level
				self::$buffer_level = ob_get_level();
			}
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
		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		// Fetch benchmark for page execution time
		$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');

		if (Kohana::config('core.render_stats') === TRUE)
		{
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
					KOHANA_VERSION,
					KOHANA_CODENAME,
					$benchmark['time'],
					number_format($memory, 2).'MB',
					count(get_included_files()),
				),
				$output
			);
		}

		if ($level = Kohana::config('core.output_compression') AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
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
	 * Displays a 404 page.
	 *
	 * @throws  Kohana_404_Exception
	 * @param   string  URI of page
	 * @param   string  custom template
	 * @return  void
	 */
	public static function show_404($page = FALSE, $template = FALSE)
	{
		throw new Kohana_404_Exception($page, $template);
	}

	/**
	 * Provides class auto-loading.
	 *
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class)
	{
		if (class_exists($class, FALSE))
			return TRUE;

		// Determine class filename
		$filename = str_replace('_', '/', strtolower($class));

		if ( ! ($path = Kohana::find_file('classes', $filename, FALSE)))
			return FALSE;

		// Load class
		require $path;

		if (class_exists($class.'_Core', FALSE))
		{
			if ($path = Kohana::find_file('extensions', $filename, FALSE))
			{
				// Load class extension
				require $path;
			}
			else
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
	 * @param   string   filename to look for (including extension only if 4th parameter is TRUE)
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

		if ($directory === 'config' OR $directory === 'i18n')
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
				throw new Kohana_Exception('core.resource_not_found', self::lang($directory), $filename);
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
	 * Lists all files in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @return  array    resolved filename paths
	 */
	public static function list_files($directory, $recursive = FALSE)
	{
		$files = array();
		$paths = array_reverse(Kohana::include_paths());

		foreach ($paths as $path)
		{
			if (is_dir($path.$directory))
			{
				$dir = new DirectoryIterator($path.$directory);

				foreach ($dir as $file)
				{
					$filename = $file->getFilename();

					if ($filename[0] === '.')
						continue;

					if ($file->isDir())
					{
						if ($recursive === TRUE)
						{
							// Recursively add files
							$files = array_merge($files, self::list_files($directory.'/'.$filename, TRUE));
						}
					}
					else
					{
						// Add the file to the files
						$files[$directory.'/'.$filename] = $file->getRealPath();
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @param   array    filenames to exclude
	 * @return  array    filenames and directories
	 */
	public static function new_list_files($directory = NULL, $recursive = FALSE, $exclude = NULL)
	{
		$files = array();
		$paths = array_reverse(Kohana::include_paths());

		foreach ($paths as $path)
		{
			if (is_dir($path.$directory))
			{
				$dir = new DirectoryIterator($path.$directory);

				foreach ($dir as $file)
				{
					$filename = $file->getFilename();

					if ($filename[0] === '.' OR ($exclude == TRUE AND in_array($filename, $exclude)))
						continue;

					if ($recursive == TRUE AND $file->isDir())
					{
						// Recursively add files
						$files[$filename] = self::new_list_files($directory.'/'.$filename, TRUE);
					}
					else
					{
						// Add the file to the files
						$files[] = $filename;
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Fetch an i18n language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = array())
	{
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Get locale name
		$locale = Kohana::config('locale.language.0');

		if ( ! isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($files = self::find_file('i18n', $locale.'/'.$group))
			{
				foreach ($files as $file)
				{
					include $file;

					// Merge in configuration
					if ( ! empty($lang) AND is_array($lang))
					{
						foreach ($lang as $k => $v)
						{
							$messages[$k] = $v;
						}
					}
				}
			}

			if ( ! isset(self::$write_cache['language']))
			{
				// Write language cache
				self::$write_cache['language'] = TRUE;
			}

			self::$internal_cache['language'][$locale][$group] = $messages;
		}

		// Get the line from cache
		$line = self::key_string(self::$internal_cache['language'][$locale], $key);

		if ($line === NULL)
		{
			Kohana::log('error', 'Missing i18n entry '.$key.' for language '.$locale);

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
			return Kohana::$user_agent;

		if ($info === NULL)
		{
			// Parse the user agent and extract basic information
			$agents = Kohana::config('user_agents');

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos(Kohana::$user_agent, $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', Kohana::$user_agent, $match))
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
					return in_array($compare, Kohana::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, Kohana::user_agent('charsets'));
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
	 * @return  array
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

						$args[] = '<code>'.self::debug_var($arg).'</code>';
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

	// Error resources have not been loaded
	protected static $error_resources = FALSE;

	// To hold unique identifier to distinguish error output
	protected $instance_identifier;

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

		if (is_numeric($exception->code))
		{
			$codes = Kohana::lang('errors');

			if ( ! empty($codes[$exception->code]))
			{
				list($level, $error) = $codes[$exception->code];
			}
			else
			{
				$level = 1;
				$error = get_class($exception);
			}
		}
		else
		{
			// Custom error message, this will never be logged
			$level = 5;
			$error = $exception->code;
		}

		if ($level <= Kohana::config('core.log_threshold'))
		{
			// Log the error
			Kohana::log('error', Kohana::lang('core.uncaught_exception', $error, $exception->message, $exception->file, $exception->line));
		}

		echo $exception;

		// Exceptions must halt execution
		exit;
	}

	// Error template
	public $template = 'kohana/error';

	// Error code
	protected $code = E_KOHANA;

	/**
	 * Creates a new i18n Kohana_Exception using the passed error and arguments.
	 *
	 * @return  void
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		// Fetch the error message
		$message = Kohana::lang($error, $args);

		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

		$this->instance_identifier = uniqid();

		// Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Outputs an inline error message.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		if (Kohana::config('core.display_errors') === FALSE)
		{
			// Load the "errors disabled" message
			$code  = Kohana::lang('core.generic_error');
			$error = Kohana::lang('core.errors_disabled', url::site(''), url::site(Router::$current_uri));

			// Do not show the file or line
			$file = $line = NULL;
		}
		else
		{
			// Load exception properties locally
			$code  = $this->code;
			$error = $this->message;
			$file  = $this->file;
			$line  = $this->line;
			$instance_identifier = $this->instance_identifier;

			// Load the i18n error name
			$code = Kohana::lang('errors.'.$code.'.1').' ('.$code.')';
		}

		if (Kohana_Exception::$html_output)
		{
			if ( ! empty($file))
			{
				// Lines to read from the source
				$start_line = $line - 4;
				$end_line   = $line + 3;

				$file_source = fopen($file, 'r');
				$file_line   = 1;

				// Source code
				$source = '';

				while ($read_line = fgets($file_source))
				{
					if ($file_line >= $start_line)
					{
						if ($file_line === $line)
						{
							// Wrap the text of this line in <span> tags, for highlighting
							$read_line = preg_replace('/^(\s+)(.+?)(\s+)$/', '$1<span>$2</span>$3', $read_line);
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

			// Sanitize filepath for greater security
			$file = Kohana::debug_path($file);
		}

		if ( ! Kohana_Exception::$html_output)
		{
			// Show only the error text
			return $code.': '.$error.' [ '.$file.', '.$line.' ] '."\n";
		}

		if (Kohana::config('core.display_errors'))
		{
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
			
			require Kohana::find_file('views', 'kohana/error', FALSE);
		}
		else
		{
			// Clean and stop all output buffers except the Kohana buffer
			Kohana::close_buffers(FALSE, FALSE);

			// Clean the Kohana output buffer
			ob_clean();

			require Kohana::find_file('views', 'kohana/error_disabled', FALSE);
		}

		return ob_get_clean();
	}

	/**
	 * Fetch the template name.
	 *
	 * @return  string
	 */
	public function getTemplate()
	{
		return $this->template;
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

		// Get the error level and name
		list ($level, $error) = Kohana::lang('errors.'.$exception->code);

		if ($level >= Kohana::config('core.log_threshold'))
		{
			// Log the error
			Kohana::log('error', Kohana::lang('core.uncaught_exception', $error, $exception->message, $exception->file, $exception->line));
		}

		echo $exception;

		if (Kohana::config('core.display_errors') === FALSE)
		{
			// Execution must halt
			exit;
		}
	}

	/**
	 * Create a new PHP error exception.
	 *
	 * @return  void
	 */
	public function __construct($code, $error, $file, $line, $context = NULL)
	{
		Exception::__construct($error);

		// Set the error code, file, line, and context manually
		$this->code = $code;
		$this->file = $file;
		$this->line = $line;

		$this->instance_identifier = uniqid();
	}

	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
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
	public function __construct($title, $message, $template = NULL)
	{
		Exception::__construct($message);

		// Code is the error title
		$this->code = $title;

		if ($template !== NULL)
		{
			// Override the default template
			$this->template = $template;
		}

		$this->instance_identifier = uniqid();
	}

} // End Kohana PHP Exception

/**
 * Creates a "Page Not Found" exception.
 */
class Kohana_404_Exception extends Kohana_Exception {

	/**
	 * Throws a new 404 exception.
	 *
	 * @throws  Kohana_404_Exception
	 * @return  void
	 */
	public static function trigger($page = NULL, $template = NULL)
	{
		throw new Kohana_404_Exception($page, $template);
	}

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URI of page
	 * @param  string  custom error template
	 */
	public function __construct($page = NULL, $template = NULL)
	{
		if ($page === NULL)
		{
			// Use the complete URI
			$page = Router::$complete_uri;
		}

		parent::__construct('core.page_not_found', $page);

		if ($template !== NULL)
		{
			// Override the default template
			$this->template = $template;
		}
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
