<?php
/**
 * This file acts as the "front controller" to your application. You can
 * configure your application, modules, and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

/**
 * Toggle for production status.
 */
define('IN_PRODUCTION', (bool) preg_match('/kohana(?:php|\.webfactional)\./', $_SERVER['SERVER_NAME']));

/**
 * Website application directory. This directory should contain your application
 * configuration, controllers, models, views, and other resources.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_application = IN_PRODUCTION ? '/home/kohana/checkout/kohana_website/application': 'application';

/**
 * Kohana modules directory. This directory should contain all the modules used
 * by your application. Modules are enabled and disabled by the application
 * configuration file.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_modules = IN_PRODUCTION ? '/home/kohana/checkout/kohana_website/modules' : 'modules';

/**
 * Kohana system directory. This directory should contain the core/ directory,
 * and the resources you included in your download of Kohana.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_system = IN_PRODUCTION ? '/home/kohana/checkout/kohana_website/system' : 'system';


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
// $Id: index.php 3366 2008-08-27 22:15:39Z Shadowhand $
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

if ( ! IN_PRODUCTION)
{
	// Check APPPATH
	if ( ! (is_dir(APPPATH) AND is_file(APPPATH.'config/config'.EXT)))
	{
		die
		(
			'<div style="width:80%;margin:50px auto;text-align:center;">'.
				'<h3>Application Directory Not Found</h3>'.
				'<p>The <code>$kohana_application</code> directory does not exist.</p>'.
				'<p>Set <code>$kohana_application</code> in <tt>'.KOHANA.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}

	// Check SYSPATH
	if ( ! (is_dir(SYSPATH) AND is_file(SYSPATH.'bootstrap'.EXT)))
	{
		die
		(
			'<div style="width:80%;margin:50px auto;text-align:center;">'.
				'<h3>System Directory Not Found</h3>'.
				'<p>The <code>$kohana_system</code> directory does not exist.</p>'.
				'<p>Set <code>$kohana_system</code> in <tt>'.KOHANA.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}
}

// Initialize Kohana
require SYSPATH.'bootstrap'.EXT;