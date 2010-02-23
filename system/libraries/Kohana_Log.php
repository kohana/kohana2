<?php 
/**
 * Logging class.
 *
 * $Id: Kohana_Log.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

namespace Library;

defined('SYSPATH') OR die('No direct access allowed.');

class Kohana_Log {

	// Configuration
	protected static $config;

	// Drivers
	protected static $drivers;

	// Logged messages
	protected static $messages;

	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function add($type, $message)
	{
		// Make sure the drivers and config are loaded
		if ( ! is_array(Kohana_Log::$config))
		{
			Kohana_Log::$config = \Kernel\Kohana::config('log');
		}

		if ( ! is_array(Kohana_Log::$drivers))
		{
			foreach ( (array) \Kernel\Kohana::config('log.drivers') as $driver_name)
			{
				// Set driver name
				$driver = '\Driver\Log\\'.ucfirst($driver_name);

				// Load the driver
				if ( ! \Kernel\Kohana::auto_load($driver))
					throw new \Kernel\Kohana_Exception('Log Driver Not Found: %driver%', array('%driver%' => $driver));

				// Initialize the driver
				$driver = new $driver(array_merge(\Kernel\Kohana::config('log'), \Kernel\Kohana::config('log_'.$driver_name)));

				// Validate the driver
				if ( ! ($driver instanceof \Driver\Log))
					throw new \Kernel\Kohana_Exception('%driver% does not implement the Log interface', array('%driver%' => $driver));

				Kohana_Log::$drivers[] = $driver;
			}

			// Always save logs on shutdown
			\Kernel\Event::add('system.shutdown', array('Kohana_Log', 'save'));
		}

		Kohana_Log::$messages[] = array('date' => time(), 'type' => $type, 'message' => $message);
	}

	/**
	 * Save all currently logged messages.
	 *
	 * @return  void
	 */
	public static function save()
	{
		if (empty(Kohana_Log::$messages))
			return;

		foreach (Kohana_Log::$drivers as $driver)
		{
			// We can't throw exceptions here or else we will get a
			// Exception thrown without a stack frame error
			try
			{
				$driver->save(Kohana_Log::$messages);
			}
			catch(\Exception $e){}
		}

		// Reset the messages
		Kohana_Log::$messages = array();
	}
}