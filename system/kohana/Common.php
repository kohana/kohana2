<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package		Kohana
 * @subpackage	Core
 * @category	Common Functions
 * @author		Rick Ellis, Kohana Team
 */

// ------------------------------------------------------------------------

/**
* Class registry
*
* This function acts as a singleton.  If the requested class does not
* exist it is instantiated and set to a static variable.  If it has
* previously been instantiated the variable is returned.
*
* @access	public
* @param	string	the class name being requested
* @param	bool	optional flag that lets classes get loaded but not instantiated
* @return	object
*/
function &load_class($class, $instantiate = TRUE)
{
	static $objects = array();

	// Does the class exist?  If so, we're done...
	if (isset($objects[$class]))
		return $objects[$class];


	// If the requested class does not exist in the /libraries folders of any
	// extension folder we'll load the native class from the system/libraries folder.
	if (($abs_resource_path = find_resource(config_item('subclass_prefix').$class.EXT,'libraries',array(BASEPATH))) !== FALSE)
	{
		require(BASEPATH.'libraries/'.$class.EXT);
		require($abs_resource_path);
		$is_subclass = TRUE;
	}
	elseif (($abs_resource_path = find_resource($class.EXT,'libraries')) !== FALSE)
	{
		require($abs_resource_path);
		$is_subclass = FALSE;

		// We do this to allow the transparent extension of classes
		eval('class '.$class.' extends Core_'.$class.' {}');
	}
	//could not find class anywhere in search paths--error
	else
	{
		show_error("Initial core load failed for class: $class");
	}

	if ($instantiate == FALSE)
	{
		$objects[$class] = TRUE;
		return $objects[$class];
	}

	$objects[$class] =& new $class();
	return $objects[$class];
}

/**
* Loads the main config.php file
*
* @access	private
* @return	array
*/
function &get_config()
{
	static $main_conf;

	if ( ! isset($main_conf))
	{
		if ( ! file_exists(APPPATH.'config/config'.EXT))
		{
			exit('The configuration file config'.EXT.' does not exist.');
		}

		require(APPPATH.'config/config'.EXT);

		if ( ! isset($config) OR ! is_array($config))
		{
			exit('Your config file does not appear to be formatted correctly.');
		}

		$main_conf[0] =& $config;
	}
	return $main_conf[0];
}

/**
* Gets a config item
*
* @access	public
* @return	mixed
*/
function config_item($item)
{
	static $config_item = array();

	if ( ! isset($config_item[$item]))
	{
		$config =& get_config();

		if ( ! isset($config[$item]))
		{
			return FALSE;
		}
		$config_item[$item] = $config[$item];
	}

	return $config_item[$item];
}


/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
function show_error($message, $header = 'An Error Was Encountered')
{
	$error =& load_class('Exceptions');
	$error->show_error($header, $message);
	exit;
}


/**
* 404 Page Handler
*
* This function is similar to the show_error() function above
* However, instead of the standard error template it displays
* 404 errors.
*
* @access	public
* @return	void
*/
function show_404($page = '')
{
	$error =& load_class('Exceptions');
	$error->show_404($page);
	exit;
}


/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
function log_message($level = 'error', $message, $php_error = FALSE)
{
	static $LOG;

	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$LOG =& load_class('Log');
	$LOG->write_log($level, $message, $php_error);
}

/**
* Exception Handler
*
* This is the custom exception handler that is declared at the top
* of Kohana.php.  The main reason we use this is permit
* PHP errors to be logged in our own log files since we may
* not have access to server logs. Since this function
* effectively intercepts PHP errors, however, we also need
* to display errors based on the current error_reporting level.
* We do that with the use of a PHP error template.
*
* @access	private
* @return	void
*/
function _exception_handler($severity, $message, $filepath, $line)
{
	// We don't bother with "strict" notices since they will fill up
	// the log file with information that isn't normally very
	// helpful.  For example, if you are running PHP 5 and you
	// use version 4 style class functions (without prefixes
	// like "public", "private", etc.) you'll get notices telling
	// you that these have been deprecated.

	if ($severity == E_STRICT)
		return;

	$error =& load_class('Exceptions');

	// Should we display the error?
	// We'll get the current error_reporting level and add its bits
	// with the severity bits to find out.

	if (($severity & error_reporting()) == $severity)
	{
		$error->show_php_error($severity, $message, $filepath, $line);
	}

	// Should we log the error?  No?  We're done...
	if (config_item('log_threshold') > 0)
	{
		$error->log_exception($severity, $message, $filepath, $line);
	}
}

/**
* Shutdown Handler
*
* This is the custom shutdown function. It allows us to register multiple
* shutdown events with add_shutdown_event(), and call them all when exiting
*
* @access	private
* @return	void
*/
function _shutdown_handler()
{
	if (function_exists('get_shutdown_events'))
	{
		foreach(get_shutdown_events() as $event)
		{
			if (is_array($event) AND is_array($event[0]))
			{
				call_user_func_array($event[0], $event[1]);
			}
			else
			{
				call_user_func($event);
			}
		}
	}
}


/**
* Set Include Paths
*
* Build an array of absolute paths where the first entry is the local
* application directory (APPPATH), the next entries are validated entries
* from the 'include_paths' entry in main config, and the last entry is
* the system directory (BASEPATH)
*
* @access	public
* @return	array
*/
function set_include_paths()
{
	$include_paths = array(APPPATH);
	$conf_include_paths = config_item('include_paths');
	if (is_array($conf_include_paths) AND count($conf_include_paths)>0)
	{
		foreach ($conf_include_paths as $path)
		{
			$path = (substr($path,0,1)=='/' OR substr($path,1,1)==':')
			      ? $path
			      : realpath(dirname(SELF)).'/'.$path;
			if (($path=realpath($path))!==FALSE AND is_dir($path))
			{
				$include_paths[] = $path.'/';
			}
		}
	}
	$include_paths[] = BASEPATH;

	return $include_paths;
}
/**
 * Find Resource
 *
 * Takes a filename, resource subdirectory (libraries, helpers, plugins, etc),
 * and optional list of paths to exclude in the search and returns absolute
 * path to first matching filename in search path hierarchy or BOOL FALSE if
 * not found
 *
 * @access   public
 * @param    string
 * @param    string
 * @param    array
 * @return   mixed
 */
function find_resource($resource_file,$resource_subdir,$exclude_in_search=array())
{
	global $IPATHS;
	$return_val = FALSE;
	foreach ($IPATHS as $path)
	{
		if (is_file($path.$resource_subdir.'/'.$resource_file))
		{
			if (is_array($exclude_in_search) AND in_array($path,$exclude_in_search))
				continue;

			$return_val = $path.$resource_subdir.'/'.$resource_file;
			break;
		}
	}
	return $return_val;
}

/**
 * Verify Directory Exists in Include Path
 *
 * Takes a directory name, resource sub-directory name (libraries, helpers, plugins, etc),
 * and optional list of include paths to exclude in the search and returns absolute path to
 * first matching filename in search path hierarchy or BOOL FALSE if not found
 *
 * @access   public
 * @param    string
 * @param    string
 * @param    array
 * @return   mixed
 */
function verify_include_dir($dir_name,$resource_subdir,$exclude_in_search=array())
{
	global $IPATHS;
	$return_val = FALSE;
	foreach ($IPATHS as $path)
	{
		if ((is_array($exclude_in_search) && !in_array($path,$exclude_in_search)) && is_dir($path.$resource_subdir.'/'.$dir_name))
		{
			$return_val = $path.$resource_subdir.'/'.$dir_name;
			break;
		}
	}
	return $return_val;
}
?>