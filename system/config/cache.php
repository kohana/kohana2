<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cache library configuration.
 *
 * Options:
 *  driver   - cache driver to use, "file" is supported by default
 *  params   - parameters for the driver, "file" uses a directory name
 *  lifetime - default lifetime for all cache items
 *  cleanup  - percentage chance that garbage collection will be done
 */
$config = array
(
	'driver'   => 'file',
	'params'   => 'application/cache',
	'lifetime' => 1800,
	'cleanup'  => 3,
);