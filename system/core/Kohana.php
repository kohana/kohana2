<?php defined('SYSPATH') or die('No direct access allowed.');

define('KOHANA_VERSION', '1.2');

/**
 * We define a variable to hold a random string for the system benchmark prefix.
 * This allows 100% freeform benchmarking for application.
 */
define('SYSTEM_BENCHMARK', uniqid(rand(1,100)));

// This contains the Core class, common functions, and magic PHP functions.
require SYSPATH.'core/Common'.EXT;

// Start the system benchmarks
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution_time');
Benchmark::start(SYSTEM_BENCHMARK.'_base_classes_loading');

// Run the pre_system hook
Core::load_file('hook', 'pre_system');

// Load Routing
Router::load_segments();

// Validate Controller
(file_exists(Router::$directory.Router::$controller.EXT)) OR Core::show_error
(
	'core', 'controller_not_found', ucfirst(Router::$controller)
);

// Stop base class loading benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_base_classes_loading');

// Start the controller execution benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

// Initialize the system, load Controller
Core::initialize();

// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');

print_r(Router::$segments);


print Benchmark::get(SYSTEM_BENCHMARK.'_total_execution_time');