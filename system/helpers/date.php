<?php defined('SYSPATH') or die('No direct access allowed.');

class date {

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

} // End date Class