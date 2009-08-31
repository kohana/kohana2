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
	const LOCALE = 'en_US';
	
	// The singleton instance of the controller
	public static $instance;

	// Output buffering level
	protected static $buffer_level;

	// Will be set to TRUE when an exception is caught
	public static $has_error = FALSE;

	// The final output that will displayed by Kohana
	public static $output = '';

	// The current locale
	public static $locale;

	// Include paths
	protected static $include_paths;

	// Cache lifetime
	protected static $cache_lifetime;

	// Internal caches and write status
	protected static $internal_cache = array();
	protected static $write_cache;
	protected static $internal_cache_path;
	protected static $internal_cache_key;
	protected static $internal_cache_encrypt;
	
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
		
		if (Kohana_Config::instance()->loaded() === FALSE)
		{
			// Re-parse the include paths
			Kohana::include_paths(TRUE);
		}
		
		if (Kohana::$cache_lifetime = Kohana::config('core.internal_cache'))
		{
			// Are we using encryption for caches?
			Kohana::$internal_cache_encrypt	= Kohana::config('core.internal_cache_encrypt');
			
			if(Kohana::$internal_cache_encrypt===TRUE)
			{
				Kohana::$internal_cache_key = Kohana::config('core.internal_cache_key');
				
				// Be sure the key is of acceptable length for the mcrypt algorithm used
				Kohana::$internal_cache_key = substr(Kohana::$internal_cache_key, 0, 24);
			}
			
			// Set the directory to be used for the internal cache
			if ( ! Kohana::$internal_cache_path = Kohana::config('core.internal_cache_path'))
			{
				Kohana::$internal_cache_path = APPPATH.'cache/';
			}

			// Load cached configuration and language files
			Kohana::$internal_cache['configuration'] = Kohana::cache('configuration', Kohana::$cache_lifetime);
			Kohana::$internal_cache['language']      = Kohana::cache('language', Kohana::$cache_lifetime);

			// Load cached file paths
			Kohana::$internal_cache['find_file_paths'] = Kohana::cache('find_file_paths', Kohana::$cache_lifetime);

			// Enable cache saving
			Event::add('system.shutdown', array(__CLASS__, 'internal_cache_save'));
		}

		// Disable notices and "strict" errors
		$ER = error_reporting(~E_NOTICE & ~E_STRICT);

		if (function_exists('date_default_timezone_set'))
		{
			$timezone = Kohana::config('locale.timezone');

			// Set default timezone, due to increased validation of date settings
			// which cause massive amounts of E_NOTICEs to be generated in PHP 5.2+
			date_default_timezone_set(empty($timezone) ? date_default_timezone_get() : $timezone);
		}

		// Restore error reporting
		error_reporting($ER);

		// Start output buffering
		ob_start(array(__CLASS__, 'output_buffer'));

		// Save buffering level
		Kohana::$buffer_level = ob_get_level();

		// Set autoloader
		spl_autoload_register(array('Kohana', 'auto_load'));
		
		// Send default text/html UTF-8 header
		header('Content-Type: text/html; charset='.Kohana::CHARSET);

		// Load i18n
		new I18n;

		// Shutdown Exception handling
		Kohana_Shutdown_Exception::enable();

		// Enable exception handling
		Kohana_Exception::enable();

		// Enable error handling
		Kohana_PHP_Exception::enable();

		// Load locales
		$locales = Kohana::config('locale.language');

		// Make first locale the defined Kohana charset
		$locales[0] .= '.'.Kohana::CHARSET;

		// Set locale information
		Kohana::$locale = setlocale(LC_ALL, $locales);
		
		// Default to the default locale when none of the user defined ones where accepted
		Kohana::$locale = ! Kohana::$locale ? Kohana::LOCALE.'.'.Kohana::CHARSET : Kohana::$locale;
		
		// Set locale for the I18n system
		I18n::set_locale(Kohana::$locale);

		// Enable Kohana routing
		Event::add('system.routing', array('Router', 'find_uri'));
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Kohana controller initialization
		Event::add('system.execute', array('Kohana', 'instance'));

		// Enable Kohana 404 pages
		Event::add('system.404', array('Kohana_404_Exception', 'trigger'));

		// Enable Kohana output handling
		Event::add('system.shutdown', array('Kohana', 'shutdown'));

		if (Kohana::config('core.enable_hooks') === TRUE)
		{
			// Find all the hook files
			$hooks = Kohana::list_files('hooks', TRUE);

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
		if (Kohana::$instance === NULL)
		{
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

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

				// Method exists
				if (Router::$method[0] === '_')
				{
					// Do not allow access to hidden methods
					Event::run('system.404');
				}

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

		return Kohana::$instance;
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
			Kohana::$include_paths = array(APPPATH);

			foreach (Kohana::config('core.modules') as $path)
			{
				if ($path = str_replace('\\', '/', realpath($path)))
				{
					// Add a valid path
					Kohana::$include_paths[] = $path.'/';
				}
			}

			// Add SYSPATH as the last path
			Kohana::$include_paths[] = SYSPATH;
		}

		return Kohana::$include_paths;
	}

	/**
	 * Get a config item or group proxies Kohana_Config.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = FALSE)
	{
		return Kohana_Config::instance()->get($key,$slash,$required);
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
			$path = Kohana::$internal_cache_path.'kohana_'.$name;

			if (is_file($path))
			{
				// Check the file modification time
				if ((time() - filemtime($path)) < $lifetime)
				{
					// Cache is valid! Now, do we need to decrypt it?
					if(Kohana::$internal_cache_encrypt===TRUE)
					{
						$data		= file_get_contents($path);
						
						$iv_size	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
						$iv			= mcrypt_create_iv($iv_size, MCRYPT_RAND);
						
						$decrypted_text	= mcrypt_decrypt(MCRYPT_RIJNDAEL_256, Kohana::$internal_cache_key, $data, MCRYPT_MODE_ECB, $iv);
						
						$cache	= unserialize($decrypted_text);
						
						// If the key changed, delete the cache file
						if(!$cache)
							unlink($path);

						// If cache is false (as above) return NULL, otherwise, return the cache
						return ($cache ? $cache : NULL);
					}
					else
					{
						return unserialize(file_get_contents($path));
					}
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

		$path = Kohana::$internal_cache_path.'kohana_'.$name;

		if ($data === NULL)
		{
			// Delete cache
			return (is_file($path) and unlink($path));
		}
		else
		{
			// Using encryption? Encrypt the data when we write it
			if(Kohana::$internal_cache_encrypt===TRUE)
			{
				// Encrypt and write data to cache file
				$iv_size	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv			= mcrypt_create_iv($iv_size, MCRYPT_RAND);
				
				// Serialize and encrypt!
				$encrypted_text	= mcrypt_encrypt(MCRYPT_RIJNDAEL_256, Kohana::$internal_cache_key, serialize($data), MCRYPT_MODE_ECB, $iv);
				
				return (bool) file_put_contents($path, $encrypted_text);
			}
			else
			{
				// Write data to cache file
				return (bool) file_put_contents($path, serialize($data));
			}
		}
	}

	/**
	 * Kohana output handler. Called during ob_clean, ob_flush, and their variants.
	 *
	 * @param   string  current output buffer
	 * @return  string
	 */
	public static function output_buffer($output)
	{
		// Could be flushing, so send headers first
		if ( ! Event::has_run('system.send_headers'))
		{
			// Run the send_headers event
			Event::run('system.send_headers');
		}

		// Set final output
		Kohana::$output = $output;

		// Set and return the final output
		return Kohana::$output;
	}

	/**
	 * Closes all open output buffers, either by flushing or cleaning, and stores
	 * output buffer for display during shutdown.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @return  void
	 */
	public static function close_buffers($flush = TRUE)
	{
		if (ob_get_level() >= Kohana::$buffer_level)
		{
			// Set the close function
			$close = ($flush === TRUE) ? 'ob_end_flush' : 'ob_end_clean';

			while (ob_get_level() > Kohana::$buffer_level)
			{
				// Flush or clean the buffer
				$close();
			}

			// Store the Kohana output buffer
			ob_end_clean();
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
		Kohana::close_buffers(TRUE);

		// Run the output event
		Event::run('system.display', Kohana::$output);

		// Render the final output
		Kohana::render(Kohana::$output);
	}

	/**
	 * Inserts global Kohana variables into the generated output and prints it.
	 *
	 * @param   string  final output that will displayed
	 * @return  void
	 */
	public static function render($output)
	{
		if (Kohana::config('core.render_stats') === TRUE)
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

		if ($level = Kohana::config('core.output_compression') AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
		{
			if ($compress = request::preferred_encoding(array('gzip','deflate'), TRUE))
			{
				if ($level < 1 OR $level > 9)
				{
					// Normalize the level to be an integer between 1 and 9. This
					// step must be done to prevent gzencode from triggering an error
					$level = max(1, min($level, 9));
				}

				if ($compress === 'gzip')
				{
					// Compress output using gzip
					$output = gzencode($output, $level);
				}
				elseif ($compress === 'deflate')
				{
					// Compress output using zlib (HTTP deflate)
					$output = gzdeflate($output, $level);
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

		if ($filename = Kohana::find_file($type, $file))
		{
			// Load the class
			require $filename;
		}
		else
		{
			// The class could not be found
			return FALSE;
		}

		if ($filename = Kohana::find_file($type, Kohana::config('core.extension_prefix').$class))
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

		if (isset(Kohana::$internal_cache['find_file_paths'][$search]))
			return Kohana::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = Kohana::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config' OR $directory === 'messages' OR $directory === 'i18n')
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
				throw new Kohana_Exception('The requested :resource:, :file:, could not be found', array(':resource:' => Kohana::message($directory), ':file:' =>$filename));
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		if ( ! isset(Kohana::$write_cache['find_file_paths']))
		{
			// Write cache at shutdown
			Kohana::$write_cache['find_file_paths'] = TRUE;
		}

		return Kohana::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   list all files having extension $ext
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $ext = EXT, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(self::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, self::list_files($directory, $recursive, $ext, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path) AND $items = glob($path.'*'.$ext))
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
						$files = array_merge($files, self::list_files($directory, TRUE, $ext, $path.$item));
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
		$locale = Kohana::config('locale.language.0');

		if ( ! isset(Kohana::$internal_cache['messages'][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($file = Kohana::find_file('messages', $group))
			{
				include $file[0];
			}

			if ( ! isset(Kohana::$write_cache['messages']))
			{
				// Write language cache
				Kohana::$write_cache['messages'] = TRUE;
			}

			Kohana::$internal_cache['messages'][$group] = $messages;
		}

		// Get the line from cache
		$line = Kohana::key_string(Kohana::$internal_cache['messages'], $key);

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
						if (is_string($arg) AND strpos($arg, '://') === FALSE AND file_exists($arg))
						{
							// Sanitize path
							$arg = Kohana::debug_path($arg);
						}

						$args[] = '<code>'.text::limit_chars(html::specialchars(Kohana::debug_var($arg)), 50, '...').'</code>';
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
							$out .= Kohana::debug_var($property->getValue($var), TRUE);
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
						$key = Kohana::debug_var($key, TRUE).' => ';
					}
					else
					{
						$key = '';
					}
					$out .= ($more ? ', ' : '').$key.Kohana::debug_var($val, TRUE);
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
	 * Returns an HTML string, highlighting a specific line of a file, with some
	 * number of lines padded above and below.
	 *
	 *     // Highlights the current line of the current file
	 *     echo Kohana::debug_source(__FILE__, __LINE__);
	 *
	 * @param   string   file to open
	 * @param   integer  line number to highlight
	 * @param   integer  number of padding lines
	 * @return  string
	 */
	public static function debug_source($file, $line_number, $padding = 5)
	{
		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

		// Set the zero-padding amount for line numbers
		$format = '% '.strlen($range['end']).'d';

		$source = '';
		while (($row = fgets($file)) !== FALSE)
		{
			// Increment the line number
			if (++$line > $range['end'])
				break;

			if ($line >= $range['start'])
			{
				// Make the row safe for output
				$row = htmlspecialchars($row, ENT_NOQUOTES, Kohana::CHARSET);

				// Trim whitespace and sanitize the row
				$row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

				if ($line === $line_number)
				{
					// Apply highlighting to this row
					$row = '<span class="line highlight">'.$row.'</span>';
				}
				else
				{
					$row = '<span class="line">'.$row.'</span>';
				}

				// Add to the captured source
				$source .= $row;
			}
		}

		// Close the file
		fclose($file);

		return '<pre class="source"><code>'.$source.'</code></pre>';
	}
	
	/**
	 * Returns an array of HTML strings that represent each step in the backtrace.
	 *
	 *     // Displays the entire current backtrace
	 *     echo implode('<br/>', Kohana::trace());
	 *
	 * @param   string  path to debug
	 * @return  string
	 */
	public static function trace(array $trace = NULL)
	{
		if ($trace === NULL)
		{
			// Start a new trace
			$trace = debug_backtrace();
		}

		// Non-standard function calls
		$statements = array('include', 'include_once', 'require', 'require_once');

		$output = array();
		foreach ($trace as $step)
		{
			if ( ! isset($step['function']))
			{
				// Invalid trace step
				continue;
			}

			if (isset($step['file']) AND isset($step['line']))
			{
				// Include the source of this step
				$source = Kohana::debug_source($step['file'], $step['line']);
			}

			if (isset($step['file']))
			{
				$file = $step['file'];

				if (isset($step['line']))
				{
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if (in_array($step['function'], $statements))
			{
				if (empty($step['args']))
				{
					// No arguments
					$args = array();
				}
				else
				{
					// Sanitize the file path
					$args = array($step['args'][0]);
				}
			}
			elseif (isset($step['args']))
			{
				if (isset($step['class']))
				{
					if (method_exists($step['class'], $step['function']))
					{
						$reflection = new ReflectionMethod($step['class'], $step['function']);
					}
					else
					{
						$reflection = new ReflectionMethod($step['class'], '__call');
					}
				}
				else
				{
					$reflection = new ReflectionFunction($step['function']);
				}

				// Get the function parameters
				$params = $reflection->getParameters();

				$args = array();

				foreach ($step['args'] as $i => $arg)
				{
					if (isset($params[$i]))
					{
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					}
					else
					{
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if (isset($step['class']))
			{
				// Class->method() or Class::method()
				$function = $step['class'].$step['type'].$step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset($args)   ? $args : NULL,
				'file'     => isset($file)   ? $file : NULL,
				'line'     => isset($line)   ? $line : NULL,
				'source'   => isset($source) ? $source : NULL,
			);

			unset($function, $args, $file, $line, $source);
		}

		return $output;
	}


	/**
	 * Returns an HTML string of information about a single variable.
	 *
	 * Borrows heavily on concepts from the Debug class of {@link http://nettephp.com/ Nette}.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @return  string
	 */
	public static function dump($value, $length = 128)
	{
		return Kohana::_dump($value, $length);
	}

	/**
	 * Helper for Kohana::dump(), handles recursion in arrays and objects.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @param   integer  recursion level (internal)
	 * @return  string
	 */
	private static function _dump( & $var, $length = 128, $level = 0)
	{
		if ($var === NULL)
		{
			return '<small>NULL</small>';
		}
		elseif (is_bool($var))
		{
			return '<small>bool</small> '.($var ? 'TRUE' : 'FALSE');
		}
		elseif (is_float($var))
		{
			return '<small>float</small> '.$var;
		}
		elseif (is_resource($var))
		{
			if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
			{
				$meta = stream_get_meta_data($var);

				if (isset($meta['uri']))
				{
					$file = $meta['uri'];

					if (function_exists('stream_is_local'))
					{
						// Only exists on PHP >= 5.2.4
						if (stream_is_local($file))
						{
							$file = Kohana::debug_path($file);
						}
					}

					return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, Kohana::CHARSET);
				}
			}
			else
			{
				return '<small>resource</small><span>('.$type.')</span>';
			}
		}
		elseif (is_string($var))
		{
			if (strlen($var) > $length)
			{
				// Encode the truncated string
				$str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, Kohana::CHARSET).'&nbsp;&hellip;';
			}
			else
			{
				// Encode the string
				$str = htmlspecialchars($var, ENT_NOQUOTES, Kohana::CHARSET);
			}

			return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
		}
		elseif (is_array($var))
		{
			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			static $marker;

			if ($marker === NULL)
			{
				// Make a unique marker
				$marker = uniqid("\x00");
			}

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($var[$marker]))
			{
				$output[] = "(\n$space$s*RECURSION*\n$space)";
			}
			elseif ($level < 5)
			{
				$output[] = "<span>(";

				$var[$marker] = TRUE;
				foreach ($var as $key => & $val)
				{
					if ($key === $marker) continue;
					if ( ! is_int($key))
					{
						$key = '"'.$key.'"';
					}

					$output[] = "$space$s$key => ".Kohana::_dump($val, $length, $level + 1);
				}
				unset($var[$marker]);

				$output[] = "$space)</span>";
			}
			else
			{
				// Depth too great
				$output[] = "(\n$space$s...\n$space)";
			}

			return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
		}
		elseif (is_object($var))
		{
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			$hash = spl_object_hash($var);

			// Objects that are being dumped
			static $objects = array();

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($objects[$hash]))
			{
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			}
			elseif ($level < 5)
			{
				$output[] = "<code>{";

				$objects[$hash] = TRUE;
				foreach ($array as $key => & $val)
				{
					if ($key[0] === "\x00")
					{
						// Determine if the access is private or protected
						$access = '<small>'.($key[1] === '*' ? 'protected' : 'private').'</small>';

						// Remove the access level from the variable name
						$key = substr($key, strrpos($key, "\x00") + 1);
					}
					else
					{
						$access = '<small>public</small>';
					}

					$output[] = "$space$s$access $key => ".Kohana::_dump($val, $length, $level + 1);
				}
				unset($objects[$hash]);

				$output[] = "$space}</code>";
			}
			else
			{
				// Depth too great
				$output[] = "{\n$space$s...\n$space}";
			}

			return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
		}
		else
		{
			return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, Kohana::CHARSET);
		}
	}


	/**
	 * Saves the internal caches: configuration, include paths, etc.
	 *
	 * @return  boolean
	 */
	public static function internal_cache_save()
	{
		if ( ! is_array(Kohana::$write_cache))
			return FALSE;

		// Get internal cache names
		$caches = array_keys(Kohana::$write_cache);

		// Nothing written
		$written = FALSE;

		foreach ($caches as $cache)
		{
			if (isset(Kohana::$internal_cache[$cache]))
			{
				// Write the cache file
				Kohana::cache_save($cache, Kohana::$internal_cache[$cache], Kohana::config('core.internal_cache'));

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
	 * @var  array  PHP error code => human readable name
	 */
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
	);

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
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   object  Exception
	 * @return  string
	 */
	public static function text($e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Kohana::debug_path($e->getFile()), $e->getLine());
	}
	
	/**
	 * exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Kohana::$php_errors
	 * @uses    Kohana::exception_text()
	 * @param   object   exception object
	 * @return  boolean
	 */
	public static function handle(Exception $e)
	{
		// An error has been triggered
		Kohana::$has_error = TRUE;
		
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			// Create a text version of the exception
			$error = Kohana_Exception::text($e);

			// Add this exception to the log
			Kohana_Log::add('error', $error);

			if (PHP_SAPI === 'cli')
			{
				// Just display the text of the exception
				echo "\n{$error}\n";

				return TRUE;
			}

			if (Kohana::config('core.display_errors') === FALSE)
			{
				// Get the i18n messages
				$error   = __('Unable to Complete Request');
				$message = __('You can go to the <a href="%site%">home page</a> or <a href="%uri%">try again</a>.',
				                    array('%site%' => url::site(), '%uri%' => url::site(Router::$current_uri)));

				// Do not show the file or line
				$file = $line = NULL;

				require Kohana::find_file('views', 'kohana/error_disabled', TRUE);
			}
			else
			{
				// Get the exception backtrace
				$trace = $e->getTrace();
	
				if ($e instanceof ErrorException)
				{
					if (isset(self::$php_errors[$code]))
					{
						// Use the human-readable error name
						$code = self::$php_errors[$code];
					}
	
					if (version_compare(PHP_VERSION, '5.3', '<'))
					{
						// Workaround for a bug in ErrorException::getTrace() that exists in
						// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
						for ($i = count($trace) - 1; $i > 0; --$i)
						{
							if (isset($trace[$i - 1]['args']))
							{
								// Re-position the args
								$trace[$i]['args'] = $trace[$i - 1]['args'];
	
								// Remove the args
								unset($trace[$i - 1]['args']);
							}
						}
					}
				}
	
				if ( ! headers_sent())
				{
					// Make sure the proper content type is sent with a 500 status
					header('Content-Type: text/html; charset='.Kohana::CHARSET, TRUE, 500);
				}
	
				// Clean the output buffer if one exists
				ob_get_level() and ob_clean();
	
				// Include the exception HTML
				include Kohana::find_file('views', 'kohana/error');
	
				// Exit with an error status
				exit(1);
			}
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo Kohana_Exception::text($e), "\n";

			// Exit with an error status
			exit(1);
		}
	}

	/**
	 * Outputs an text error message.
	 *
	 * @return  string
	 */
	public function __toString() 
	{
		return Kohana_Exception::text($this);		
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

		// Throw an exception
		throw new Kohana_PHP_Exception($code, $error, $file, $line, $context);
		
		// Do not execute the PHP error handler
		return TRUE;
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

		parent::__construct(__('The page you requested, %page%, could not be found.', array('%page%' => $page)));
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

/**
 * Creates a fatal error exception
 */
class Kohana_Shutdown_Exception extends Kohana_Exception {
	public static $disabled = false;
	
	/**
	 * Enable Kohana Shudown error handling.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		register_shutdown_function(array(__CLASS__, 'handle'));
	}
	
	/**
	 * Disable Kohana Shudown error handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		self::$disabled = true;
	}
	
	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Kohana_Exception::handle()
	 * @return  void
	 */
	public static function handle()
	{
		if (self::$disabled === true)
		{
			//this will prevent any future exception handlers from running
			exit();
		}
		if ($error = error_get_last())
		{
			// If an output buffer exists, clear it
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			Kohana_Exception::handle(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}
}
