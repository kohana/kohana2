<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Message file logging class.
 * 
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
final class Log {

	private static $types = array(1 => 'error', 2 => 'debug', 3 => 'info');
	private static $messages = array();

	/**
	 * Add a log message.
	 *
	 * @param   string  info, debug, or error
	 * @param   string  message to be logged
	 * @return  void
	 */
	public static function add($type, $message)
	{
		self::$messages[strtolower($type)][] = array
		(
			date(Config::item('log.format')),
			strip_tags($message)
		);
	}

	/**
	 * Write the current log to a file.
	 *
	 * @return  void
	 */
	public static function write()
	{
		$filename  = Config::item('log.directory');
		$threshold = Config::item('log.threshold');

		// Don't log if there is nothing to log to
		if ($threshold < 1 OR count(self::$messages) == 0 OR $filename == FALSE) return;

		// Make sure that the log directory is absolute
		$filename = (substr($filename, 0, 1) === '/') ? $filename : APPPATH.$filename;

		// Find the realpath to the log directory
		$filename = realpath($filename).'/';

		// Make sure the log directory is writable
		if ( ! is_dir($filename) OR ! is_writable($filename))
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
			if (array_search($type, self::$types) > $threshold)
				continue;

			foreach($data as $date => $text)
			{
				list($date, $message) = $text;
				$messages .= $date.' -- '.$type.': '.$message."\r\n";
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