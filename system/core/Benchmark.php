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
 * Benchmark Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/general/benchmarks.html
 */
final class Benchmark {

	// Benchmark timestamps
	private static $marks;

	/**
	 * Set a benchmark start point
	 *
	 * @access  public
	 * @param   string
	 * @return  void
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

	/**
	 * Set a benchmark stop point
	 *
	 * @access  public
	 * @param   string
	 * @return  void
	 */
	public static function stop($name)
	{
		if (isset(self::$marks[$name]) AND self::$marks[$name]['stop'] === FALSE)
		{
			self::$marks[$name]['stop'] = microtime(TRUE);
		}
	}

	/**
	 * Get the elapsed time for a benchmark point
	 *
	 * @access  public
	 * @param   string
	 * @param   integer
	 * @return  flaot
	 */
	public static function get($name, $decimals = 4)
	{
		$total = FALSE;

		if (isset(self::$marks[$name]))
		{
			if (self::$marks[$name]['stop'] === FALSE)
			{
				self::stop($name);
			}

			// Properly reading a float requires using number_format or sprintf
			$total = number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals);
		}

		return $total;
	}

	/**
	 * Get the elapsed time for all benchmark points
	 *
	 * @access  public
	 * @param   integer
	 * @return  array
	 */
	public static function get_all($decimals = 4)
	{
		$benchmarks = array();

		foreach (array_keys(self::$marks) as $name)
		{
			$benchmarks[$name] = self::get($name, $decimals);
		}

		return $benchmarks;
	}

} // End Benchmark Class