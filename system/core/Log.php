<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Log
 *  Message file logging class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
final class Log {

	private static $messages = array();

	/*
	 * Method: add
	 *  Add a log message.
	 *
	 * Parameters:
	 *  type    - info, debug, or error
	 *  message - message to be logged
	 */
	public static function add($type, $message)
	{
		self::$messages[$type][] = array
		(
			date(Config::item('log.format')),
			$message
		);
	}

	/*
	 * Method: write
	 *  Write the current log to a file.
	 */
	public static function write()
	{
		$filename = Config::item('log.directory');

		// Don't log if there is nothing to log to
		if (count(self::$messages) == 0 OR $filename == FALSE) return;

		// Make sure that the log directory is absolute
		$filename = (substr($filename, 0, 1) !== '/') ? APPPATH.$filename : $filename;

		// Make sure there is an ending slash
		$filename = rtrim($filename, '/').'/';

		// Make sure the log directory is writable
		if ( ! is_writable($filename))
		{
			ob_get_level() AND ob_clean();
			exit(Kohana::lang('core.cannot_write_log'));
		}

		// Attach the filename to the directory
		$filename .= date('Y-m-d').'.log'.EXT;

		$messages = '';

		// Get messages
		foreach(self::$messages as $type => $data)
		{
			foreach($data as $date => $text)
			{
				list($date, $message) = $text;
				$messages .= $date.' --- '.$type.': '.$message."\r\n";
			}
		}

		// No point in logging nothing
		if ($messages == '')
			return;

		// Create the log file if it doesn't exist yet
		if ( ! file_exists($filename))
		{
			touch($filename);
			chmod($filename, 0644);

			// Add our PHP header to the log file to prevent URL access
			$messages = "<?php defined('SYSPATH') or die('No direct script access.'); ?>\r\n\r\n".$messages;
		}

		// Append the messages to the log
		file_put_contents($filename, $messages, FILE_APPEND) or trigger_error
		(
			'The log file could not be written to. Please correct the permissions and refresh the page.',
			E_USER_ERROR
		);
	}

} // End Log