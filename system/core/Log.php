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

	private static $log_directory;

	private static $types = array(1 => 'error', 2 => 'alert', 3 => 'info', 4 => 'debug');
	private static $messages = array();

	/**
	 * Set the the log directory. The log directory is determined by Kohana::setup,
	 * but can be changed during execution
	 *
	 * @throws  Kohana_Exception
	 * @param   string       directory path
	 * @return  string|void
	 */
	public static function directory($directory = NULL)
	{
		if ($directory === NULL)
		{
			// Return the directory
			return self::$log_directory;
		}

		// Get the full path to the directory
		$directory = realpath($directory);

		if (file_exists($directory) AND is_dir($directory) AND is_writable($directory))
		{
			// Set the log directory
			self::$log_directory = $directory.'/';
		}
		else
		{
			// Log directory is invalid
			throw new Kohana_Exception('core.log_dir_unwritable', $directory);
		}
	}

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
		// Set the log threshold
		$threshold = Config::item('log.threshold');

		// Don't log if there is nothing to log to
		if ($threshold < 1 OR count(self::$messages) === 0) return;

		// Set the log filename
		$filename = self::$log_directory.Config::item('log.prefix').date('Y-m-d').'.log'.EXT;

		// Compile the messages
		$messages = '';
		foreach (self::$messages as $type => $data)
		{
			if (array_search($type, self::$types) > $threshold)
				continue;

			foreach ($data as $date => $text)
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

		// Reset the messages after writing
		self::$messages = array();
	}

} // End Log
