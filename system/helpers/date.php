<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The Date helper assists in formating dates and times allowing for addition
 * and conversion between different formats.
 *
 * The functions that return mirrored arrays are very useful when combined
 * with the [form::dropdown] helper.
 *
 * ###### Example
 *
 *     echo Kohana::debug(form::dropdown('year', date::years()));
 *
 *     // Output (output has been truncated for display purposes):
 *     (string) <select name="year" >
 *     <option value="2005">2005</option>
 *     ...
 *     <option value="2014">2014</option>
 *     <option value="2015">2015</option>
 *     </select>
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class date_Core {

	/**
	 * Converts a UNIX timestamp to DOS format.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::unix2dos(mktime(0, 0, 0, 31, 10, 1987)));
	 *
	 *     // Output:
	 *     (integer) 317325312
	 *
	 * @param   integer  $timestamp  UNIX timestamp
	 * @return  integer
	 */
	public static function unix2dos($timestamp = FALSE)
	{
		$timestamp = ($timestamp === FALSE) ? getdate() : getdate($timestamp);

		if ($timestamp['year'] < 1980)
		{
			return (1 << 21 | 1 << 16);
		}

		$timestamp['year'] -= 1980;

		// What voodoo is this? I have no idea... Geert can explain it though,
		// and that's good enough for me.
		return ($timestamp['year']    << 25 | $timestamp['mon']     << 21 |
		        $timestamp['mday']    << 16 | $timestamp['hours']   << 11 |
		        $timestamp['minutes'] << 5  | $timestamp['seconds'] >> 1);
	}

	/**
	 * Converts a DOS timestamp to UNIX format.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::dos2unix(317325312));
	 *
	 *     // Output:
	 *     (integer) 616046400
	 *
	 * @param   integer  $timestamp   DOS timestamp
	 * @return  integer
	 */
	public static function dos2unix($timestamp = FALSE)
	{
		$sec  = 2 * ($timestamp & 0x1f);
		$min  = ($timestamp >>  5) & 0x3f;
		$hrs  = ($timestamp >> 11) & 0x1f;
		$day  = ($timestamp >> 16) & 0x1f;
		$mon  = ($timestamp >> 21) & 0x0f;
		$year = ($timestamp >> 25) & 0x7f;

		return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
	}

	/**
	 * Returns the offset (in seconds) between two time zones.
	 * List of [supported timezones](http://php.net/timezones).
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::offset('EST', 'PST'));
	 *
	 *     // Output:
	 *     (integer) 10800  // 3 hours
	 *
	 * @param   string    $remote  Timezone to find the offset of
	 * @param   mixed     $local   Timezone used as the baseline
	 * @param   string    $when    Time at which to calculate
	 * @return  integer
	 */
	public static function offset($remote, $local = TRUE, $when = 'now')
	{
		if ($local === TRUE)
		{
			$local = date_default_timezone_get();
		}

		// Create timezone objects
		$remote = new DateTimeZone($remote);
		$local  = new DateTimeZone($local);

		// Create date objects from timezones
		$time_there = new DateTime($when, $remote);
		$time_here  = new DateTime($when, $local);

		// Find the offset
		return $remote->getOffset($time_there) - $local->getOffset($time_here);
	}

	/**
	 * Number of seconds in a minute, incrementing by a step.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::seconds(30));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [0] => 00
	 *         [30] => 30
	 *     )
	 *
	 * @param   integer  $step   Amount to increment each step by, 1 to 30
	 * @param   integer  $start  Start value
	 * @param   integer  $end    End value
	 * @return  array    A mirrored (foo => foo) array from 1-60.
	 */
	public static function seconds($step = 1, $start = 0, $end = 60)
	{
		// Always integer
		$step = (int) $step;

		$seconds = array();

		for ($i = $start; $i < $end; $i += $step)
		{
			$seconds[$i] = ($i < 10) ? '0'.$i : $i;
		}

		return $seconds;
	}

	/**
	 * Number of minutes in an hour, incrementing by a step.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::minutes(30));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [0] => 00
	 *         [30] => 30
	 *     )
	 *
	 * @param   integer  $step  Amount to increment each step by, 1 to 30
	 * @return  array    A mirrored (foo => foo) array from 1-60.
	 */
	public static function minutes($step = 5)
	{
		// Because there are the same number of minutes as seconds in this set,
		// we choose to re-use seconds(), rather than creating an entirely new
		// function. Shhhh, it's cheating! ;) There are several more of these
		// in the following methods.
		return date::seconds($step);
	}

	/**
	 * Number of hours in a day, incrementing by a step.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::hours(3));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [1] => 1
	 *         [4] => 4
	 *         [7] => 7
	 *         [10] => 10
	 *     )
	 *
	 * @param   integer  $step   Amount to increment each step by
	 * @param   boolean  $long   Use 24-hour time
	 * @param   integer  $start  The hour to start at
	 * @return  array    A mirrored (foo => foo) array from start-12 or start-23.
	 */
	public static function hours($step = 1, $long = FALSE, $start = NULL)
	{
		// Default values
		$step = (int) $step;
		$long = (bool) $long;
		$hours = array();

		// Set the default start if none was specified.
		if ($start === NULL)
		{
			$start = ($long === FALSE) ? 1 : 0;
		}

		$hours = array();

		// 24-hour time has 24 hours, instead of 12
		$size = ($long === TRUE) ? 23 : 12;

		for ($i = $start; $i <= $size; $i += $step)
		{
			$hours[$i] = $i;
		}

		return $hours;
	}

	/**
	 * Returns AM or PM, based on a given hour.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::ampm(1));
	 *     echo Kohana::debug(date::ampm(date('G')));
	 *
	 *     // Output:
	 *     (string) AM
	 *     (string) PM
	 *
	 * @param   integer  $hour   number of the hour
	 * @return  string
	 */
	public static function ampm($hour)
	{
		// Always integer
		$hour = (int) $hour;

		return ($hour > 11) ? 'PM' : 'AM';
	}

	/**
	 * Adjusts a non-24-hour number into a 24-hour number.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::adjust(2, 'am'));
	 *     echo Kohana::debug(date::adjust(2, 'pm'));
	 *
	 *     // Output:
	 *     (string) 02
	 *     (string) 14
	 *
	 * @param   integer  $hour  Hour to adjust
	 * @param   string   $ampm  AM or PM
	 * @return  string
	 */
	public static function adjust($hour, $ampm)
	{
		$hour = (int) $hour;
		$ampm = strtolower($ampm);

		switch ($ampm)
		{
			case 'am':
				if ($hour == 12)
					$hour = 0;
			break;
			case 'pm':
				if ($hour < 12)
					$hour += 12;
			break;
		}

		return sprintf('%02s', $hour);
	}

	/**
	 * Counts the number of days there are in a specific month of a specific year
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::days(2, 2010));
	 *     echo Kohana::debug(date::days(3, 2010));
	 *
	 *     // Output (output has been truncated for display purposes):
	 *     (array) Array
	 *     (
	 *         [1] => 1
	 *         [2] => 2
	 *         ...
	 *         [28] => 28
	 *     )
	 *     (array) Array
	 *     (
	 *         [1] => 1
	 *         [2] => 2
	 *         ...
	 *         [31] => 31
	 *     )
	 *
	 * @param   integer  $month   Number of month
	 * @param   integer  $year    Number of year to check month, defaults to the current year
	 * @return  array    A mirrored (foo => foo) array of the days.
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

	/**
	 * Number of months in a year
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::months());
	 *
	 *     // Output (output has been truncated for display purposes):
	 *     (array) Array
	 *     (
	 *         [1] => 1
	 *         ...
	 *         [12] => 12
	 *     )
	 *
	 * @return  array  A mirrored (foo => foo) array from 1-12.
	 */
	public static function months()
	{
		return date::hours();
	}

	/**
	 * Returns an array of years between a starting and ending year.
	 * Uses the current year +/- 5 as the max/min.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::years());
	 *
	 *     // Output (output has been truncated for display purposes):
	 *     (array) Array
	 *     (
	 *         [2005] => 2005
	 *         [2006] => 2006
	 *         ...
	 *         [2014] => 2014
	 *         [2015] => 2015
	 *     )
	 *
	 * @param   integer  $start  Starting year
	 * @param   integer  $end    Ending year
	 * @return  array
	 */
	public static function years($start = FALSE, $end = FALSE)
	{
		// Default values
		$start = ($start === FALSE) ? date('Y') - 5 : (int) $start;
		$end   = ($end   === FALSE) ? date('Y') + 5 : (int) $end;

		$years = array();

		// Add one, so that "less than" works
		$end += 1;

		for ($i = $start; $i < $end; $i++)
		{
			$years[$i] = $i;
		}

		return $years;
	}

	/**
	 * Returns time difference between two timestamps, in human readable format.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::timespan(time(), strtotime('2 year ago')));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [years] => 2
	 *         [months] => 0
	 *         [weeks] => 0
	 *         [days] => 0
	 *         [hours] => 12
	 *         [minutes] => 22
	 *         [seconds] => 28
	 *     )
	 *
	 * @param   integer       $time1    Timestamp
	 * @param   integer       $time2    Timestamp, defaults to the current time
	 * @param   string        $output   Formatting string
	 * @return  mixed
	 */
	public static function timespan($time1, $time2 = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		// Array with the output formats
		$output = preg_split('/[^a-z]+/', strtolower((string) $output));

		// Invalid output
		if (empty($output))
			return FALSE;

		// Make the output values into keys
		extract(array_flip($output), EXTR_SKIP);

		// Default values
		$time1  = max(0, (int) $time1);
		$time2  = empty($time2) ? time() : max(0, (int) $time2);

		// Calculate timespan (seconds)
		$timespan = abs($time1 - $time2);

		// All values found using Google Calculator.
		// Years and months do not match the formula exactly, due to leap years.

		// Years ago, 60 * 60 * 24 * 365
		isset($years) and $timespan -= 31556926 * ($years = (int) floor($timespan / 31556926));

		// Months ago, 60 * 60 * 24 * 30
		isset($months) and $timespan -= 2629744 * ($months = (int) floor($timespan / 2629743.83));

		// Weeks ago, 60 * 60 * 24 * 7
		isset($weeks) and $timespan -= 604800 * ($weeks = (int) floor($timespan / 604800));

		// Days ago, 60 * 60 * 24
		isset($days) and $timespan -= 86400 * ($days = (int) floor($timespan / 86400));

		// Hours ago, 60 * 60
		isset($hours) and $timespan -= 3600 * ($hours = (int) floor($timespan / 3600));

		// Minutes ago, 60
		isset($minutes) and $timespan -= 60 * ($minutes = (int) floor($timespan / 60));

		// Seconds ago, 1
		isset($seconds) and $seconds = $timespan;

		// Remove the variables that cannot be accessed
		unset($timespan, $time1, $time2);

		// Deny access to these variables
		$deny = array_flip(array('deny', 'key', 'difference', 'output'));

		// Return the difference
		$difference = array();
		foreach ($output as $key)
		{
			if (isset($$key) AND ! isset($deny[$key]))
			{
				// Add requested key to the output
				$difference[$key] = $$key;
			}
		}

		// Invalid output formats string
		if (empty($difference))
			return FALSE;

		// If only one output format was asked, don't put it in an array
		if (count($difference) === 1)
			return current($difference);

		// Return array
		return $difference;
	}

	/**
	 * Returns time difference between two timestamps, in the format:
	 * N year, N months, N weeks, N days, N hours, N minutes, and N seconds ago
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(date::timespan_string(time(), strtotime('2 year ago')));
	 *
	 *     // Output:
	 *     (string)  2 years, 12 hours, 22 minutes and 28 seconds
	 *
	 * @param   integer   $time1   Timestamp
	 * @param   integer   $time2   Timestamp, defaults to the current time
	 * @param   string    $output  Formatting string
	 * @return  string
	 */
	public static function timespan_string($time1, $time2 = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		if ($difference = date::timespan($time1, $time2, $output) AND is_array($difference))
		{
			// Determine the key of the last item in the array
			$last = end($difference);
			$last = key($difference);

			$span = array();
			foreach ($difference as $name => $amount)
			{
				if ($amount === 0)
				{
					// Skip empty amounts
					continue;
				}

				// Add the amount to the span
				$span[] = ($name === $last ? ' and ' : ', ').$amount.' '.($amount === 1 ? inflector::singular($name) : $name);
			}

			// If the difference is less than 60 seconds, remove the preceding and.
			if (count($span) === 1)
			{
				$span[0] = ltrim($span[0], 'and ');
			}

			// Replace difference by making the span into a string
			$difference = trim(implode('', $span), ',');
		}
		elseif (is_int($difference))
		{
			// Single-value return
			$difference = $difference.' '.($difference === 1 ? inflector::singular($output) : $output);
		}

		return $difference;
	}

} // End date