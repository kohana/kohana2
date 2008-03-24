<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Calendar event observer class.
 *
 * $Id$
 *
 * @package    Calendar
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Calendar_Event_Core extends Event_Observer {

	// Rendering conditions
	protected $conditions = array();

	// Cell classes
	protected $classes = array();

	// Cell output
	protected $output = '';

	public function condition($key, $value)
	{
		if ($value === NULL)
		{
			unset($this->conditions[$key]);
		}
		else
		{
			$this->conditions[$key] = $value;
		}

		return $this;
	}

	public function add_class($class)
	{
		$this->classes[$class] = $class;

		return $this;
	}

	public function remove_class($class)
	{
		unset($this->classes[$class]);

		return $this;
	}

	public function output($str)
	{
		$this->output = $str;

		return $this;
	}

	public function notify($date)
	{
		// Split the date and current status
		list ($date, $current) = $date;

		// Date conditionals
		$date_value = array
		(
			'day'      => (int) date('j', $date),
			'week_day' => (int) date('w', $date),
			'month'    => (int) date('n', $date),
			'year'     => (int) date('Y', $date),
			'current'  => (bool) $current,
		);

		// First and last day date conditionals
		$date_value['first_day'] = ($date_value['day'] === 1);
		$date_value['last_day'] = ($date_value['day'] === (int) date('t', $date));

		foreach ($this->conditions as $key => $value)
		{
			if ($value !== $date_value[$key])
				return FALSE;
		}

		$this->caller->add_data(array
		(
			'classes' => $this->classes,
			'output'  => $this->output,
		));
	}

} // End Calendar Event