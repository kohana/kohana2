<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package Session
 *
 * Session driver name.
 */
$config['driver'] = 'cookie';

/**
 * Session storage parameter, used by drivers.
 */
$config['storage'] = '';

/**
 * Default session name.
 */
$config['name'] = 'kohana_session';

/**
 * Session parameters to validate.
 */
$config['validate'] = array('user_agent');

/**
 * Encryption key, set to FALSE to disable session encryption.
 */
$config['encryption'] = FALSE;

/**
 * Number of seconds that each session will last.
 * A value of 0 will keep the session active until the browser is closed.
 */
$config['expiration'] = 7200;

/**
 * Number of page loads before the session is regenerated.
 */
$config['regenerate'] = 3;