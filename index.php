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
 * @see http://doc.kohanaphp.com/installation/deployment
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
 * Test to make sure that Kohana is running on PHP 5.2 or newer. Once you are
 * sure that your environment is compatible with Kohana, you can comment this
 * line out. When running an application on a new server, uncomment this line
 * to check the PHP version quickly.
 */
version_compare(PHP_VERSION, '5.2', '<') and exit('Kohana requires PHP 5.2 or newer.');

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
 * Define the Kohana-generated error message style.
 */
define('KOHANA_ERROR_CSS',
	// Any of these styles can be changed to affect all error messages.
	'div#kohana_error { background: #fff; border:solid 1px #ccc; font-family:sans-serif; color:#111; font-size: 14px; line-height: 130%; vertical-align: baseline; }'.
	'div#kohana_error h3 { color:#fff; font-size:16px; padding:8px 6px; margin:0 0 8px; background: #f15a00; text-align: center; }'.
	'div#kohana_error a { color:#228; text-decoration:none; }'.
	'div#kohana_error a:hover { text-decoration:underline; }'.
	'div#kohana_error strong { color:#900; }'.
	'div#kohana_error p { margin:0; padding:4px 6px 10px; }'.
	'div#kohana_error tt,'.
	'div#kohana_error pre,'.
	'div#kohana_error code { font-family:monospace; padding:2px 4px; white-space:pre; font-size:12px; color:#333; }'.
	'div#kohana_error tt { font-style:italic; }'.
	'div#kohana_error tt:before { content:">"; color:#aaa; }'.
	'div#kohana_error code tt:before { content:""; }'.
	'div#kohana_error pre,'.
	'div#kohana_error code { background:#eaeee5; border:solid 0 #D6D8D1; border-width:0 1px 1px 0; }'.
	'div#kohana_error .block { display:block; text-align:left; }'.
	'div#kohana_error .stats { padding: 4px; background: #eee; border-top:solid 1px #ccc; text-align:center; font-size:10px; color: #888; }'.
	'div#kohana_error .backtrace { margin:0; padding:0 6px; list-style:none; line-height:12px; }'
);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file is has a
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

if ( ! IN_PRODUCTION)
{
	// Check APPPATH
	if ( ! (is_dir(APPPATH) AND file_exists(APPPATH.'/config/config'.EXT)))
	{
		die
		(
			'<style type="text/css">'.KOHANA_ERROR_CSS.'</style>'.
			'<div id="kohana_error" style="width:26em;margin:50px auto;text-align:center;">'.
				'<h3>Application Directory Not Found</h3>'.
				'<p>The <code>$kohana_application</code> directory does not exist.</p>'.
				'<p>Set <code>$kohana_application</code> in <tt>'.KOHANA.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}

	// Check SYSPATH
	if ( ! (is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/Bootstrap'.EXT)))
	{
		die
		(
			'<style type="text/css">'.KOHANA_ERROR_CSS.'</style>'.
			'<div id="kohana_error" style="width:26em;margin:50px auto;text-align:center;">'.
				'<h3>System Directory Not Found</h3>'.
				'<p>The <code>$kohana_system</code> directory does not exist.</p>'.
				'<p>Set <code>$kohana_system</code> in <tt>'.KOHANA.'</tt> to a valid directory and refresh the page.</p>'.
			'</div>'
		);
	}
}

// Initialize.
require SYSPATH.'core/Bootstrap'.EXT;