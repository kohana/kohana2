<?php defined('SYSPATH') or die('No direct access allowed.');

// Kohana version
define('KOHANA_VERSION', '2.0');
// Kohana benchmarks are prefixed by a random string to prevent collisions
define('SYSTEM_BENCHMARK', uniqid(rand(1,100)));

// Load the benchmarking class
require SYSPATH.'core/Benchmark'.EXT;

// Start the system benchmarks
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution_time');
Benchmark::start(SYSTEM_BENCHMARK.'_base_classes_loading');

// Load core classes
require SYSPATH.'core/Config'.EXT;
require SYSPATH.'core/Event'.EXT;
require SYSPATH.'core/Kohana'.EXT;
// Load UTF-8 compatible string functions
require SYSPATH.'core/utf8'.EXT;

// Run Kohana's setup routine
// This registers the Kohana handlers and prepares the output buffer
Kohana::setup();

// Run system.ready event
Event::run('system.ready');

// Run Router's setup routine
// All routing is performed at this stage
Router::setup();

// Stop base class loading benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_base_classes_loading');
// Start system initialization benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Run system.initialize
Event::run('system.initialize');

// Stop system initialization benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');
// Start the controller execution benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

try
{
	// Load the controller
	Kohana::instance();

	// Run system.ready_controller
	Event::run('system.ready_controller');

	/**
	 * @todo This needs to check for _remap and _default, as well as validating that method exists
	 */
	call_user_func_array(array(Kohana::instance(), Router::$method), Router::$arguments);

	// Run system.post_controller
	Event::run('system.post_controller');
}
catch (controller_not_found $exception)
{
	die('Controller not found: '.$exception);
}

// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');