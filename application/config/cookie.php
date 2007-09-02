<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie Configuration
 *
 * @param  string  prefix   - Prefix to avoid collisions
 * @param  string  domain   - Domain to restrict cookie to
 * @param  string  path     - Path to restrict cookie to
 * @param  integer expire   - Liftime of cookie in seconds (0 = until browser closes)
 * @param  boolean secure   - Only allow the cookie on HTTPS
 * @param  boolean httponly - Only allow cookie access through the HTTP protocol
 */

// ----------------------------------------------------------------------------

$config['prefix']   = '';
$config['domain']   = '';
$config['path']     = '/';
$config['expire']   = 0;
$config['secure']   = FALSE;
$config['httponly'] = FALSE;
