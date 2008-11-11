<?php
/**
 * This file acts as the "front controller" to your application. You can
 * configure your application, modules, and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see http://kohanaphp.com
 */

/**
 * Define the website environment status. When this flag is set to TRUE, some
 * module demonstration controllers will result in 404 errors. For more information
 * about this option, read the documentation about deploying Kohana.
 *
 * @see http://docs.kohanaphp.com/installation/deployment
 */
define('IN_PRODUCTION', FALSE);

/**
 * Website application directory. This directory should contain your application
 * configuration, controllers, models, views, and other resources.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_application = 'application';

/**
 * Kohana modules directory. This directory should contain all the modules used
 * by your application. Modules are enabled and disabled by the application
 * configuration file.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_modules = 'modules';

/**
 * Kohana system directory. This directory should contain the core/ directory,
 * and the resources you included in your download of Kohana.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_system = 'system';

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Turning off display_errors will effectively disable Kohana error display
 * and logging. You can turn off Kohana errors in application/config/config.php
 */
ini_set('display_errors', TRUE);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id$
//

// Define the front controller name and docroot
define('DOCROOT', getcwd().DIRECTORY_SEPARATOR);
define('KOHANA',  basename(__FILE__));

// If the front controller is a symlink, change to the real docroot
is_link(KOHANA) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('MODPATH', str_replace('\\', '/', realpath($kohana_modules)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

// Clean up
unset($kohana_application, $kohana_modules, $kohana_system);

if (file_exists('install'.EXT))
{
	// Load the installation tests
	include 'install'.EXT;
}
else
{
	// Initialize Kohana
	require SYSPATH.'bootstrap'.EXT;
}
