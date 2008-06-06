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
	 * @param   string  type of message
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

		// Do nothing if logging is disabled or no messages exist
		if (empty(self::$log_directory) OR $threshold < 1 OR count(self::$messages) === 0)
			return;

		// Set the log filename
		$filename = self::$log_directory.Config::item('log.prefix').date('Y-m-d').'.log'.EXT;

		$messages = array();
		foreach (self::$messages as $type => $data)
		{
			// Skip all messages below the threshold
			if (array_search($type, self::$types) > $threshold)
				continue;

			for ($i = 0, $max = count($data); $i < $max; $i++)
			{
				// Compile messages: 0 = time, 1 = message text
				$messages[] = "{$data[$i][0]} -- {$type}: {$data[$i][1]}";
			}
		}

		// Prevent empty logs
		if (empty($messages))
			return;

		if ( ! file_exists($filename))
		{
			// Create the log file, adding the Kohana header to prevent URL access
			file_put_contents($filename, '<?php defined(\'SYSPATH\') or die(\'No direct script access.\'); ?>'.PHP_EOL.PHP_EOL);

			// Chmod the file to prevent external writes
			chmod($filename, 0644);
		}

		// Append the messages to the log
		file_put_contents($filename, implode(PHP_EOL, $messages), FILE_APPEND) or trigger_error
		(
			'The log file could not be written to. Please correct the permissions and refresh the page.',
			E_USER_ERROR
		);

		// Reset the messages after writing
		self::$messages = array();
	}

} // End Log