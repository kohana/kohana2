<?php
/**
 * Simple benchmarking.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
final class Benchmark {

	private static $benchmarks = array();

	/**
	 * Start a benchmark that tracks elapsed time and memory usage.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function start($name)
	{
		if ( ! isset(self::$benchmarks[$name]))
		{
			// Start timer tracking
			self::$benchmarks[$name]['start']['time'] = microtime(TRUE);

			// Start memory usage tracking
			self::$benchmarks[$name]['start']['memory'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
		}
	}

	/**
	 * Stop a benchmark.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function stop($name)
	{
		if (isset(self::$benchmarks[$name]) AND ! isset(self::$benchmarks[$name]['stop']))
		{
			// Stop timer tracking
			self::$benchmarks[$name]['stop']['time'] = microtime(TRUE);

			// Stop memory usage tracking
			self::$benchmarks[$name]['stop']['memory'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
		}
	}

	/**
	 * Clear a benchmark.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function clear($name)
	{
		unset(self::$benchmarks[$name]);
	}

	/**
	 * Get the total elapsed time and memory usage of a benchmark.
	 *
	 * @param   string   benchmark name, TRUE for all
	 * @return  array    time => (float), memory = (bytes)
	 */
	public static function get($name)
	{
		if ($name === TRUE)
		{
			$names = array_keys(self::$benchmarks);

			$marks = array();
			foreach ($names as $name)
			{
				// Get each benchmark total
				$marks[$name] = self::get($name);
			}

			return $marks;
		}

		if ( ! isset(self::$benchmarks[$name]))
		{
			// Benchmark does not exist
			return FALSE;
		}

		if ( ! isset(self::$benchmarks[$name]['stop']))
		{
			// Stop the benchmark
			self::stop($name);
		}

		return array
		(
			'time'   => self::$benchmarks[$name]['stop']['time']   - self::$benchmarks[$name]['start']['time'],
			'memory' => self::$benchmarks[$name]['stop']['memory'] - self::$benchmarks[$name]['start']['memory'],
		);
	}

} // End Benchmark
