<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * File: Session
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
	'driver'     => 'cookie',
	'storage'    => '',
	'name'       => 'kohana_session',
	'validate'   => array('user_agent'),
	'encryption' => FALSE,
	'expiration' => 7200,
	'regenerate' => 3
);