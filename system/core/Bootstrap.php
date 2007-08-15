<?php defined('SYSPATH') or die('No direct access allowed.');

// Kohana version
define('KOHANA_VERSION', '2.0');
// Kohana benchmarks are prefixed by a random string to prevent collisions
define('SYSTEM_BENCHMARK', uniqid(rand(1,100)));

// Load core classes
require SYSPATH.'core/Config'.EXT;
require SYSPATH.'core/Event'.EXT;
require SYSPATH.'core/Kohana'.EXT;
// Load UTF-8 compatible string functions
require SYSPATH.'core/utf8'.EXT;

// Run system.pre_init event
Event::run('system.pre_init');

// Setup Kohana
Kohana::setup();
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

// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');