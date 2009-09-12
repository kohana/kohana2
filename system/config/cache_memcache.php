<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Cache:Memcache
 *
 * memcache server configuration.
 */
$config['servers'] = array
(
	array
	(
		'host' => '127.0.0.1',
		'port' => 11211,
		'persistent' => FALSE,
		'weight' => 1,
		'timeout' => 1,
		'retry_interval' => 15
	)
);

/**
 * Enable cache data compression.
 */
$config['compression'] = FALSE;

/**
 * Enable memcahe failover
 * This is a BAD idea. Leave as FALSE unless you know what your doing. 
 */
$config['allow_failover'] = FALSE;