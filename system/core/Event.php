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

	public static $events = array();

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

		self::$events[$name][] = $callback;
	}

	/**
	 * Run an event
	 *
	 * @access  public
	 * @param   string
	 * @param   array
	 * @return  mixed
	 */
	public static function run($name, $args = array())
	{
		if ($name == FALSE)
			return FALSE;

		if (isset(self::$events[$name]) AND count(self::$events[$name]))
		{
			foreach(array_reverse(self::$events[$name]) as $event)
			{
				if ($args == TRUE)
				{
					call_user_func_array($event, (array) $args);
				}
				else
				{
					call_user_func($event);
				}
			}
		}
	}

} // End Event Class