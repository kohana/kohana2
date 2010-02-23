<?php
/**
 * Kohana process control file, loaded by the front controller.
 *
 * $Id: Bootstrap.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

defined('SYSPATH') OR die('No direct access allowed.');

// Kohana benchmarks are prefixed to prevent collisions
define('SYSTEM_BENCHMARK', 'system_benchmark');

// Load benchmarking support
require SYSPATH.'kernel/Benchmark'.EXT;

// Start total_execution
\Kernel\Benchmark::start(SYSTEM_BENCHMARK.'_total_execution');

// Start kohana_loading
\Kernel\Benchmark::start(SYSTEM_BENCHMARK.'_kohana_loading');

// Load kernel files
require SYSPATH.'kernel/Event'.EXT;
final class Event extends \Kernel\Event {}

require SYSPATH.'kernel/Kohana'.EXT;
final class Kohana extends \Kernel\Kohana {}

require SYSPATH.'kernel/Kohana_Exception'.EXT;
class Kohana_Exception extends \Kernel\Kohana_Exception {}

require SYSPATH.'kernel/Kohana_Config'.EXT;
require SYSPATH.'libraries/drivers/Config'.EXT;
require SYSPATH.'libraries/drivers/Config/Array'.EXT;
final class Kohana_Config extends \Kernel\Kohana_Config {}

// Prepare the environment
Kohana::setup();

// End kohana_loading
\Kernel\Benchmark::stop(SYSTEM_BENCHMARK.'_kohana_loading');

// Start system_initialization
\Kernel\Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');

// Prepare the system
\Kernel\Event::run('system.ready');

// Determine routing
\Kernel\Event::run('system.routing');

// End system_initialization
\Kernel\Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');

// Make the magic happen!
\Kernel\Event::run('system.execute');