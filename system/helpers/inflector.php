<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Inflector helper class.
 *
 * $Id:$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class inflector_Core {

	/**
	 * Method: uncountable
	 *  Checks if a word is defined as uncountable.
	 *
	 * Parameters:
	 *  str - word to check
	 *
	 * Returns:
	 *  TRUE or FALSE.
	 */
	public static function uncountable($str)
	{
		static $uncountables = NULL;

		if ($uncountables === NULL)
		{
			// Makes a mirrored array, eg: foo => foo
			$uncountables = array_flip(Kohana::lang('inflector'));
		}

		return isset($uncountables[$str]);
	}

	/**
	 * Method: singular
	 *  Makes a plural word singular.
	 *
	 * Parameters:
	 *  str - word to singularize
	 *
	 * Returns:
	 *  The singular version of the word.
	 */
	public static function singular($str)
	{
		static $cache;

		$str = trim($str);

		if ($cache === NULL)
		{
			// Initialize the cache
			$cache = array();
		}
		else
		{
			// Already pluralized
			if (isset($cache[$str]))
				return $cache[$str];
		}

		// Set the key name
		$key = $str;

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

		return $cache[$key] = $str;
	}

	/**
	 * Method: plural
	 *  Makes a singular word plural.
	 *
	 * Parameters:
	 *  str - word to pluralize
	 *
	 * Returns:
	 *  Plural version of the word.
	 */
	public static function plural($str)
	{
		static $cache;

		$str = trim($str);

		// We can just return uncountable words
		if (self::uncountable($str))
			return $str;

		if ($cache === NULL)
		{
			// Initialize the cache
			$cache = array();
		}
		else
		{
			// Already pluralized
			if (isset($cache[$str]))
				return $cache[$str];
		}

		// Set the key name
		$key = $str;

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

		// Set the cache and return
		return $cache[$key] = $str;
	}

	/**
	 * Method: camelize
	 *  Makes a phrase camel case.
	 *
	 * Parameters:
	 *  str - phrase to to camelize
	 *
	 * Returns:
	 *  camelCase version of the phrase.
	 */
	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * Method: underscore
	 *  Makes a phrase underscored instead of spaced.
	 *
	 * Parameters:
	 *  str - phrase to underscore
	 *
	 * Returns:
	 *  Under_score version of the phrase.
	 */
	public static function underscore($str)
	{
		return preg_replace('/\s+/', '_', trim($str));
	}

	/**
	 * Method:
	 *  Makes an underscored or dashed phrase human-reable.
	 *
	 * Parameters:
	 *  str - phrase to make human-reable
	 *
	 * Returns:
	 *  Human readable version of the phrase.
	 */
	public static function humanize($str)
	{
		return preg_replace('/[_-]+/', ' ', trim($str));
	}

} // End inflector