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
