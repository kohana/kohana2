<?php
/**
 * This file acts as the "front controller" to your application. You can
 * configure your application and system directories here, as well as error
 * reporting and error display.
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

/**
 * Kohana website application directory. This directory should contain your
 * application configuration, controllers, models, views, and other resources.
 *
 * This path can be absolute or relative to this file.
 */
$kohana_application = 'application';

/**
 * Kohana package files. This directory should contain the core/ directory, and
 * the resources you included in your download of Kohana.
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
define('KOHANA',  substr(__FILE__, strlen(DOCROOT)));

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

// Clean up
unset($kohana_application, $kohana_system);

(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$kohana_application</code> directory does not exist. '.
	'Set a valid <code>$kohana_application</code> in <tt>'.KOHANA.'</tt> and refresh the page.'
);

(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/'.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> directory does not exist. '.
	'Set a valid <code>$kohana_system</code> in <tt>'.KOHANA.'</tt> and refresh the page.'
);

require SYSPATH.'core/Bootstrap'.EXT;