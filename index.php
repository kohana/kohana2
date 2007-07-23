<?php

// Turn on full error reporting
@error_reporting('E_ALL');

// Turn on error display
@ini_set('display_errors', TRUE);

// Application Path
$application_path = 'application';

// System Path
$system_path = 'system';

// Define absolute paths
define('APPPATH', realpath($application_path).'/');
define('SYSPATH', realpath($system_path).'/');
// More definitions
define('DOCROOT', pathinfo(__FILE__, PATHINFO_DIRNAME).'/');
define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
define('IS_CLI', isset($argv));

// Check APPPATH
(is_dir(APPPATH) AND is_dir(APPPATH.'/configs')) or die
(
	'Your <code>$application_path</code> does not exist. '.
	'Set a valid <code>$application_path</code> in <kbd>index.php</kbd> and refresh the page.'
);

// Check SYSPATH
(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/Kohana'.EXT)) or die
(
	'Your <code>$system_path</code> does not exist. '.
	'Set a valid <code>$system_path</code> in <kbd>index.php</kbd> and refresh the page.'
);

// Buckle those bootstraps!
require_once SYSPATH.'core/Kohana'.EXT;
// End Index file