<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Log thresholds:
 *  0 - Disable logging
 *  1 - Errors and exceptions
 *  2 - Warnings
 *  3 - Notices
 *  4 - Debugging
 */
$config['log_threshold'] = 1;

$config['date_format'] = 'Y-m-d H:i:s P';

// We can define multiple logging backends at the same time.
$config['driver'] = array('file');