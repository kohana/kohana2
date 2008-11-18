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

// Load benchmarking support
require SYSPATH.'classes/benchmark'.EXT;

// Start total_execution
Benchmark::start('system.total_execution');

// Start bootstrap
Benchmark::start('system.bootstrap');

// Define Kohana error constant
define('E_KOHANA', 42);

// Define database error constant
define('E_DATABASE_ERROR', 44);

// Test of Kohana is running in Windows
define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

if (extension_loaded('mbstring'))
{
	// Use mb_* utf8 functions when possible
	mb_internal_encoding('UTF-8');
	define('SERVER_UTF8', TRUE);
}
else
{
	// Use internal utf8 functions
	define('SERVER_UTF8', FALSE);
}

// Load Kohana core
// Load Event support
require SYSPATH.'classes/event'.EXT;

// Load Kohana core
require SYSPATH.'classes/kohana'.EXT;

// Create a new instance
new Kohana;

// Convert all global variables to UTF-8
$_GET    = utf8::clean($_GET);
$_POST   = utf8::clean($_POST);
$_COOKIE = utf8::clean($_COOKIE);
$_SERVER = utf8::clean($_SERVER);

// Send default text/html UTF-8 header
header('Content-Type: text/html; charset=UTF-8');

// Prepare the system
Event::run('system.ready');

// Stop boostrap
Benchmark::stop('system.bootstrap');

// Start execution
Benchmark::start('system.execute');

// Start dispatching the request
Event::run('system.execute');

// Stop execution
Benchmark::stop('system.execute');

// Clean up and exit
Event::run('system.shutdown');
