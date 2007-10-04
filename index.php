<?php
/*
| -----------------------------------------------------------------------------
| Kohana - The Swift PHP5 Framework
| -----------------------------------------------------------------------------
| This file acts as the "front controller" to your application. If you do not
| understand the configuration parameters below, please consult the Kohana
| User Guide for more information.
| -----------------------------------------------------------------------------
| User Guide: http://kohanaphp.com/user_guide/en/kohana/installation.html
| -----------------------------------------------------------------------------
| License:    http://kohanaphp.com/user_guide/en/license.html
| -----------------------------------------------------------------------------
*/

/**
 * Set the error reporting level. E_ALL is a good default.
 * NOTE: Kohana will always ignore E_NOTICE errors
 */
error_reporting(E_ALL);

/**
 * Enable or disable error reporting. You should always disable this in production.
 */
ini_set('display_errors', TRUE);

/**
 * Kohana application directory. This directory must contain a config/ directory.
 */
$kohana_application = 'application';

/**
 * Kohana system directory. This directory must contain the core/ directory.
 */
$kohana_system = 'system';

/**
 * If you have to rename all of the .php files distributed with Kohana to a
 * different filename, you set the new extension here. Most people will never
 * use this option.
 */
define('EXT', '.php');

/*
| -----------------------------------------------------------------------------
| PLEASE DO NOT EDIT BELOW THIS LINE, unless you understand the repercussions!
| -----------------------------------------------------------------------------
| User Guide: http://kohanaphp.com/user_guide/en/general/bootstrapping.html
| -----------------------------------------------------------------------------
| $Id$
*/
$docroot = pathinfo(str_replace('\\', '/', realpath(__FILE__)));

define('KOHANA',  $docroot['basename']);
define('DOCROOT', $docroot['dirname'].'/');

define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

unset($docroot, $kohana_application, $kohana_system);

(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$application_path</code> does not exist. '.
	'Set a valid <code>$application_path</code> in <kbd>index.php</kbd> and refresh the page.'
);

(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/'.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> does not exist. '.
	'Set a valid <code>$kohana_system</code> in <kbd>index.php</kbd> and refresh the page.'
);

require_once SYSPATH.'core/Bootstrap'.EXT;