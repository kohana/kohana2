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

	public function notify($data)
	{
		// Split the date and current status
		list ($month, $day, $year, $week, $current) = $data;

		// Get a timestamp for the day
		$timestamp = mktime(0, 0, 0, $month, $day, $year);

		// Date conditionals
		$condition = array
		(
			'timestamp'   => (int) $timestamp,
			'day'         => (int) date('j', $timestamp),
			'week'        => (int) $week,
			'month'       => (int) date('n', $timestamp),
			'year'        => (int) date('Y', $timestamp),
			'day_of_week' => (int) date('w', $timestamp),
			'current'     => (bool) $current,
		);

		if ($condition['current'] === TRUE)
		{
			if (isset($this->conditions['weekend']))
			{
				// Weekday vs weekend
				$condition['weekend'] = ($condition['day_of_week'] === 0 OR $condition['day_of_week'] === 6);
			}

			if (isset($this->conditions['first_day']))
			{
				// First day of month
				$condition['first_day'] = ($condition['day'] === 1);
			}

			if (isset($this->conditions['last_day']))
			{
				// Last day of month
				$condition['last_day'] = ($condition['day'] === (int) date('t', $timestamp));
			}

			if (isset($this->conditions['occurrence']))
			{
				// Get the occurance of the current day
				$condition['occurrence'] = $this->day_occurrence($timestamp);
			}

			if (isset($this->conditions['last_occurrence']))
			{
				// Test if the next occurance of this date is next month
				$condition['last_occurrence'] = ((int) date('n', strtotime(date('Y/m/d', $timestamp).' +1 week')) !== $condition['month']);
			}
		}

		foreach ($this->conditions as $key => $value)
		{
			if (isset($condition[$key]) AND $condition[$key] !== $value)
				return FALSE;
		}

		$this->caller->add_data(array
		(
			'classes' => $this->classes,
			'output'  => $this->output,
		));
	}

	protected function day_occurrence($timestamp)
	{
		$month = date('m', $timestamp);
		$test = date('Y/m/d', $timestamp).' -%s week';

		$occurrence = 0;
		while(TRUE)
		{
			if (date('m', strtotime(sprintf($test, ++$occurrence))) !== $month)
			{
				return $occurrence;
			}
		}
	}

} // End Calendar Event