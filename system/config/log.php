<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Log
 *  Message logging is a very useful debugging tool for production websites, as
 *  well as a useful tool during development to see what files are being loaded
 *  in what order.
 *  
 *  You may also set a threshold to choose what information gets logged, as well
 *  as a timestamp format for the log messages.
 *
 * Log Thresholds:
 *  0 - Disables logging completely
 *  1 - Error Messages (including PHP errors)
 *  2 - Debug Messages
 *  3 - Informational Messages
 *  4 - All Messages
 *
 * Options:
 * threshold  - Message threshold
 * directory  - Log file directory, relative to application/, or absolute
 * format     - PHP date format for timestamps, see: http://php.net/date
 *
 * Note:
 *  In production, it is recommended that you set disable "display_errors" in
 *  your index file, and set the logging threshold to log only errors (level 1).
 */
$config = array
(
	'threshold' => 4,
	'directory' => 'logs',
	'format'    => 'Y-m-d H:i:s'
);