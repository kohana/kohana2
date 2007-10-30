<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: date
 *  Date helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class date {

	/*
	 * Method: offset
	 *  Returns the offset (in seconds) between two time zones. See
	 *  <http://php.net/timezones> for a list of supported time zones.
	 *
	 * Parameters:
	 *  remote - timezone that to find the offset of
	 *  local  - timezone used as the baseline
	 *
	 * Returns:
	 *  Number of seconds between the remote and local timezones.
	 */
	public static function offset($remote, $local = TRUE)
	{
		static $offsets;

		// Default values
		$remote = (string) $remote;
		$local  = ($local === TRUE) ? date_default_timezone_get() : (string) $local;

		// Cache key name
		$cache = $remote.$local;

		if (empty($offsets[$cache]))
		{
			// Create timezone objects
			$remote = new DateTimeZone($remote);
			$local  = new DateTimeZone($local);

			// Create date objects from timezones
			$time_there = new DateTime('now', $remote);
			$time_here  = new DateTime('now', $local);

			// Find the offset
			$offsets[$cache] = $remote->getOffset($time_there) - $local->getOffset($time_here);
		}

		return $offsets[$cache];
	}

	/*
	 * Method: seconds
	 *  Number of seconds in a minute, incrementing by a step.
	 *
	 * Parameters:
	 *  step - amount to increment each step by, 1 to 30
	 *
	 * Returns:
	 *  A mirrored (foo => foo) array from 1-60.
	 */
	public static function seconds($step = 1, $start = 0, $end = 60)
	{
		static $seconds;

		// Always integer
		$step = (int) $step;

		if (empty($seconds[$step]))
		{
			$seconds[$step] = array();

			for ($i = $start; $i < $end; $i += $step)
			{
				$seconds[$step][$i] = $i;
			}
		}

		return $seconds[$step];
	}

	/*
	 * Method: minutes
	 *  Number of minutes in an hour, incrementing by a step.
	 *
	 * Parameters:
	 *  step - amount to increment each step by, 1 to 30
	 *
	 * Returns:
	 *  A mirrored (foo => foo) array from 1-60.
	 */
	public static function minutes($step = 5)
	{
		// Because there are the same number of minutes as seconds in this set,
		// we choose to re-use seconds(), rather than creating an entirely new
		// function. Shhhh, it's cheating! ;) There are several more of these
		// in the following methods.
		return self::seconds($step);
	}

	/*
	 * Method: hours
	 *  Number of hours in a day.
	 *
	 * Parameters:
	 *  step - amount to increment each step by
	 *  long - use 24-hour time
	 *
	 * Returns:
	 *  A mirrored (foo => foo) array from 1-12 or 1-24.
	 */
	public static function hours($step = 1, $long = FALSE, $start = 0)
	{
		static $hours;

		// Default values
		$step = (int) $step;
		$long = (bool) $long;

		// Caching key
		$cache = ($long == TRUE) ? '24hr' : '12hr';

		if (empty($hours[$cache][$step]))
		{
			$hours[$cache][$step] = array();

			// 24-hour time has 24 hours, instead of 12
			$size = ($long == TRUE) ? 24 : 12;

			for ($i = $start; $i < $size; $i += $step)
			{
				$hours[$cache][$step][$i] = $i;
			}
		}

		return $hours[$cache][$step];
	}

	/*
	 * Method: ampm
	 *  Returns AM or PM, based on a given hour.
	 *
	 * Parameters:
	 *  hour - number of the hour
	 *
	 * Returns:
	 *  AM or PM.
	 */
	public static function ampm($hour)
	{
		// Always integer
		$hour = (int) $hour;

		return ($hour > 11) ? 'PM' : 'AM';
	}

	/*
	 * Method: days
	 *  Number of days in month.
	 *
	 * Parameters:
	 *  month - number of month
	 *  year  - number of year to check month, defaults to the current year
	 *
	 * Returns:
	 *  A mirrored (foo => foo) array of the days.
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
			$months[$year][$month] = array();

			// Use date to find the number of days in the given month
			$total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

			for ($i = 1; $i < $total; $i++)
			{
				$months[$year][$month][$i] = $i;
			}
		}

		return $months[$year][$month];
	}

	/*
	 * Method: months
	 *  Number of months in a year
	 *
	 * Returns:
	 *  A mirrored (foo => foo) array from 1-12.
	 */
	public static function months()
	{
		return self::hours(1, FALSE);
	}

	/*
	 * Method: years
	 *  Returns an array of years between a starting and ending year. Uses the
	 *  current year +/- 5 as the max/min.
	 *
	 * Parameters:
	 *  start - starting year
	 *  end   - ending year
	 *
	 * Returns:
	 *  A mirrored array of years between start and end.
	 */
	public static function years($start = FALSE, $end = FALSE)
	{
		static $years;

		// Default values
		$start = ($start == FALSE) ? date('Y') - 5 : (int) $start;
		$end   = ($end   == FALSE) ? date('Y') + 5 : (int) $end;

		// Cache key
		$cache = $start.$end;

		if (empty($years[$cache]))
		{
			$years[$cache] = array();

			// Add one, so that "less than" works
			$end += 1;

			for ($i = $start; $i < $end; $i++)
			{
				$years[$cache][$i] = $i;
			}
		}

		return $years[$cache];
	}

	/*
	 * Method:
	 *  Returns time difference between two timestamps, in human readable format.
	 *
	 * Parameters:
	 *  time1  - timestamp
	 *  time2  - timestamp, defaults to the current time
	 *  output - formatting string
	 *
	 * Returns:
	 *  A human-readable description of the time span.
	 */
	public static function timespan($time1, $time2 = FALSE, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		// Default values
		$time1  = max(0, (int) $time);
		$time2  = ($time2 === FALSE) ? time() : max(0, (int) $time2);

		// Calculate timespan (seconds)
		$timespan = abs($time1 - $time2);

		// Array with the output formats
		$output = preg_split('/[\s,]+/', strtolower((string) $output));
		$output = array_combine($output, $output);

		// Array of diff values
		$timediff = array();

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
		if (empty($timediff))
			return FALSE;

		// If only one output format was asked, don't put it in an array
		if (count($timediff) == 1)
			return current($timediff);

		// Return array
		return $timediff;
	}

} // End date