<?php
/*
| -----------------------------------------------------------------------------
| Kohana - The Swift PHP5 Framework
| -----------------------------------------------------------------------------
| This file acts as the "front controller" to your application. If you do not
| understand the configuration parameters below, please consult the Kohana
| User Guide for more information.
| -----------------------------------------------------------------------------
| User Guide: http://kohanaphp.com/user_guide/kohana/installation.html
| -----------------------------------------------------------------------------
| $Id$
*/

// Set the error reporting level
@error_reporting(E_ALL);

// Enable or disable error reporting
@ini_set('display_errors', TRUE);

// Kohana application directory
$kohana_application = 'application';

// Kohana system directory
$kohana_system = 'system';

/*
| -----------------------------------------------------------------------------
| PLEASE DO NOT EDIT BELOW THIS LINE, unless you understand the repercussions!
| -----------------------------------------------------------------------------
| User Guide: http://kohanaphp.com/user_guide/general/bootstrapping.html
| -----------------------------------------------------------------------------
*/
// Absolute path names for include purposes
define('APPPATH', realpath($kohana_application).DIRECTORY_SEPARATOR); unset($kohana_application);
define('SYSPATH', realpath($kohana_system).DIRECTORY_SEPARATOR); unset($kohana_system);
// Information about the front controller
define('KOHANA',  pathinfo(__FILE__, PATHINFO_BASENAME));
define('DOCROOT', pathinfo(__FILE__, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR);
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
// Validate APPPATH
(is_dir(APPPATH) AND is_dir(APPPATH.DIRECTORY_SEPARATOR.'config')) or die
(
	'Your <code>$application_path</code> does not exist. '.
	'Set a valid <code>$application_path</code> in <kbd>index.php</kbd> and refresh the page.'
);
// Validate SYSPATH
(is_dir(SYSPATH) AND file_exists(SYSPATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> does not exist. '.
	'Set a valid <code>$kohana_system</code> in <kbd>index.php</kbd> and refresh the page.'
);
// Buckle those bootstraps!
require_once SYSPATH.'core'.DIRECTORY_SEPARATOR.'Bootstrap'.EXT;