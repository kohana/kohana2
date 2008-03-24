<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana event subject. Uses the SPL observer pattern.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Event_Subject implements SplSubject {

	// Attach of subject listeners
	protected $listeners = array();

	/**
	 * Attach an observer to the Eventable object.
	 *
	 * @chainable
	 * @param   object  Event_Observer
	 * @return  object
	 */
	public function attach(SplObserver $obj)
	{
		if ( ! ($obj instanceof Event_Observer))
			throw new Kohana_Exception('eventable.invalid_observer');

		// Add a new listener
		$this->listeners[spl_object_hash($obj)] = $obj;

		return $this;
	}

	/**
	 * Detach an observer from the the Eventable object.
	 *
	 * @chainable
	 * @param   object  Event_Observer
	 * @return  object
	 */
	public function detach(SplObserver $obj)
	{
		if ( ! ($obj instanceof Event_Observer))
			throw new Kohana_Exception('eventable.invalid_observer');

		// Notify the observer of removal
		$this->listeners[spl_object_hash($obj)]->remove();

		// Remove the listener
		unset($this->listeners[spl_object_hash($obj)]);

		return $this;
	}

	/**
	 * Notify all attached observers of a new message.
	 *
	 * @chainable
	 * @param   mixed   message string, object, or array
	 * @return  object
	 */
	public function notify($message)
	{
		foreach ($this->listeners as $obj)
		{
			$obj->notify($message);
		}

		return $this;
	}

} // End Eventable