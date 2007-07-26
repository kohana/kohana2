<?php

/**
 * Turn on full error reporting
 */
error_reporting(E_ALL); ini_set('display_errors', true);

/**
 * Application Path
 */
$application_path = 'application';

/**
 * System Path
 */
$system_path = 'system';

/**** END CONFIGURATION ** DO NOT EDIT BELOW ****/

// Define absolute paths
define('APPPATH', realpath($application_path).'/');
define('SYSPATH', realpath($system_path).'/');
// More definitions
define('DOCROOT', pathinfo(__FILE__, PATHINFO_DIRNAME).'/');
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));

// Check APPPATH
(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$application_path</code> does not exist. '.
	'Set a valid <code>$application_path</code> in <kbd>index.php</kbd> and refresh the page.'
);

// Check SYSPATH
(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/Bootstrap'.EXT)) or die
(
	'Your <code>$system_path</code> does not exist. '.
	'Set a valid <code>$system_path</code> in <kbd>index.php</kbd> and refresh the page.'
);

// Buckle those bootstraps!
require_once SYSPATH.'core/Bootstrap'.EXT;