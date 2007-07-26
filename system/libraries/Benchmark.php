<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Benchmark {

	public static $marks;

	/**
	 * Set a benchmark start point
	 *
	 * @access  public
	 * @param   string
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
	 */
	public static function stop($name)
	{
		if (isset(self::$marks[$name]) AND self::$marks[$name]['stop'] === FALSE)
		{
			self::$marks[$name]['stop'] = microtime(TRUE);
		}
	}

	public static function get($name, $decimals = 4)
	{
		if (isset(self::$marks[$name]))
		{
			if (self::$marks[$name]['stop'] == 0)
			{
				self::stop($name);
			}

			return number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals);
		}
	}
} // End Benchmark Class