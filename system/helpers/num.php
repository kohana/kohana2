<?php

namespace Helper;

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Number helper class.
 *
 * ###### Using the num helper:
 *
 *     // Using the num helper is simple:
 *     echo \Kernel\Kohana::debug(num::round(24));
 *
 *     // Output:
 *     (double) 25
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class num {

	/**
	 * Round a number to the nearest nth.
	 *
	 * ###### Example
	 *
	 *     echo \Kernel\Kohana::debug(num::round(24));
	 *
	 *     // Output:
	 *     (double) 25
	 *
	 * @param   integer  $number	Number to round
	 * @param   integer  $nearest	Number to round to
	 * @return  integer
	 */
	public static function round($number, $nearest = 5)
	{
		return round($number / $nearest) * $nearest;
	}

} // End num
