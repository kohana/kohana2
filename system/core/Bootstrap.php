<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: Bootstrap
 *  Kohana process control file, loaded by <index.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
define('KOHANA_VERSION',  '2.0');
define('KOHANA_CODENAME', 'Superlime');

// Kohana benchmarks are prefixed by a random string to prevent collisions
define('SYSTEM_BENCHMARK', uniqid(rand(1, 100)));

require SYSPATH.'core/Benchmark'.EXT;
Benchmark::start(SYSTEM_BENCHMARK.'_total_execution_time');

Benchmark::start(SYSTEM_BENCHMARK.'_kohana_loading');
require SYSPATH.'core/utf8'.EXT;
require SYSPATH.'core/Config'.EXT;
require SYSPATH.'core/Log'.EXT;
require SYSPATH.'core/Event'.EXT;
require SYSPATH.'core/Kohana'.EXT;
Benchmark::stop(SYSTEM_BENCHMARK.'_kohana_loading');

Event::run('system.setup');

Benchmark::start(SYSTEM_BENCHMARK.'_system_initialization');
Event::run('system.ready');
Event::run('system.routing');
Benchmark::stop(SYSTEM_BENCHMARK.'_system_initialization');

Event::run('system.execute');

Event::run('system.shutdown');