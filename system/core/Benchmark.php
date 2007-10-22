<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Benchmark
 *  Simple benchmarking.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
final class Benchmark {

	// Benchmark timestamps
	private static $marks;

	/*
	 * Method: start
	 *  Set a benchmark start point.
	 *
	 * Parameters:
	 *  name - benchmark name
	 */
	public static function start($name)
	{
		if ( ! isset(self::$marks[$name]))
		{
			self::$marks[$name] = array
			(
				'start' => microtime(TRUE),
				'stop'  => FALSE
			);
		}
	}

	/*
	 * Method: stop
	 *  Set a benchmark stop point.
	 *
	 * Parameters:
	 *  name - benchmark name
	 */
	public static function stop($name)
	{
		if (isset(self::$marks[$name]) AND self::$marks[$name]['stop'] === FALSE)
		{
			self::$marks[$name]['stop'] = microtime(TRUE);
		}
	}

	/*
	 * Method: get
	 *  Get the elapsed time between a start and stop.
	 *
	 * Parameters:
	 *  name     - benchmark name, TRUE for all
	 *  decimals - number of decimal places to count to
	 */
	public static function get($name, $decimals = 4)
	{
		if ($name === TRUE)
		{
			$times = array();

			foreach(array_keys(self::$marks) as $name)
			{
				// Get each mark recursively
				$times[$name] = self::get($name, $decimals);
			}

			// Return the array
			return $times;
		}

		if ( ! isset(self::$marks[$name]))
			return FALSE;

		if (self::$marks[$name]['stop'] === FALSE)
		{
			// Stop the benchmark to prevent mis-matched results
			self::stop($name);
		}

		// Return a string version of the time between the start and stop points
		// Properly reading a float requires using number_format or sprintf
		return number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals);
	}

} // End Benchmark