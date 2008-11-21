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

	// Kohana singleton instance
	private static $instance;

	// Searchable include paths
	private static $include_paths = array(APPPATH, SYSPATH);

	// Current locale setting
	private static $locale = 'en_US';

	// Current timezone setting
	private static $timezone = '';

	// The current user agent
	private static $user_agent;

	/**
	 * Enables auto-loading of classes that are available within the application,
	 * modules, or system paths.
	 *
	 * @param   string   class name
	 * @return  boolean
	 */
	public static function auto_load($class)
	{
		if (class_exists($class, FALSE))
		{
			// The class is already loaded
			return TRUE;
		}

		// Determine class filename
		$filename = str_replace('_', '/', strtolower($class));

		if ($path = Kohana::find_file('classes', $filename))
		{
			// Include the class file
			require $path;
		}
		else
		{
			// The requested class cannot be found
			return FALSE;
		}

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
	 * Default PHP exception handler, renders exceptions for HTML or CLI.
	 *
	 * @param   object  Exception instance
	 * @return  TRUE
	 */
	public static function exception_handler(Exception $e)
	{
		// Get the exception properties
		$code  = $e->getCode();
		$error = $e->getMessage();
		$file  = $e->getFile();
		$line  = $e->getLine();
		$trace = $e->getTraceAsString();

		if (is_int($code) AND is_array($lang = Kohana::lang('errors.'.$code)))
		{
			// Get the PHP error name and level
			$level = $lang[0];
			$name  = $lang[1];
		}
		else
		{
			// Custom error
			$level = 5;
			$name  = $code;
		}

		if (PHP_SAPI === 'cli')
		{
			// Display the plaintext error
			echo Kohana::lang('errors.cli_error', $name, self::debug_path($file), $line, $error, $trace), "\n";
		}
		else
		{
			// Display the error page
			echo Kohana::lang('errors.cli_error', $name, self::debug_path($file), $line, $error, $trace), "\n";
		}

		return TRUE;
	}

	/**
	 * Default PHP error handler, converts all errors into ErrorExceptions.
	 *
	 * @throws   ErrorException
	 * @return   TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if ((error_reporting() & $code) === 0)
		{
			// Respect error_reporting settings and ignore this error
			return TRUE;
		}

		// Convert all errors into exceptions
		throw new ErrorException($error, $code, 0, $file, $line);
	}

	/**
	 * Acts as a getter and setter for the current locale.
	 *
	 * @param   array   new locales
	 * @return  string
	 */
	public static function locale($languages = NULL)
	{
		if (is_array($languages))
		{
			// Set locale information
			$locale = setlocale(LC_ALL, $languages);

			// Set the xx_XX locale name
			self::$locale = substr($languages[0], 0, 5);
		}

		return self::$locale;
	}

	/**
	 * Acts as a getter and setting for the current timezone.
	 *
	 * @param   string  new timezone
	 * @return  string
	 */
	public static function timezone($zone = NULL)
	{
		if (is_string($zone))
		{
			if ($zone === '')
			{
				// Disable strict errors when calling date_default_timezone_get,
				// which will trigger an E_STRICT if it has to guess the zone
				$ER = error_reporting(~E_STRICT);

				$zone = date_default_timezone_get();

				// Restore error reporting
				error_reporting($ER);
			}

			// Set the default PHP timezone
			date_default_timezone_set(self::$timezone = $zone);
		}

		return self::$timezone;
	}

	/**
	 * Set module paths. Every path must be absolute or relative to the DOCROOT.
	 *
	 * @param   array  module paths
	 * @return  void
	 */
	public static function modules(array $paths)
	{
		// Start with the application path
		$include_paths = array(APPPATH);

		foreach ($paths as $path)
		{
			if ($path = realpath($path))
			{
				if ( ! is_dir($path))
				{
					// All modules must be directories
					continue;
				}

				if (KOHANA_IS_WIN)
				{
					// Convert backslashes to forward slashes
					$path = str_replace('\\', '/', $path);
				}

				// Add the module path to the include paths
				$include_paths[] = $path.'/';
			}
		}

		// Add the system path to the end
		$include_paths[] = SYSPATH;

		// Set the new include paths
		self::$include_paths = $include_paths;
	}

	/**
	 * Get an i18n string from static language files.
	 *
	 * @param   string   key.string
	 * @param   mixed    values to insert into strings (...)
	 * @return  mixed
	 */
	public static function lang($key, $args = NULL)
	{
		if (strpos($key, '.') === FALSE)
		{
			// Only the filename is present
			$file = $key;
			$keys = NULL;
		}
		else
		{
			// Separate the filename and keys
			list($file, $keys) = explode('.', $key, 2);
		}

		if ( ! is_array($files = self::find_file('i18n', self::$locale.'/'.$file)))
		{
			// The requested file does not exist
			return $key;
		}

		// Language message strings
		$messages = array();

		foreach ($files as $file)
		{
			// Load the language file
			include $file;

			foreach ($lang as $k => $v)
			{
				// Do this manually to prevent keys from being re-indexed by array_merge
				$messages[$k] = $v;
			}
		}

		if ($keys === NULL)
		{
			// Return the entire file
			return $messages;
		}
		elseif ($line = Kohana::key_string($messages, $keys))
		{
			if (func_num_args() > 1)
			{
				if ( ! is_array($args))
				{
					// Get all the arguments
					$args = func_get_args();
					$args = array_slice($args, 1);
				}

				// Place the arguments into the line
				$line = vsprintf($line, $args);
			}

			return $line;
		}
		else
		{
			// The requested key does not exist in the file
			return $key;
		}
	}

	/**
	 * Locate a file by directory, filepath, and extension. If the extension
	 * is not specified, the default extension will be used.
	 *
	 * @param   string   top-level directory
	 * @param   string   file path
	 * @param   string   non-default extension
	 * @return  mixed
	 */
	public static function find_file($directory, $filepath, $extension = NULL)
	{
		// Combine the directory, file path, and extension
		$file = $directory.'/'.$filepath.(($extension === NULL) ? EXT : '.'.$extension);

		if ($directory === 'i18n' OR $directory === 'config')
		{
			// Get the include paths in reverse
			$include_paths = array_reverse(self::$include_paths);

			foreach ($include_paths as $path)
			{
				if (is_file($path.$file))
				{
					// A file has been found, add it
					$files[] = $path.$file;
				}
			}

			if (isset($files))
			{
				// Files were found, return them
				return $files;
			}
		}
		else
		{
			foreach (self::$include_paths as $path)
			{
				if (is_file($path.$file))
				{
					// A file has been found, return it
					return $path.$file;
				}
			}
		}

		// No file could be found
		return FALSE;
	}

	/**
	 * Lists all files in a given directory.
	 *
	 * @param   string   directory to search
	 * @param   boolean  enable recursion
	 * @return  array    resolved filename paths
	 */
	public static function list_files($directory, $recursive = FALSE)
	{
		$files = array();
		$paths = array_reverse(self::$include_paths);

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
						$files[$directory.'/'.$filename] = realpath($file->getPathname());
					}
				}
			}
		}

		return $files;
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
		if (self::$user_agent === NULL)
		{
			// Disable notices while finding the user agent to prevent
			// "undefined variable" E_NOTICEs
			$ER = error_reporting(~E_NOTICE);

			if (PHP_SAPI === 'cli')
			{
				self::$user_agent['agent'] = basename($_SERVER['SHELL']).' ('.$_SERVER['TERM_PROGRAM'].')';
			}
			else
			{
				self::$user_agent['agent'] = $_SERVER['HTTP_USER_AGENT'];
			}

			// Parse the user agent and extract basic information
			$agents = Kohana_Config::get('user_agents', array());

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos(self::$user_agent['agent'], $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', self::$user_agent['agent'], $match))
						{
							// Set the browser version
							self::$user_agent['version'] = $match[1];
						}

						// Set the agent name
						self::$user_agent[$type] = $name;
						break;
					}
				}
			}

			// Restore error reporting
			error_reporting($ER);
		}

		if ( ! isset(self::$user_agent[$key]))
		{
			switch ($key)
			{
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$result = ! empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$result = array();
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
					$result = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
					{
						if (preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches))
						{
							// Found a result
							$result = $matches[0];
						}
					}
				break;
				case 'referrer':
					if ( ! empty($_SERVER['HTTP_REFERER']))
					{
						// Found a result
						$result = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			if (isset($result))
			{
				// Cache the result
				self::$user_agent[$key] = $result;
			}
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
		return isset(self::$user_agent[$key]) ? self::$user_agent[$key] : NULL;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 * If the value is not found, NULL is returned.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @param   mixed   default value to return if the key is not found
	 * @return  mixed
	 */
	public static function key_string(array $array, $keys, $default = NULL)
	{
		if (empty($array))
			return $default;

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

		return $default;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set(array & $array, $keys, $fill = NULL)
	{
		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create a reference to the array
		$row =& $array;

		for ($i = 0, $max = count($keys); $i < $max; $i++)
		{
			$key = $keys[$i];

			if ($i + 1 === $max)
			{
				// Fill
				$row[$key] = $fill;
			}
			else
			{
				if ( ! isset($row[$key]))
				{
					// Make the next row an array
					$row[$key] = array();
				}

				$row =& $row[$key];
			}
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
	 * Creates a new singleton instance of Kohana:
	 * - Enable modules
	 * - Set [PHP locale][ref-loc]
	 * - Set [PHP timezone][ref-tim]
	 * - Enable [auto-loader][ref-aut]
	 * - Enable [exception handler][ref-exc]
	 * - Enable [error handler][ref-err], [converts][ref-erc] errors to exceptions
	 * - Add Kohana_Request::instance to [system.execute][ref-evt]
	 * - Load [hooks][ref-hok]
	 *
	 * [ref-loc]: http://php.net/setlocale
	 * [ref-tim]: http://php.net/date_default_timezone_set
	 * [ref-aut]: http://php.net/spl_autoload_register
	 * [ref-exc]: http://php.net/set_execption_handler
	 * [ref-err]: http://php.net/set_error_handler
	 * [ref-erc]: http://php.net/manual/class.errorexception.php
	 * [ref-evt]: http://docs.kohanaphp.com/events
	 * [ref-hok]: http://docs.kohanaphp.com/hooks
	 */
	public function __construct()
	{
		if (self::$instance === NULL)
		{
			Benchmark::start('system.kohana_loading');

			// Load configuration
			require APPPATH.'config/kohana'.EXT;

			// Set the default modules
			Kohana::modules($config['modules']);

			// Set the default locale
			Kohana::locale($config['locale']);

			// Set the default timezone
			Kohana::timezone($config['timezone']);

			// Enable the Kohana auto-loader
			spl_autoload_register(array(__CLASS__, 'auto_load'));

			// Enable the exception handler
			set_exception_handler(array(__CLASS__, 'exception_handler'));

			// Enable the error handler
			set_error_handler(array(__CLASS__, 'error_handler'));

			// Add request processing to system.execute
			Event::add('system.execute', array('Kohana_Request', 'instance'));

			// Add request output preparation to system.display
			Event::add('system.display', array('Kohana_Request', 'display'));

			if ($config['enable_hooks'] === TRUE)
			{
				// Find all of the hook files
				$files = Kohana::list_files('hooks', TRUE);

				foreach ($files as $hook)
				{
					// Include the hook
					include $hook;
				}
			}

			// Enable the singleton
			self::$instance = $this;

			Benchmark::stop('system.kohana_loading');
		}
	}

} // End Kohana
