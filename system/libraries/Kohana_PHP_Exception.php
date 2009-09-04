<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana PHP Error Exceptions
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

class Kohana_PHP_Exception_Core extends Kohana_Exception {

	public static $disabled = false;
	
	/**
	 * Enable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		// Register with non shutdown errors
		set_error_handler(array(__CLASS__, 'error_handler'));
		// Register a shutdown function to handle fatal errors 
		register_shutdown_function(array(__CLASS__, 'shutdown_handler'));
	}

	/**
	 * Disable Kohana PHP error handling.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		self::$disabled = true;
		restore_error_handler();
	}

	/**
	 * Create a new PHP error exception.
	 *
	 * @return  void
	 */
	public function __construct($code, $error, $file, $line, $context = NULL)
	{
		parent::__construct($error);

		// Set the error code, file, line, and context manually
		$this->code = $code;
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 * PHP error handler.
	 *
	 * @throws  Kohana_PHP_Exception
	 * @return  void
	 */
	public static function error_handler($code, $error, $file, $line, $context = NULL)
	{
		if ((error_reporting() & $code) === 0)
		{
			// Respect error_reporting settings
			return;
		}

		// An error has been triggered
		Kohana::$has_error = TRUE;

		// Throw an exception
		throw new Kohana_PHP_Exception($code, $error, $file, $line, $context);

		// Do not execute the PHP error handler
		return TRUE;
	}
	
	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Kohana_Exception::handle()
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if (self::$disabled === true)
		{
			//this will prevent any future exception handlers from running
			exit();
		}
		if ($error = error_get_last())
		{
			// If an output buffer exists, clear it
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			Kohana_Exception::handle(new Kohana_PHP_Exception($error['type'], $error['message'], $error['file'], $error['line']));
		}
	}
} // End Kohana PHP Exception