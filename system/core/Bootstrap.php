<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework for PHP5+
 *
 * $Id$
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 */

define('KOHANA_VERSION',  '2.0a1');
define('KOHANA_CODENAME', 'Superlime');

// Kohana benchmarks are prefixed by a random string to prevent collisions
define('SYSTEM_BENCHMARK', uniqid(rand(1, 100)));

// Load the benchmarking class
require SYSPATH.'core/Benchmark'.EXT;

// Start the system benchmarks
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution_time');
Benchmark::start(SYSTEM_BENCHMARK.'_base_classes_loading');

// Load core classes
require SYSPATH.'core/Kohana'.EXT;

// Run system.setup event
// This sets up Kohana's PHP hooks, output buffering, error handling, etc
Event::run('system.setup');

// Stop base class loading benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_base_classes_loading');
// Start system initialization benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Run system.ready event
Event::run('system.ready');

// Run system.routing
// All routing is performed at this stage
Event::run('system.routing');

// Stop system initialization benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');
// Start the controller execution benchmark
Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

// Run system.execute
// The controller is loaded and executed at this point
Event::run('system.execute');

// Stop the controller execution benchmark
Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');

// Manually flush the output buffer to allow loading views in the system.output event
Event::run('system.shutdown');