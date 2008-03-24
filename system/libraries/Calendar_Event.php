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

		// Tested conditions
		$tested = array();

		foreach ($condition as $key => $value)
		{
			// Test basic conditions first
			if (isset($this->conditions[$key]) AND $this->conditions[$key] !== $value)
				return FALSE;

			// Condition has been tested
			$tested[$key] = TRUE;
		}

		if (isset($this->conditions['weekend']))
		{
			// Weekday vs Weekend
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

		if (isset($this->conditions['easter']))
		{
			if ($condition['month'] === 3 OR $condition['month'] === 4)
			{
				// This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
				// Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
				// 1876. This algorithm has also been published in the 1922 book General Astronomy by
				// Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
				// 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus.

				/**
				 * @todo I imagine Geert will have a party with this one...
				 */
				$a = $condition['year'] % 19;
				$b = (int) ($condition['year'] / 100);
				$c = $condition['year'] % 100;
				$d = (int) ($b / 4);
				$e = $b % 4;
				$f = (int) (($b + 8) / 25);
				$g = (int) (($b - $f + 1) / 3);
				$h = (19 * $a + $b - $d - $g + 15) % 30;
				$i = (int) ($c / 4);
				$k = $c % 4;
				$l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
				$m = (int) (($a + 11 * $h + 22 * $l) / 451);
				$p = ($h + $l - 7 * $m + 114) % 31;

				$month = (int) (($h + $l - 7 * $m + 114) / 31);
				$day = $p + 1;

				$condition['easter'] = ($condition['month'] === $month AND $condition['day'] === $day);
			}
			else
			{
				// Easter can only happen in March or April
				$condition['easter'] = FALSE;
			}
		}

		if (isset($this->conditions['callback']))
		{
			// Use a callback to determine validity
			$condition['callback'] = call_user_func($this->conditions['callback'], $condition);
		}

		foreach (array_diff_key($this->conditions, $tested) as $key => $value)
		{
			// Test advanced conditions
			if ($condition[$key] !== $value)
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