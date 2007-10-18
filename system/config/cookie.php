<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie Configuration
 * ----------------------------------------------------------------------------
 * By default, cookie security is very relaxed. You are encouraged to set a
 * domain and path to add some security to your cookies.
 *
 * User Guide: http://kohanaphp.com/user_guide/en/general/cookies.html
 *
 * @param  string  prefix   - Prefix to avoid collisions
 * @param  string  domain   - Domain to restrict cookie to
 * @param  string  path     - Path to restrict cookie to
 * @param  integer expire   - Liftime of cookie in seconds (0 = until browser closes)
 * @param  boolean secure   - Only allow the cookie on HTTPS
 * @param  boolean httponly - Only allow cookie access through the HTTP protocol
 *
 */
$config = array
(
	'prefix'   => '',
	'domain'   => '',
	'path'     => '/',
	'expire'   => 0,
	'secure'   => FALSE,
	'httponly' => FALSE
);