<?php
/**
 * Process queuing/execution class. Allows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple times, and can also
 * process event-specific data.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
final class Event {

	// Event callbacks
	private static $events = array();

	// Cache of events that have been run
	private static $has_run = array();

	/**
	 * Add a callback to an event queue.
	 *
	 * @param   string   event name
	 * @param   array    http://php.net/callback
	 * @return  boolean
	 */
	public static function add($name, $callback)
	{
		if ( ! isset(self::$events[$name]))
		{
			// Create an empty event if it is not yet defined
			self::$events[$name] = array();
		}
		elseif (in_array($callback, self::$events[$name], TRUE))
		{
			// The event already exists
			return FALSE;
		}

		// Add the event
		self::$events[$name][] = $callback;

		return TRUE;
	}

	/**
	 * Add a callback to an event queue, before a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_before($name, $existing, $callback)
	{
		if (empty(self::$events[$name]) OR ($key = array_search($existing, self::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return self::add($name, $callback);
		}
		else
		{
			// Insert the event immediately before the existing event
			return self::insert_event($name, $key, $callback);
		}
	}

	/**
	 * Add a callback to an event queue, after a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_after($name, $existing, $callback)
	{
		if (empty(self::$events[$name]) OR ($key = array_search($existing, self::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return self::add($name, $callback);
		}
		else
		{
			// Insert the event immediately after the existing event
			return self::insert_event($name, $key + 1, $callback);
		}
	}

	/**
	 * Inserts a new event at a specfic key location.
	 *
	 * @param   string   event name
	 * @param   integer  key to insert new event at
	 * @param   array    event callback
	 * @return  void
	 */
	private static function insert_event($name, $key, $callback)
	{
		if (in_array($callback, self::$events[$name], TRUE))
		{
			// The callback already exists in the event
			return FALSE;
		}

		// Add the new event at the given key location
		self::$events[$name] = array_merge
		(
			// Events before the key
			array_slice(self::$events[$name], 0, $key),
			// New event callback
			array($callback),
			// Events after the key
			array_slice(self::$events[$name], $key)
		);

		return TRUE;
	}

	/**
	 * Replaces an event with another event.
	 *
	 * @param   string   event name
	 * @param   array    event to replace
	 * @param   array    new callback
	 * @return  boolean
	 */
	public static function replace($name, $existing, $callback)
	{
		if (empty(self::$events[$name]) OR ($key = array_search($existing, self::$events[$name], TRUE)) === FALSE)
		{
			// The existing event was not found
			return FALSE;
		}

		if ( ! in_array($callback, self::$events[$name], TRUE))
		{
			// Replace the exisiting event with the new event
			self::$events[$name][$key] = $callback;
		}
		else
		{
			// Remove the existing event from the queue
			unset(self::$events[$name][$key]);

			// Reset the array so the keys are ordered properly
			self::$events[$name] = array_values(self::$events[$name]);
		}

		return TRUE;
	}

	/**
	 * Get all callbacks for an event.
	 *
	 * @param   string  event name
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(self::$events[$name]) ? array() : self::$events[$name];
	}

	/**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string  event name
	 * @param   array   specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback === FALSE)
		{
			// Clear all events on this name
			self::$events[$name] = array();
		}
		elseif (isset(self::$events[$name]) AND ($key = array_search($callback, self::$events[$name], TRUE)) !== FALSE)
		{
			// Remove the event
			unset(self::$events[$name][$key]);

			// Reset the array so the keys are ordered properly
			self::$events[$name] = array_values(self::$events[$name]);
		}
	}

	/**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   event name
	 * @param   mixed    data that will be passed to each callback
	 * @return  mixed
	 */
	public static function run($name, $data = NULL)
	{
		if ( ! empty(self::$events[$name]))
		{
			$callbacks = self::get($name);

			foreach ($callbacks as $callback)
			{
				$data = call_user_func($callback, $data);
			}

			// The event has been run!
			self::$has_run[$name] = $name;
		}

		return $data;
	}

	/**
	 * Check if a given event has been run.
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
	public static function has_run($name)
	{
		return isset(self::$has_run[$name]);
	}

} // End Event