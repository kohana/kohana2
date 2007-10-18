<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Message Logging
 * ----------------------------------------------------------------------------
 * If you have enabled message logging, you can set a threshold to determine
 * what gets logged. Setting the threshold to 0 will disable logging completely.
 *
 *   0 = Disables logging completely
 *   1 = Error Messages (including PHP errors)
 *   2 = Debug Messages
 *   3 = Informational Messages
 *   4 = All Messages
 *
 * In production, you will want to disable "display_errors" in your index file,
 * and set the threshold to enable only errors (1).
 *
 * @param integer threshold  - Message threshold
 * @param string  directory  - Log file directory, relative to application/, or absolute
 * @param string  format     - PHP date format for timestamps, see: http://php.net/date
 *
 */
$config = array
(
	'threshold' => 4,
	'directory' => 'logs',
	'format'    => 'Y-m-d H:i:s'
);