<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Session Configuration
 *
 * @param  string  driver     - Session driver name
 * @param  string  name       - Default session name
 * @param  array   validate   - Session parameters to validate
 * @param  mixed   encryption - Encryption key for sessions
 * @param  int     expiration - Number of seconds that each session will last
 * @param  int     regenerate - Number of page loads before the session is regenerated
 */

// ----------------------------------------------------------------------------

$config['driver']     = 'cookie';
$config['name']       = 'kohana_session';
$config['validate']   = array('user_agent');
$config['encryption'] = FALSE;
$config['expiration'] = 7200;
$config['regenerate'] = 3;

// DEFAULT SETTINGS -----------------------------------------------------------

/*
$config['driver']     = 'cookie';
$config['name']       = 'kohana_session';
$config['validate']   = array('user_agent');
$config['encryption'] = FALSE;
$config['expiration'] = 7200;
$config['regenerate'] = 3;
*/