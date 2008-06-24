<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Inflector helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class inflector_Core {

	// Cached inflections
	protected static $cache = array();

	// Uncountable and irregular words
	protected static $uncountable;
	protected static $irregular;

	/**
	 * Checks if a word is defined as uncountable.
	 *
	 * @param   string   word to check
	 * @return  boolean
	 */
	public static function uncountable($str)
	{
		if (self::$uncountable === NULL)
		{
			// Cache uncountables
			self::$uncountable = Config::item('inflector.uncountable');

			// Make uncountables mirroed
			self::$uncountable = array_combine(self::$uncountable, self::$uncountable);
		}

		return isset($uncountables[$str]);
	}

	/**
	 * Makes a plural word singular.
	 *
	 * @param   string   word to singularize
	 * @param   integer  number of things
	 * @return  string
	 */
	public static function singular($str, $count = NULL)
	{
		// Remove garbage
		$str = trim($str);

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with a single count
		if ($count === 0 OR $count > 1)
			return $str;

		// Cache key name
		$key = $str.$count;

		if (isset(self::$cache[$key]))
			return self::$cache[$key];

		if (inflector::uncountable($str))
			return self::$cache[$key] = $str;

		if (empty(self::$irregular))
		{
			// Cache irregular words
			self::$irregular = Config::item('inflector.irregular');
		}

		if ($irregular = array_search($str, self::$irregular))
		{
			$str = $irregular;
		}
		elseif (substr($str, -3) === 'ies')
		{
			$str = substr($str, 0, strlen($str) - 3).'y';
		}
		elseif (substr($str, -4) === 'sses' OR substr($str, -3) === 'xes')
		{
			$str = substr($str, 0, strlen($str) - 2);
		}
		elseif (substr($str, -1) === 's')
		{
			$str = substr($str, 0, strlen($str) - 1);
		}

		return self::$cache[$key] = $str;
	}

	/**
	 * Makes a singular word plural.
	 *
	 * @param   string  word to pluralize
	 * @return  string
	 */
	public static function plural($str, $count = NULL)
	{
		// Remove garbage
		$str = trim($str);

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with singular
		if ($count === 1)
			return $str;

		// Cache key name
		$key = $str.$count;

		if (isset(self::$cache[$key]))
			return self::$cache[$key];

		if (inflector::uncountable($str))
			return self::$cache[$key] = $str;

		$end = substr($str, -1);
		$low = (strcmp($end, strtolower($end)) === 0) ? TRUE : FALSE;

		if (empty(self::$irregular))
		{
			// Cache irregular words
			self::$irregular = Config::item('inflector.irregular');
		}

		if (isset(self::$irregular[strtolower($str)]))
		{
			$str = self::$irregular[$str];

			if ($low === FALSE)
			{
				// Make uppercase
				$str = strtoupper($str);
			}
		}
		elseif (preg_match('/[sxz]$/i', $str) OR preg_match('/[^aeioudgkprt]h$/i', $str))
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

		// Set the cache and return
		return $cache[$key] = $str;
	}

	/**
	 * Makes a phrase camel case.
	 *
	 * @param   string  phrase to camelize
	 * @return  string
	 */
	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * Makes a phrase underscored instead of spaced.
	 *
	 * @param   string  phrase to underscore
	 * @return  string
	 */
	public static function underscore($str)
	{
		return preg_replace('/\s+/', '_', trim($str));
	}

	/**
	 * Makes an underscored or dashed phrase human-reable.
	 *
	 * @param   string  phrase to make human-reable
	 * @return  string
	 */
	public static function humanize($str)
	{
		return preg_replace('/[_-]+/', ' ', trim($str));
	}

} // End inflector