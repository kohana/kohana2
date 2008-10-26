<?php
/**
 * Kohana process control file, loaded by the front controller.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

define('KOHANA_VERSION',  '2.3');
define('KOHANA_CODENAME', 'kernachtig');

// Kohana benchmarks are prefixed to prevent collisions
define('SYSTEM_BENCHMARK', 'system_benchmark');

// Load benchmarking support
require SYSPATH.'classes/benchmark'.EXT;

// Start total_execution
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution');

// Start system_initialization
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Test of Kohana is running in Windows
define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

// Check UTF-8 support
if ( ! preg_match('/^.$/u', 'ñ'))
{
	trigger_error
	(
		'<a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support. '.
		'See <a href="http://php.net/manual/reference.pcre.pattern.modifiers.php">PCRE Pattern Modifiers</a> '.
		'for more information. This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if ( ! extension_loaded('iconv'))
{
	trigger_error
	(
		'The <a href="http://php.net/iconv">iconv</a> extension is not loaded. '.
		'Without iconv, strings cannot be properly translated to UTF-8 from user input. '.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if (extension_loaded('mbstring') AND (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING))
{
	trigger_error
	(
		'The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP\'s native string functions. '.
		'Disable this by setting mbstring.func_overload to 0, 1, 4 or 5 in php.ini or a .htaccess file.'.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

// Check PCRE support for Unicode properties such as \p and \X.
$ER = error_reporting(0);
define('PCRE_UNICODE_PROPERTIES', (bool) preg_match('/^\pL$/u', 'ñ'));
error_reporting($ER);

// SERVER_UTF8 ? use mb_* functions : use non-native functions
if (extension_loaded('mbstring'))
{
	mb_internal_encoding('UTF-8');
	define('SERVER_UTF8', TRUE);
}
else
{
	define('SERVER_UTF8', FALSE);
}

// Load utf8 support
require SYSPATH.'classes/utf8'.EXT;

// Load Event support
require SYSPATH.'classes/event'.EXT;

// Load Kohana core
require SYSPATH.'classes/kohana'.EXT;

// Convert all global variables to UTF-8.
$_GET    = utf8::clean($_GET);
$_POST   = utf8::clean($_POST);
$_COOKIE = utf8::clean($_COOKIE);
$_SERVER = utf8::clean($_SERVER);

if (PHP_SAPI == 'cli')
{
	// Convert command line arguments
	$_SERVER['argv'] = utf8::clean($_SERVER['argv']);
}

// Prepare the environment
Kohana::setup();

// Prepare the system
Event::run('system.ready');

// Stop system_initialization
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');

// Start routing
Benchmark::start(SYSTEM_BENCHMARK.'_routing');

// Determine routing
Event::run('system.routing');

// Stop routing
Benchmark::stop(SYSTEM_BENCHMARK.'_routing');

// Make the magic happen!
Event::run('system.execute');

// Clean up and exit
Event::run('system.shutdown');
