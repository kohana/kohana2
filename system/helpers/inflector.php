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
 * Inflector Class
 *
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/helpers/inflector.html
 */
class inflector {

	public static function uncountable($str)
	{
		static $uncountables = NULL;

		if ($uncountables === NULL)
		{
			// Makes a mirrored array, eg: foo => foo
			$uncountables = array_combine(Kohana::lang('inflector'), Kohana::lang('inflector'));
		}

		return isset($uncountables[$str]);
	}

	public static function singular($str)
	{
		$str = trim($str);

		// We can just return uncountable words
		if (self::uncountable($str))
			return $str;

		$end = substr($str, -3);

		if ($end == 'ies')
		{
			$str = substr($str, 0, strlen($str) - 3).'y';
		}
		elseif ($end == 'ses' OR $end == 'zes' OR $end == 'xes')
		{
			$str = substr($str, 0, strlen($str) - 2);
		}
		elseif (substr($str, -1) == 's')
		{
			$str = substr($str, 0, strlen($str) - 1);
		}

		return $str;
	}

	public static function plural($str)
	{
		$str = trim($str);

		// We can just return uncountable words
		if (self::uncountable($str))
			return $str;

		$end = substr($str, -1);
		$low = (strcmp($end, strtolower($end)) === 0) ? TRUE : FALSE;

		if (preg_match('/[sxz]$/i', $str) OR preg_match('/[^aeioudgkprt]h$/i', $str))
		{
			$end = 'es';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}
		elseif (preg_match('/[^aeiou]y$/i', $str))
		{
			$end = 'ies';
			$end = ($low == FALSE) ? strtoupper($end) : $end;
			$str = substr_replace($str, $end, -1);
		}
		else
		{
			$end = 's';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}

		return $str;
	}

	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	public static function underscore($str)
	{
		$str = strtolower(trim($str));

		return preg_replace('/\s+/', '_', $str);
	}

	public static function humanize($str)
	{
		$str = strtolower(trim($str));

		return ucwords(preg_replace('/_+/', ' ', $str));
	}

} // End inflector Class