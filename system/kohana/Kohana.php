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
 * System Front Controller
 *
 * Loads the base classes and executes the request.
 *
 * @package		Kohana
 * @subpackage	Core
 * @category	Front-controller
 * @author		Rick Ellis
 */
// Kohana Version
define('KOHANA_VERSION', '1.0');
// Is this PHP5?
define('KOHANA_IS_PHP5', (floor(PHP_VERSION) >= 5));
ob_start();

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require(BASEPATH.'kohana/Common'.EXT);


/*
 * ------------------------------------------------------
 *  Register a shutdown function for catching more errors
 * ------------------------------------------------------
 */
register_shutdown_function('_shutdown_handler');

/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
set_error_handler('_exception_handler');
set_magic_quotes_runtime(0); // Kill magic quotes

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */
$BM =& load_class('Benchmark');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time_base_classes_start');


/*
 * ------------------------------------------------------
 *  Create SHA1 function for PHP <4.3
 * ------------------------------------------------------
 */
if ( ! function_exists('sha1'))
{
	function sha1($str)
	{
		$class =& load_class('SHA');
		return $class->generate($str);
	}
}

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */
$EXT =& load_class('Hooks');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the base classes
 * ------------------------------------------------------
 */

$CFG =& load_class('Config');
$RTR =& load_class('Router');
$OUT =& load_class('Output');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file?  If so, we're done...
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('cache_override') === FALSE)
{
	if ($OUT->_display_cache($CFG, $RTR) == TRUE)
	{
		exit;
	}
}

/*
 * ------------------------------------------------------
 *  Load the remaining base classes
 * ------------------------------------------------------
 */

$IN		=& load_class('Input');
$URI	=& load_class('URI');
$LANG	=& load_class('Language');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 *  Note: Due to the poor object handling in PHP 4 we'll
 *  conditionally load different versions of the base
 *  class.  Retaining PHP 4 compatibility requires a bit of a hack.
 *
 *  Note: The Loader class needs to be included first
 *
 */
if (KOHANA_IS_PHP5)
{
	require(BASEPATH.'kohana/Base5'.EXT);
}
else
{
	load_class('Loader');
	require(BASEPATH.'kohana/Base4'.EXT);
}

// Load the base controller class
load_class('Controller', FALSE);

// Load the local application controller
// Note: The Router class automatically validates the controller path.  If this include fails it
// means that the default controller in the Routes.php file is not resolving to something valid.
if ( ! include(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().EXT))
{
	show_error('Unable to load your default controller.  Please make sure the controller specified in your Routes.php file is valid.');
}

// Set a mark point for benchmarking
$BM->mark('loading_time_base_classes_end');


/*
 * ------------------------------------------------------
 *  Security check
 * ------------------------------------------------------
 *
 *  None of the functions in the app controller or the
 *  loader class can be called via the URI, nor can
 *  controller functions that begin with an underscore
 */
$class  = $RTR->fetch_class();
$method = $RTR->fetch_method();


if ( ! class_exists($class)
	OR $method == 'controller'
	OR substr($method, 0, 1) == '_'
	OR in_array($method, get_class_methods('Controller'), TRUE)
	)
{
	show_404();
}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the controller and call requested method
 * ------------------------------------------------------
 */

// Mark a start point so we can benchmark the controller
$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

// Instantiate the Controller
$CORE = new $class();

// Is this a scaffolding request?
if ($RTR->scaffolding_request === TRUE)
{
	if ($EXT->_call_hook('scaffolding_override') === FALSE)
	{
		$CORE->_ci_scaffolding();
	}
}
else
{
	/*
	 * ------------------------------------------------------
	 *  Is there a "post_controller_constructor" hook?
	 * ------------------------------------------------------
	 */
	$EXT->_call_hook('post_controller_constructor');

	// Is there a "remap" function?
	if (method_exists($CORE, '_remap'))
	{
		$CORE->_remap($method, array_slice($RTR->rsegments, (($RTR->fetch_directory() == '') ? 1 : 2)));
	}
	else
	{
		// Any URI segments present (besides the class/function) will be passed to the method for convenience
		$args = array_slice($RTR->rsegments, (($RTR->fetch_directory() == '') ? 2 : 3));
		
		if ( ! method_exists($CORE, $method))
		{
			// Attempt to call the "default" method instead of showing a 404
			if (method_exists($CORE, '_default'))
			{
				$CORE->_default($method, $args);
			}
			else
			{
				show_404();
			}
		}
		else
		{
			// Call the requested method
			call_user_func_array(array(&$CORE, $method), $args);
		}
	}
}

// Mark a benchmark end point
$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('display_override') === FALSE)
{
	$OUT->_display();
}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
if (class_exists('Core_DB') AND isset($CORE->db))
{
	$CORE->db->close();
}

?>