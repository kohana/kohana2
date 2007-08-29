<?php defined('SYSPATH') or die('No direct access allowed.');

final class Log {

	public static $messages = array();

	public static function add($type, $message)
	{
		self::$messages[$type][] = array
		(
			date(Config::item('log.format')),
			$message
		);
	}

	public static function write()
	{
		$filename = Config::item('log.directory');

		// Don't log if there is nothing to log to
		if (count(self::$messages) == 0 OR $filename == FALSE) return;

		// Make sure that the log directory is absolute
		$filename = (substr($filename, 0, 1) !== '/') ? APPPATH.$filename : $filename;

		// Make sure there is an ending slash
		$filename = rtrim($filename, '/').'/';

		/**
		 * @todo i18n error
		 */
		is_writable($filename) or trigger_error
		(
			'Your log.directory config setting does not point to a writable directory. '.
			'Please correct the permissions and refresh the page.',
			E_USER_ERROR
		);

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
		if ($messages == '') return;

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

} // End Log Class