<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Session Configuration
 * ----------------------------------------------------------------------------
 *
 * User Guide: http://kohanaphp.com/user_guide/en/libraries/session.html
 *
 * @param  string  driver     - Session driver name
 * @param  string  storage    - Session storage parameter, used by drivers
 * @param  string  name       - Default session name
 * @param  array   validate   - Session parameters to validate
 * @param  mixed   encryption - Encryption key, set to FALSE to disable session encryption
 * @param  integer expiration - Number of seconds that each session will last
 * @param  integer regenerate - Number of page loads before the session is regenerated
 *
 */
$config = array
(
	'driver'     => 'cookie',
	'storage'    => '',
	'name'       => 'kohana_session',
	'validate'   => array('user_agent'),
	'encryption' => FALSE,
	'expiration' => 7200,
	'regenerate' => 3
);