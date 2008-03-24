<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana event observer. Uses the SPL observer pattern.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Event_Observer implements SplObserver {

	// Calling object
	protected $caller;

	/**
	 * Initializes a new observer and attaches the subject as the caller.
	 *
	 * @param   object  Event_Subject
	 * @return  void
	 */
	public function __construct(SplSubject $caller)
	{
		if ( ! ($caller instanceof Event_Subject))
			throw new Kohana_Exception('event.invalid_subject');

		// Set the caller
		$this->caller = $caller;
	}

	/**
	 * Updates the observer subject with a new caller.
	 *
	 * @chainable
	 * @param   object  Event_Subject
	 * @return  void
	 */
	public function update(SplSubject $caller)
	{
		if ( ! ($caller instanceof Event_Subject))
			throw new Kohana_Exception('event.invalid_subject');

		// Update the caller
		$this->caller = $caller;

		return $this;
	}

	/**
	 * Detaches this observer from the subject.
	 *
	 * @chainable
	 * @param   object  Event_Subject
	 * @return  void
	 */
	public function remove()
	{
		// Detach this observer from the caller
		$this->caller->detach($this);

		return $this;
	}

	/**
	 * Notify the observer of a new message. This function must be defined in
	 * all observers and must take exactly one parameter of any type.
	 *
	 * @param   mixed   message string, object, or array
	 * @return  void
	 */
	abstract public function notify($message);

} // End Event Observer