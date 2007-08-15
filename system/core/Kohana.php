<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework.
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
final class Kohana {

	public static $registry = array(); // Library registery
	public static $instance = FALSE;   // Controller instance

	public static $buffer_level = 0;       // Ouput buffering level
	public static $error_types  = array(); // Human readable error types

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
	public static function initialize()
	{
		// The controller can only be loaded once
		if (is_object(self::$instance)) return;

		$class = Router::$controller;

		require Router::$directory.Router::$controller.EXT;

		if ( ! class_exists($class))
		{
			throw new controller_not_found($class);
		}

		self::$instance = new $class;
		self::$instance->load = new Loader();

		// Run autoloader
		self::$instance->load->autoload();

		/**
		 * @todo This needs to check for _remap and _default, as well as validating that method exists
		 */
		call_user_func_array(array(self::$instance, Router::$method), Router::$arguments);
	}

	/**
	 * Output Handler
	 *
	 * @access public
	 * @return string
	 */
	public static function output($output)
	{
		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		return str_replace(
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
	}

	/**
	 * Error Handler
	 *
	 * @access public
	 * @return void
	 */
	public static function error_handler($error, $message, $file, $line)
	{
		$error = isset(self::$error_types[$error]) ? self::$error_types[$error] : 'Unknown Error';
		$file  = preg_replace('#^'.preg_quote(DOCROOT, '-').'#', '', $file);
		$template = self::find_file('errors', 'php_error');

		while(ob_get_level() > self::$buffer_level)
		{
			ob_end_clean();
		}

		include $template;
		exit;
	}

	/**
	 * Exception Handler
	 *
	 * @access public
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
	 * @return void
	 */
	public static function auto_load($class)
	{
		try
		{
			$class = preg_replace('/^Core_/', '', $class);

			require self::find_file('libraries', $class, TRUE);

			if ($extension = self::find_file('libraries', Config::item('subclass_prefix').$class))
			{
				require $extension;
			}
			else
			{
				eval('class '.$class.' extends Core_'.$class.' { }');
			}

		}
		catch (file_not_found $exception)
		{
			print $exception->getMessage().' Library could not be loaded.';
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
	 * @return object
	 */
	public static function load_class($class)
	{
		$class = preg_replace('/^Core_/', '', $class);

		if (isset(self::$registry[$class]))
		{
			return self::$registry[$class];
		}

		self::auto_load($class);

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
	 * @return mixed
	 */
	public static function find_file($directory, $filename, $required = FALSE)
	{
		static $found = array();

		$search = $directory.'/'.$filename;

		if (isset($found[$search]))
		{
			return $found[$search];
		}

		$paths = Config::item('include_paths');

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
	 * @return void
	 */
	public static function load_hook($name)
	{
		if (Config::item('enable_hooks') AND $hook = self::findFile('hooks', $name))
		{
			require $hook;
		}
	}

	/**
	 * HTML Attribute Parser
	 *
	 * @access public
	 * @return string
	 */
	public static function attributes($attrs)
	{
		if (is_string($attrs))
		{
			return ($attrs == FALSE) ? '' : ' '.$attrs;
		}
		else
		{
			$compiled = '';

			foreach($attrs as $key => $val)
			{
				$compiled .= ' '.$key.'="'.$val.'"';
			}

			return $compiled;
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