<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Benchmark
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
				'start'        => microtime(TRUE),
				'stop'         => FALSE,
				'memory_start' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
				'memory_stop'  => FALSE
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
			self::$marks[$name]['memory_stop'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
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
		return array
		(
			'time'   => number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals),
			'memory' => (self::$marks[$name]['memory_stop'] - self::$marks[$name]['memory_start'])
		);
	}

} // End Benchmark