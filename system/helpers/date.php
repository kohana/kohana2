<?php defined('SYSPATH') or die('No direct access allowed.');

class date {

	public static function offset($one, $two = TRUE)
	{
		// Create timezone objects
		$one = new DateTimeZone($one);
		$two = new DateTimeZone(($two === TRUE) ? 'now' : $two);
		
		// Create datetime objects from timezones
		$one = new DateTime();
		
	}

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

	public static function minutes($step = 5)
	{
		return self::seconds((int) $step);
	}

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

	public static function ampm($hour)
	{
		$hour = (int) $hour;

		return ($hour > 11) ? 'PM' : 'AM';
	}

	public static function days($month, $year = FALSE)
	{
		$month = (int) $month;
		$year  = ($year == FALSE) ? date('Y') : (int) $year;
		$time  = mktime(1, 0, 0, $month, 1, $year);

		$vals  = array();
		for ($i = 1; $i < date('t', $time); $i++)
		{
			$vals[$i] = $i;
		}

		return $vals;
	}

	public static function months()
	{
		return self::hours(1);
	}

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
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @param	string
	 * @return	mixed
	 */
	public static function timespan($time1, $time2 = FALSE, $output = 'years,months,weeks,days,hours,minutes,seconds')
	{
		// Calculate timespan (in seconds)
		$time1 = (int) max(0, $time1);
		$time2 = (int) ($time2 === FALSE) ? time() : max(0, $time2);
		$timespan = abs($time1 - $time2);
		
		// Array with the output formats
		$output = preg_split('/[\s,]+/', strtolower($output));
		
		// Years ago
		if (in_array('years', $output))
		{
			$year = 60 * 60 * 24 * 365;
			$timediff['years'] = (int) floor($timespan / $year);
			$timespan -= $timediff['years'] * $year;
		}
		
		// Months ago
		if (in_array('months', $output))
		{
			$month = 60 * 60 * 24 * 30;
			$timediff['months'] = (int) floor($timespan / $month);
			$timespan -= $timediff['months'] * $month;
		}
			
		// Weeks ago
		if (in_array('weeks', $output))
		{
			$week = 60 * 60 * 24 * 7;
			$timediff['weeks'] = (int) floor($timespan / $week);
			$timespan -= $timediff['weeks'] * $week;
		}
			
		// Days ago
		if (in_array('days', $output))
		{
			$day = 60 * 60 * 24;
			$timediff['days'] = (int) floor($timespan / $day);
			$timespan -= $timediff['days'] * $day;
		}
			
		// Hours ago
		if (in_array('hours', $output))
		{
			$hour = 60 * 60;
			$timediff['hours'] = (int) floor($timespan / $hour);
			$timespan -= $timediff['hours'] * $hour;
		}
			
		// Minutes ago
		if (in_array('minutes', $output))
		{
			$minute = 60;
			$timediff['minutes'] = (int) floor($timespan / $minute);
			$timespan -= $timediff['minutes'] * $minute;
		}
		
		// Seconds ago
		if (in_array('seconds', $output))
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