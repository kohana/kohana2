<?php defined('SYSPATH') or die('No direct access allowed.');

define('KOHANA_VERSION', '1.2');

/**
 * We define a variable to hold a random string for the system benchmark prefix.
 * This allows 100% freeform benchmarking for application.
 */
define('SYSTEM_BENCHMARK', uniqid(rand(1,100)));

// Core class, common functions, and magic PHP functions.
require SYSPATH.'core/Kohana'.EXT;
// UTF-8 compatible string functions
require SYSPATH.'core/utf8'.EXT;
// Start output buffering
ob_start(array('Kohana', 'output'));

// Start the system benchmarks
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution_time');
Benchmark::start(SYSTEM_BENCHMARK.'_base_classes_loading');

// Load Routing
try
{
	Router::initialize();
}
catch (file_not_found $exception)
{
	/**
	 * @todo make this display a real error
	 */
	die('File not found: '.$exception);
}

// Validate Controller
if ( ! file_exists(Router::$directory.Router::$controller.EXT))
	throw new controller_not_found(ucfirst(Router::$controller));

// Stop base class loading benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_base_classes_loading');

// Start the controller execution benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

// Initialize the system, load Controller
Kohana::initialize();

exit('done: {execution_time} seconds');
// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');