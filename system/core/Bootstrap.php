<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * $Id$
 *
 * @filesource
 */
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
require SYSPATH.'core/Log'.EXT;
// Load UTF-8 compatible string functions
require SYSPATH.'core/utf8'.EXT;

// Run Kohana's setup routine
// This registers the Kohana handlers and prepares the output buffer
Kohana::setup();

// Run system.ready event
Event::run('system.ready');

// Stop base class loading benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_base_classes_loading');
// Start system initialization benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Run Router's setup routine
// All routing is performed at this stage
Router::setup();

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
	$controller = Kohana::instance();

	if (method_exists($controller, '_remap'))
	{
		// Change arguments to be $method, $arguments.
		// This makes _remap capable of being a much more effecient dispatcher
		Router::$arguments = array(Router::$method, Router::$arguments);
		// Set the method to _remap
		Router::$method = '_remap';
	}
	elseif (method_exists($controller, Router::$method))
	{
		(Router::$method !== 'kohana_include_view') or trigger_error
		(
			'This method cannot be accessed directly.',
			E_USER_ERROR
		);
	}
	elseif (method_exists($controller, '_default'))
	{
		// Change arguments to be $method, $arguments.
		// This makes _default a much more effecient 404 handler
		Router::$arguments = array(Router::$method, Router::$arguments);
		// Set the method to _default
		Router::$method = '_default';
	}
	else
	{
		/**
		 * @todo This needs to have an i18n error
		 */
		trigger_error('404: Page not found');
	}
	if (count(Router::$arguments) > 0)
	{
		call_user_func_array(array(Kohana::instance(), Router::$method), Router::$arguments);
	}
	else
	{
		call_user_func(array(Kohana::instance(), Router::$method));
	}

	// Run system.post_controller
	Event::run('system.post_controller');

	// Make sure that $controller is not available globally
	unset($controller);
}
catch (controller_not_found $exception)
{
	die('Controller not found: '.$exception);
}

// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');