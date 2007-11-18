<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Profiler library configuration options.
 *
 * Options:
 *  driver     - Session driver name
 *  storage    - Session storage parameter, used by drivers
 *  name       - Default session name
 *  validate   - Session parameters to validate
 *  encryption - Encryption key, set to FALSE to disable session encryption
 *  expiration - Number of seconds that each session will last
 *  regenerate - Number of page loads before the session is regenerated
 */
$config = array
(
	'benchmarks' => TRUE,
	'database'   => TRUE,
	'post'       => TRUE,
	'session'    => TRUE
);