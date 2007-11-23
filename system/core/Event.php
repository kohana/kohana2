<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Event
 *  Process queuing/execution class. Allows an unlimited number of callbacks
 *  to be added to 'events'. Events can be run multiple times, and can also
 *  process event-specific data. By default, Kohana has several system events.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 *
 * About: system.setup
 *  Without modification to Kohana, it is not possible to attach to this event.
 *  Runs Kohana::setup() by default.
 *
 * About: system.ready
 *  Called immediately after hooks are loaded. This is the earliest attachable
 *  event. Nothing is attached to this event by default.
 *
 * About: system.routing
 *  Processes the URL and does routing. Runs Router::setup() by default.
 *
 * About: system.execute
 *  Controller locating and initialization. A controller object will be created,
 *  as an instance of Kohana. Calls Kohana::instance() by default.
 *
 * About: system.pre_controller
 *  Called within system.execute, after the controller file is loaded, but
 *  before an object is created.
 *
 * About: system.post_controller
 *  Called within system.execute, after the controller object is created.
 *  Kohana::instance() will return the controller at this point, and views can
 *  be loaded.
 *
 * About: system.send_headers
 *  Called just before the global output buffer is closed, before any content
 *  is displayed. Writing cookies is not possible after this point, and
 *  <Session> data will not be saved.
 *
 * About: system.display
 *  Displays the output that Kohana has generated. Views can be loaded, but
 *  headers have already been sent. The rendered output, Kohana::$output, can
 *  be manipulated.
 *
 * About: system.shutdown
 *  Last event to run, just before PHP starts to shut down. Calls Log::write()
 *  by default.
 */
final class Event {

	// Event callbacks
	private static $events = array();

	// Cache of events that have been run
	private static $has_run = array();

	// Data that can be processed during events
	public static $data;

	/**
	 * Method: add
	 *  Add a callback to an event queue.
	 *
	 * Parameters:
	 *  name     - event name
	 *  callback - <http://php.net/callback>
	 */
	public static function add($name, $callback)
	{
		if (empty($name) OR empty($callback))
			return FALSE;

		if ( ! isset(self::$events[$name]))
		{
			// Create an empty event if it is not yet defined
			self::$events[$name] = array();
		}

		if ( ! in_array($callback, self::$events[$name]))
		{
			// Add the event if it does not already exist in the queue
			self::$events[$name][] = $callback;
		}
	}

	/**
	 * Method: add_before
	 *  Add a callback to an event queue, before a given event.
	 *
	 * Parameters:
	 *  name     - event name
	 *  existing - existing event callback
	 *  callback - event callback
	 */
	public static function add_before($name, $existing, $callback)
	{
		if (empty($name) OR empty($existing) OR empty($callback))
			return FALSE;

		if (empty(self::$events[$name]) OR ($key = array_search($existing, self::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			self::add($name, $callback);
		}
		else
		{
			// Insert the event immediately before the existing event
			self::insert_event($name, $key, $callback);
		}

		return TRUE;
	}

	/**
	 * Method: add_after
	 *  Add a callback to an event queue, after a given event.
	 *
	 * Parameters:
	 *  name     - event name
	 *  existing - existing event callback
	 *  callback - event callback
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public static function add_after($name, $existing, $callback)
	{
		if (empty($name) OR empty($existing) OR empty($callback))
			return FALSE;

		if (empty(self::$events[$name]) OR ($key = array_search($existing, self::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			self::add($name, $callback);
		}
		else
		{
			// Insert the event immediately after the existing event
			self::insert_event($name, $key + 1, $callback);
		}

		return TRUE;
	}

	/**
	 * Method: insert_event
	 *  Inserts a new event at a specfic key location.
	 *
	 * Parameters:
	 *  name     - event name
	 *  key      - key to insert new event at
	 *  callback - event callback
	 */
	private static function insert_event($name, $key, $callback)
	{
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
	}

	/**
	 * Method: replace
	 *  Replaces an event with another event.
	 *
	 * Parameters:
	 *  name     - event name
	 *  existing - event to replace
	 *  callback - new callback
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public static function replace($name, $existing, $callback)
	{
		if (empty($name) OR empty($existing) OR empty($callback) OR empty(self::$events[$name]))
			return FALSE;

		// If the existing event does not exist, or the 
		if (($key = array_search($existing, self::$events[$name])) === FALSE)
			return FALSE;

		if ( ! in_array($callback, self::$events[$name]))
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
	 * Method: get
	 *  Get all callbacks for an event.
	 *
	 * Parameters:
	 *  name - event name
	 *
	 * Returns:
	 *  Array of callbacks.
	 */
	public static function get($name)
	{
		return empty(self::$events[$name]) ? array() : self::$events[$name];
	}

	/**
	 * Method: clear
	 *  Clear some or all callbacks from an event.
	 *
	 * Parameters:
	 *  name     - event name
	 *  callback - specific callback to remove, FALSE for all callbacks
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback == FALSE)
		{
			self::$events[$name] = array();
		}
		elseif (isset(self::$events[$name]))
		{
			// Loop through each of the event callbacks and compare it to the
			// callback requested for removal. The callback is removed if it
			// matches.
			foreach(self::$events[$name] as $i => $event_callback)
			{
				if ($callback === $event_callback)
				{
					unset(self::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * Method: run
	 *  Execute all of the callbacks attached to an event.
	 *
	 * Parameters:
	 *  name - event name
	 *  data - data can be processed as Event::$data by the callbacks
	 */
	public static function run($name, & $data = NULL)
	{
		if ($name == FALSE OR empty(self::$events[$name]))
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

		// The event has been run!
		self::$has_run[$name] = $name;
	}

	/**
	 * Method: has_run
	 *  Check if a given event has been run.
	 *
	 * Parameters:
	 *  name - event name
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	public static function has_run($name)
	{
		return isset(self::$has_run[$name]);
	}

} // End Event