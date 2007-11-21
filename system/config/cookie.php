<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: Cookie
 *  By default, cookie security is very relaxed. You are encouraged to set a
 *  domain and path to add some security to your cookies.
 *
 * Options:
 *  prefix   - Prefix to avoid collisions, empty for no prefix
 *  domain   - Domain to restrict cookie to, empty to allow any domain
 *  path     - Path to restrict cookie to, empty to allow any path
 *  expire   - Liftime of cookie in seconds, 0 for a single browser session
 *  secure   - Enable or disable HTTPS-only access
 *  httponly - Enable or disable HTTP-only access
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