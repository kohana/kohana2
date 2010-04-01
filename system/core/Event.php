<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The Event core library provides process queuing and execution
 * functionality. The library llows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple times, and can
 * also process event-specific data.
 *
 * By default, Kohana has several system events.
 *
 * @link http://docs.kohanaphp.com/general/events
 *
 * ##### What is an Event?
 * Kohana events consist of a unique name and a callback. By default,
 * there are several events defined by Kohana. Names are freeform, but
 * the idiomatic convention is to use namespaced dot-notation, e.g:
 * `prefix.name`. All pre-defined events are prefixed as `system.name`, e.g:
 * `system.post_controller`.
 *
 * For a technical overview of events, please see
 * [Event_handler](http://en.wikipedia.org/wiki/Event_handler "Event_handler") and
 * [Event_loop](http://en.wikipedia.org/wiki/Event_loop "Event_loop").
 *
 * Kohana stores events in queues, as opposed to
 * stacks. This means that, by default, new events will be processed
 * after existing events.
 *
 * ##### Using the Event Library
 *
 * All of the methods defined by the Event class are static, there is
 * no need to instantiate the class as an object.
 *
 * ##### A Demonstrative Example
 *
 *     class Test_Controller extends Controller
 *     {
 *			public function __construct()
 *			{
 *				parent::__construct();
 *				
 *				Event::add('system.display', array($this, '_pre_render'));
 *			}
 *			
 *			public function index()
 *			{
 *				echo Kohana::debug("Some text, or do some view instantiation and echoing here");
 *			}
 *			
 *			public function _pre_render()
 *			{
 *				echo Kohana::debug("This renders before the controller action outputs anything...");
 *			}
 *     }
 * 
 *     // Output:
 *     (string) This renders before the controller action outputs anything...
 *     
 *     (string) Some text, or do some view instantiation and echoing here
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 * @link       http://docs.kohanaphp.com/general/events
 */
abstract class Event_Core {

	// Event callbacks
	protected static $events = array();

	// Cache of events that have been run
	protected static $has_run = array();

	// Data that can be processed during events
	public static $data;

	/**
	 * This method adds a function callback to the queue of the
	 * specified event. If an event name provided to the first
	 * function argument already exists (such as a system event, or
	 * one previously created), the callback will simply be added to
	 * its queue; if the event name does not already exist, it is
	 * created and the callback is added.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to which the provided callback will
	 * be added.
	 *
	 * The second function argument takes as its value either a
	 * string or array denoting the callback to be used. The syntax is
	 * identical to the use of [call_user_func()](http://us.php.net/manual/en/function.call-user-func.php "call_user_func()") and co.
	 *
	 * @link http://us.php.net/callback
	 *
	 * The third function argument takes as its value a boolean and
	 * enforces unique event callbacks.
	 *
	 * ###### Example
	 *     
	 *     // Adding a simple function
	 *     Event::add('system.post_controller', 'my_func');
	 *     
	 *     // Adding an object method
	 *     Event::add('system.post_controller', array($this,      'my_method'));
	 *     Event::add('system.post_controller', array('my_class', 'my_method'));
	 *     
	 *     // Adding a static class method
	 *     Event::add('system.post_controller', 'my_class::my_static_method');
	 *     
	 *     // Adding a relative static class method (PHP 5.3 only)
	 *     Event::add('system.post_controller', array('my_class', 'parent::my_static_method));
	 * 
	 * @param   string  $name     Event name
	 * @param   mixed   $callback A qualified callback
	 * @param   boolean $unique   Prevent duplicates
	 * @return  boolean
	 */
	public static function add($name, $callback, $unique = FALSE)
	{
		if ( ! isset(Event::$events[$name]))
		{
			// Create an empty event if it is not yet defined
			Event::$events[$name] = array();
		}
		elseif ($unique AND in_array($callback, Event::$events[$name], TRUE))
		{
			// The event already exists
			return FALSE;
		}

		// Add the event
		Event::$events[$name][] = $callback;

		return TRUE;
	}

	/**
	 * This method adds a given callback to the specified event queue *before*
	 * a callback.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to which the provided callback will
	 * be added.
	 *
	 * The second function argument takes as its value either a string
	 * or array denoting the existing callback in the event queue.
	 * 
	 * The third function argument takes as its value either a
	 * string or array denoting the callback to be used. The syntax is
	 * identical to the use of [call_user_func()](http://us.php.net/manual/en/function.call-user-func.php "call_user_func()") and co.
	 *
	 * ###### Example
	 *     
	 *     // Adding a simple function
	 *     Event::add_before('system.post_controller', 'existing_callback', 'new_callback');
	 *     
	 * @param   string   $name     Event name
	 * @param   mixed    $existing Callback to be prefixed to
	 * @param   mixed    $callback Qualified callback to be prefixed
	 * @return  boolean
	 */
	public static function add_before($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return Event::add($name, $callback);
		}
		else
		{
			// Insert the event immediately before the existing event
			return Event::insert_event($name, $key, $callback);
		}
	}

	/**
	 * This method adds a given callback to the specified event queue *after*
	 * a callback.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to which the provided callback will
	 * be added.
	 *
	 * The second function argument takes as its value either a string
	 * or array denoting the existing callback in the event queue.
	 * 
	 * The third function argument takes as its value either a
	 * string or array denoting the callback to be used. The syntax is
	 * identical to the use of [call_user_func()](http://us.php.net/manual/en/function.call-user-func.php "call_user_func()") and co.
	 *
	 * ###### Example
	 *     
	 *     // Adding a simple function
	 *     Event::add_after('system.post_controller', 'existing_callback', 'new_callback');
	 *     
	 * @param   string   $name     Event name
	 * @param   mixed    $existing Callback to be affixed to
	 * @param   mixed    $callback Qualified callback to be affixed
	 * @return  boolean
	 */
	public static function add_after($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name])) === FALSE)
		{
			// Just add the event if there are no events
			return Event::add($name, $callback);
		}
		else
		{
			// Insert the event immediately after the existing event
			return Event::insert_event($name, $key + 1, $callback);
		}
	}

	/**
	 * Inserts a new event at a specfic key location.
	 *
	 * [!!] This method is private and is used internally by the class.
	 *
	 * @param   string   $name     Event name
	 * @param   integer  $key      Key to insert new event at
	 * @param   mixed    $callback Event callback
	 * @return  void
	 */
	private static function insert_event($name, $key, $callback)
	{
		if (in_array($callback, Event::$events[$name], TRUE))
			return FALSE;

		// Add the new event at the given key location
		Event::$events[$name] = array_merge
		(
			// Events before the key
			array_slice(Event::$events[$name], 0, $key),
			// New event callback
			array($callback),
			// Events after the key
			array_slice(Event::$events[$name], $key)
		);

		return TRUE;
	}

	/**
	 * This method replaces an existing callback with a given callback.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue in which the existing callback is
	 * defined.
	 *
	 * The second function argument takes as its value either a string
	 * or array denoting the existing callback in the event queue.
	 * 
	 * The third function argument takes as its value either a
	 * string or array denoting the callback to be used. The syntax is
	 * identical to the use of [call_user_func()](http://us.php.net/manual/en/function.call-user-func.php "call_user_func()") and co.
	 *
	 * ###### Example
	 *     
	 *     // Replacing a callback with a simple function callback
	 *     Event::replace('system.post_controller', 'existing_callback', 'new_callback');
	 *     
	 * @param   string   $name     Event name
	 * @param   mixed    $existing Callback to be replaced
	 * @param   mixed    $callback Qualified superseding callback
	 * @return  boolean
	 */
	public static function replace($name, $existing, $callback)
	{
		if (empty(Event::$events[$name]) OR ($key = array_search($existing, Event::$events[$name], TRUE)) === FALSE)
			return FALSE;

		if ( ! in_array($callback, Event::$events[$name], TRUE))
		{
			// Replace the exisiting event with the new event
			Event::$events[$name][$key] = $callback;
		}
		else
		{
			// Remove the existing event from the queue
			unset(Event::$events[$name][$key]);

			// Reset the array so the keys are ordered properly
			Event::$events[$name] = array_values(Event::$events[$name]);
		}

		return TRUE;
	}

	/**
	 * This method retrieves all of the callbacks queued in the given
	 * event.
	 *
	 * ###### Example
	 *     
	 *     // Assuming this is executed within the default welcome.php controller's index method
	 *     echo Kohana::debug(Event::get('system.post_controller'));
	 *     
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [0] => Array
	 *             (
	 *                 [0] => Welcome_Controller Object
	 *                     (
	 *                         [template] => View Object
	 *                             (
	 *                                 [kohana_filename:protected] => /localhost/kohana/trunk/system/views/kohana/template.php
	 *                                 [kohana_filetype:protected] => .php
	 *                                 [kohana_local_data:protected] => Array
	 *                                     (
	 *                                     )
	 *                             )
	 *                         [auto_render] => 1
	 *                     )
	 *                 [1] => _render
	 *             )
	 *     )
	 *
	 * @param   string  $name Event name
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(Event::$events[$name]) ? array() : Event::$events[$name];
	}

	/**
	 * This method clears all or some of the callbacks from a given
	 * event.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to clear.
	 *
	 * The second function argument takes as its value either a
	 * qualified callback identifier or a boolean; given a boolean
	 * `FALSE` argument all callbacks will be cleared. Given a
	 * qualified callback identifier, only that callback will be
	 * cleared from the queue.
	 *
	 * ###### Example
	 *     
	 *     // Clears all callbacks from the system.post_controller event queue
	 *     Event::clear('system.post_controller');
	 *
	 *     // Clear a specific (simple function) callback
	 *     Event::clear('system.post_controller', 'my_func');
	 * 
	 * @param   string  $name     Event name
	 * @param   mixed   $callback Specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback === FALSE)
		{
			Event::$events[$name] = array();
		}
		elseif (isset(Event::$events[$name]))
		{
			// Loop through each of the event callbacks and compare it to the
			// callback requested for removal. The callback is removed if it
			// matches.
			foreach (Event::$events[$name] as $i => $event_callback)
			{
				if ($callback === $event_callback)
				{
					unset(Event::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * This method executes all of the callbacks in a given event
	 * queue.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to process.
	 *
	 * The second function argument takes as its value an array and is
	 * passed to the callbacks being executed.
	 *
	 * ###### Example
	 *     
	 *     Event::run('system.post_controller');
	 *
	 * @param   string   event name
	 * @param   array    data can be processed as Event::$data by the callbacks
	 * @return  void
	 */
	public static function run($name, & $data = NULL)
	{
		if ( ! empty(Event::$events[$name]))
		{
			// So callbacks can access Event::$data
			Event::$data =& $data;
			$callbacks  =  Event::get($name);

			foreach ($callbacks as $callback)
			{
				call_user_func_array($callback, array(&$data));
			}

			// Do this to prevent data from getting 'stuck'
			$clear_data = '';
			Event::$data =& $clear_data;
		}

		// The event has been run!
		Event::$has_run[$name] = $name;
	}

	/**
	 * This method validates if a given event queue has been
	 * processed.
	 *
	 * The first function argument takes as its value a string and is
	 * the name of the event queue to check.
	 *
	 * ###### Example
	 *     
	 *     // Run the events defined in our complete example
	 *     Event::run('system.display');
	 *     
	 *     // Has the event queue been processed?
	 *     echo Kohana::debug(Event::has_run('system.display'));
	 *     
	 *     // Output:
	 *     (string) This is renders before the controller action outputs anything...
	 *     
	 *     (boolean) true
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
	public static function has_run($name)
	{
		return isset(Event::$has_run[$name]);
	}

} // End Event