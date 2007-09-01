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
| $Id$
*/
// Absolute path names for include purposes
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/'); unset($kohana_application);
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/'); unset($kohana_system);
// Information about the front controller
$docroot = str_replace('\\', '/', realpath(__FILE__));
define('KOHANA',  pathinfo($docroot, PATHINFO_BASENAME));
define('DOCROOT', pathinfo($docroot, PATHINFO_DIRNAME).'/');
define('EXT', '.'.pathinfo($docroot, PATHINFO_EXTENSION));
unset($docroot);
// Validate APPPATH
(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$application_path</code> does not exist. '.
	'Set a valid <code>$application_path</code> in <kbd>index.php</kbd> and refresh the page.'
);
// Validate SYSPATH
(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/'.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> does not exist. '.
	'Set a valid <code>$kohana_system</code> in <kbd>index.php</kbd> and refresh the page.'
);
// Buckle those bootstraps!
require_once SYSPATH.'core/Bootstrap'.EXT;