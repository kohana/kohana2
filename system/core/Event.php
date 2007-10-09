<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework.
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 */

// ----------------------------------------------------------------------------

/**
 * Kohana Event class
 *
 * @category    Core
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/core_classes.html
 */
final class Event {

	private static $events = array();

	public static $data;

	/**
	 * Add an event
	 *
	 * @access  public
	 * @param   string
	 * @param   callback
	 * @return  void
	 */
	public static function add($name, $callback)
	{
		if ($name == FALSE OR $callback == FALSE)
			return FALSE;

		// Make sure that the event name is defined
		if ( ! isset(self::$events[$name]))
		{
			self::$events[$name] = array();
		}

		// Make sure the event is not already in the queue
		if ( ! in_array($callback, self::$events[$name]))
		{
			self::$events[$name][] = $callback;
		}
	}

	/**
	 * Fetch an event
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(self::$events[$name]) ? array() : self::$events[$name];
	}

	/**
	 * Clear an event
	 *
	 * @access  public
	 * @param   string
	 * @param   callback
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback == FALSE)
		{
			self::$events[$name] = array();
		}
		elseif (isset(self::$events[$name]))
		{
			foreach(self::$events[$name] as $i => $event_callback)
			{
				if ($callback == $event_callback)
				{
					unset(self::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * Run an event
	 *
	 * @access  public
	 * @param   string
	 * @param   array
	 * @return  void
	 */
	public static function run($name, & $data = NULL)
	{
		if ($name == FALSE)
			return FALSE;

		// So callbacks can access Event::$data
		self::$data =& $data;

		foreach(self::get($name) as $callback)
		{
			call_user_func($callback);
		}

		// Do this to prevent data from getting 'stuck'
		$clear_data = '';
		self::$data =& $clear_data;
	}

} // End Event Class