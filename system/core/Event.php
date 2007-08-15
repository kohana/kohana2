<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
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
 * @author      Kohana Development Team
 * @link        http://kohanaphp.com/user_guide/core_classes.html
 */
final class Event {

	public static $events = array();

	public static function add($name, $callback, $params = array())
	{
		if ($name == FALSE OR $callback == FALSE)
			return FALSE;

		self::$events[$name][] = array($callback, $params);
	}

	public static function run($name)
	{
		if ($name == FALSE)
			return FALSE;

		if (isset(self::$events[$name]) AND count(self::$events[$name]))
		{
			foreach(array_reverse(self::$events[$name]) as $event)
			{
				if ( ! empty($event[1]))
				{
					call_user_func_array($event[0], $event[1]);
				}
				else
				{
					call_user_func($event[0]);
				}
			}
		}
	}

} // End Event Class