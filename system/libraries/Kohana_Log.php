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
	protected static $config;

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
		// Make sure the drivers and config are loaded
		if ( ! is_array(self::$config))
		{
			self::$config = Kohana::config('log');
		}

		if ( ! is_array(self::$drivers))
		{
			foreach ( (array) Kohana::config('log.drivers') as $driver_name)
			{
				// Set driver name
				$driver = 'Log_'.ucfirst($driver_name).'_Driver';

				// Load the driver
				if ( ! Kohana::auto_load($driver))
					throw new Kohana_Exception('Log Driver Not Found: %driver%', array('%driver%' => $driver));

				// Initialize the driver
				$driver = new $driver(array_merge(Kohana::config('log'), Kohana::config('log_'.$driver_name)));

				// Validate the driver
				if ( ! ($driver instanceof Log_Driver))
					throw new Kohana_Exception('%driver% does not implement the Log_Driver interface', array('%driver%' => $driver));

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
		if (empty(self::$messages) OR self::$config['log_threshold'] < 1)
			return;

		foreach (self::$drivers as $driver)
		{
			$driver->save(self::$messages);
		}

		// Reset the messages
		self::$messages = array();
	}
}