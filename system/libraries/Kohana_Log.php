<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Logging class.
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Log_Core {

	// Configuration
	protected static $config = array
	(
		'log_threshold' => 1,
		'driver'        => 'file',
	);

	// Drivers
	protected static $drivers;

	// Logged messages
	protected static $messages;

	// Log levels
	protected static $log_levels = array
	(
		'error' => 1,
		'alert' => 2,
		'info'  => 3,
		'debug' => 4,
	);

	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public function add($type, $message)
	{
		// Make sure the drivers are loaded
		if ( ! is_array(self::$driver))
		{
			foreach (Kohana::config('log.drivers') as $driver_name)
			{
				// Set driver name
				$driver = 'Kohana_Log_'.ucfirst($driver_name).'_Driver';

				// Load the driver
				if ( ! Kohana::auto_load($driver))
					throw new Kohana_Exception('Log Driver Not Found: %driver%', array('%driver%' => $driver);

				// Initialize the driver
				$driver = new $driver(Kohana::config('log'));

				// Validate the driver
				if ( ! (self::$driver instanceof Kohana_Log_Driver))
					throw new Kohana_Exception('%driver% does not implement the Log_Driver interface', array('%driver%' => $driver);

				self::$drivers[] = $driver;
			}
		}

		if (self::$log_levels[$type] <= Kohana::config('log.log_threshold'))
		{
			$message = array(date(Kohana::config('log.date_format')), $type, $message);

			self::$messages[] = $message;

			self::save();
		}
	}

	/**
	 * Save all currently logged messages.
	 *
	 * @return  void
	 */
	protected function save()
	{
		if (empty($this->$log) OR $this->$config['log_threshold'] < 1)
			return;

		foreach (self::$drivers as $driver)
		{
			$driver->save(self::$messages);
		}

		// Reset the messages
		self::$messages = array();


		// This all goes in the file driver...
		/*
		// Filename of the log
		$filename = self::log_directory().date('Y-m-d').'.log'.EXT;

		if ( ! is_file($filename))
		{
			// Write the SYSPATH checking header
			file_put_contents($filename,
				'<?php defined(\'SYSPATH\') or die(\'No direct script access.\'); ?>'.PHP_EOL.PHP_EOL);

			// Prevent external writes
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();

		do
		{
			// Load the next mess
			list ($date, $type, $text) = array_shift(self::$log);

			// Add a new message line
			$messages[] = $date.' --- '.$type.': '.$text;
		}
		while ( ! empty(self::$log));

		// Write messages to log file
		file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);*/
	}
}