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
 * Set the error reporting level. By default, Kohana will ignore E_NOTICE
 * messages. Unless you have a special need, E_ALL is a good level for
 * error reporting.
 */
error_reporting(E_ALL);

/**
 * Enable or disable error display. During development, it is very helpful
 * to display errors. However, errors can give away information about your
 * application. For greater security, it is recommended that you disable
 * the displaying of errors in production.
 */
ini_set('display_errors', TRUE);

/**
 * Kohana website application directory. This directory should contain your
 * application configuration, controllers, models, views, and other resources.
 */
$kohana_application = 'application';

/**
 * Kohana package files. This directory should contain the core/ directory, and
 * the resources you included in your download of Kohana.
 */
$kohana_system = 'system';

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file is has a
 * different extension.
 */
define('EXT', '.php');

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND WHAT YOU ARE CHANGING.
// ----------------------------------------------------------------------------
// $Id$
//

// Find the docroot path information
$docroot = pathinfo(str_replace('\\', '/', realpath(__FILE__)));

// Define the front controller name and docroot
define('KOHANA',  $docroot['basename']);
define('DOCROOT', $docroot['dirname'].'/');

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

// Clean up
unset($docroot, $kohana_application, $kohana_system);

(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$kohana_application</code> directory does not exist. '.
	'Set a valid <code>$kohana_application</code> in <tt>index.php</tt> and refresh the page.'
);

(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/'.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> directory does not exist. '.
	'Set a valid <code>$kohana_system</code> in <tt>index.php</tt> and refresh the page.'
);

require SYSPATH.'core/Bootstrap'.EXT;