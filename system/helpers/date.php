<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Date Class
 *
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/helpers/date.html
 */
class date {

	/**
	 * Returns the offset (in seconds) between two time zones
	 *
	 * @access public
	 * @see    http://php.net/timezones
	 * @param  string  PHP supported time zone
	 * @param  string  PHP supported time zone, or none, for the current time zone
	 * @return integer
	 */
	public static function offset($one, $two = TRUE)
	{
		// Create timezone objects
		$one = new DateTimeZone((string) $one);
		$two = new DateTimeZone(($two === TRUE) ? date_default_timezone_get() : (string) $two);

		// Create datetime objects from timezones
		$date_one = new DateTime('now', $one);
		$date_two = new DateTime('now', $two);

		return ($one->getOffset($date_two) - $two->getOffset($date_two));
	}

	/**
	 * Number of seconds in a minute
	 *
	 * This function returns a mirrored, eg: foo=foo, array of each second in a
	 * minute, jumping by $step. Any step from 1 to 30 can be used.
	 *
	 * @access public
	 * @param  integer
	 * @return array
	 */
	public static function seconds($step = 1)
	{
		$step = (int) $step;
		$vals = array();

		for ($i = $step; $i < 61; $i += $step)
		{
			$vals[$i] = $i;
		}

		return $vals;
	}

	/**
	 * Number of minutes in an hour
	 *
	 * @access public
	 * @param  integer
	 * @return array
	 */
	public static function minutes($step = 5)
	{
		// Because there are the same number of minutes as seconds in this set,
		// we choose to re-use seconds(), rather than creating an entirely new
		// function. Shhhh, it's cheating! ;) There are several more of these
		// in the following methods.
		return self::seconds($step);
	}

	/**
	 * Number of hours in a day
	 *
	 * @access public
	 * @param  integer
	 * @return array
	 */
	public static function hours($step = 1, $long = FALSE)
	{
		$step = (int) $step;
		$size = ($long == TRUE) ? 25 : 13;

		$vals = array();
		for ($i = $step; $i < $size; $i += $step)
		{
			$vals[$i] = $i;
		}

		return $vals;
	}

	/**
	 * Returns AM or PM, based on a given hour
	 *
	 * @access public
	 * @param  integer The hour, between 00 and 24
	 * @return string
	 */
	public static function ampm($hour)
	{
		return ((int) $hour > 11) ? 'PM' : 'AM';
	}

	/**
	 * Number of days in month
	 *
	 * This function can optionally be passed a year as the second parameter
	 * to use a year other than the current year.
	 *
	 * @access public
	 * @param  integer
	 * @return array
	 */
	public static function days($month, $year = FALSE)
	{
		static $months;

		// Always integers
		$month = (int) $month;
		$year  = (int) $year;

		// Use the current year by default
		$year  = ($year == FALSE) ? date('Y') : $year;

		// We use caching for months, because time functions are used
		if (empty($months[$year][$month]))
		{
			// Initialize the days array
			$months[$year][$month] = array();

			// Use date to find the number of days in the given month
			$total = date('t', mktime(1, 0, 0, $month, 1, $year));

			// Add the days
			for ($i = 1; $i < $total; $i++)
			{
				$months[$year][$month][$i] = $i;
			}
		}

		return $months[$year][$month];
	}

	/**
	 * Number of months in a year
	 *
	 * @access public
	 * @param  integer
	 * @return array
	 */
	public static function months()
	{
		return self::hours(1, FALSE);
	}

	/**
	 * Returns an array of years between a starting and ending year
	 *
	 * By default, this will return the current year +/- 5 years
	 *
	 * @access public
	 * @param  integer starting year
	 * @param  integer ending year
	 * @return array
	 */
	public static function years($start = FALSE, $end = FALSE)
	{
		$start = ($start == FALSE) ? date('Y') - 5 : (int) $start;
		$end   = ($end   == FALSE) ? date('Y') + 5 : (int) $end;

		$vals = array();
		for ($i = $start; $i < ($end + 1); $i++)
		{
			$vals[$i] = $i;
		}

		return $vals;
	}

	/**
	 * Returns time difference between two timestamps
	 *
	 * @access public
	 * @param  integer
	 * @param  integer
	 * @param  string
	 * @return mixed
	 */
	public static function timespan($time1, $time2 = FALSE, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		// Calculate timespan (in seconds)
		$time1 = (int) max(0, $time1);
		$time2 = (int) ($time2 === FALSE) ? time() : max(0, $time2);
		$timespan = abs($time1 - $time2);

		// Array with the output formats
		$output = preg_split('/[\s,]+/', strtolower($output));
		$output = array_combine($output, $output);

		// Years ago
		if (isset($output['years']))
		{
			// 60 * 60 * 24 * 365
			$year = 31536000;
			$timediff['years'] = (int) floor($timespan / $year);
			$timespan -= $timediff['years'] * $year;
		}

		// Months ago
		if (isset($output['months']))
		{
			// 60 * 60 * 24 * 30
			$month = 2592000;
			$timediff['months'] = (int) floor($timespan / $month);
			$timespan -= $timediff['months'] * $month;
		}

		// Weeks ago
		if (isset($output['weeks']))
		{
			// 60 * 60 * 24 * 7
			$week = 604800;
			$timediff['weeks'] = (int) floor($timespan / $week);
			$timespan -= $timediff['weeks'] * $week;
		}

		// Days ago
		if (isset($output['days']))
		{
			// 60 * 60 * 24
			$day = 86400;
			$timediff['days'] = (int) floor($timespan / $day);
			$timespan -= $timediff['days'] * $day;
		}

		// Hours ago
		if (isset($output['hours']))
		{
			// 60 * 60
			$hour = 3600;
			$timediff['hours'] = (int) floor($timespan / $hour);
			$timespan -= $timediff['hours'] * $hour;
		}

		// Minutes ago
		if (isset($output['minutes']))
		{
			// 60
			$minute = 60;
			$timediff['minutes'] = (int) floor($timespan / $minute);
			$timespan -= $timediff['minutes'] * $minute;
		}

		// Seconds ago
		if (isset($output['seconds']))
		{
			$timediff['seconds'] = $timespan;
		}

		// Invalid output formats string
		if ( ! isset($timediff))
			return FALSE;

		// If only one output format was asked, don't put it in an array
		if (count($timediff) == 1)
			return current($timediff);

		// Return array
		return $timediff;
	}

} // End date Class