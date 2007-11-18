<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: text
 *  Text helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class text {

	/*
	 * Method: limit_words
	 *  Limits a phrase to a given number of words.
	 *
	 * Parameters:
	 *  str      - phrase to limit words of
	 *  limit    - number of words to limit to
	 *  end_char - end character or entity
	 *
	 * Returns:
	 *  A word-limited string with the end character attached.
	 */
	public static function limit_words($str, $limit = 100, $end_char = '&#8230;')
	{
		$limit = (int) $limit;

		if (trim($str) == '')
			return $str;

		if ($limit <= 0)
			return $end_char;

		preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

		// Only attach the end character if the matched string is shorter
		// than the starting string.
		return rtrim($matches[0]).(strlen($matches[0]) == strlen($str) ? '' : $end_char);
	}

	/*
	 * Method: limit_chars
	 *  Limits a phrase to a given number of characters.
	 *
	 * Parameters:
	 *  str            - phrase to limit characters of
	 *  limit          - number of characters to limit to
	 *  end_char       - end character or entity
	 *  preserve_words - enable or disable the preservation of words while limiting
	 *
	 * Returns:
	 *  A character-limited string with the end character attached.
	 */
	public static function limit_chars($str, $limit = 100, $end_char = '&#8230;', $preserve_words = FALSE)
	{
		$limit = (int) $limit;

		if (trim($str) == '' OR utf8::strlen($str) <= $limit)
			return $str;

		if ($limit <= 0)
			return $end_char;

		if ( ! $preserve_words)
			return rtrim(utf8::substr($str, 0, $limit)).$end_char;

		preg_match('/^.{'.($limit - 1).'}\S*/us', $str, $matches);

		return rtrim($matches[0]).(strlen($matches[0]) == strlen($str) ? '' : $end_char);
	}

	/*
	 * Method: alternate
	 *  Alternates between two or more strings.
	 *
	 * Parameters:
	 *  Strings to alternate between.
	 *
	 * Returns:
	 *  The next alternate item.
	 */
	public static function alternate()
	{
		static $i;

		if (func_num_args() == 0)
		{
			$i = 0;
			return '';
		}

		$args = func_get_args();
		return $args[($i++ % count($args))];
	}

	/*
	 * Method: random
	 *  Generates a random string of a given type and length.
	 *
	 * Parameters:
	 *  type   - a type of pool, or a string of characters to use as the pool
	 *  length - length of string to return
	 *
	 * Default Types:
	 *  unique  - 40 character unique hash
	 *  alnum   - alpha-numeric characters
	 *  alpha   - alphabetical characters
	 *  numeric - digit characters, 0-9
	 *  nozero  - digit characters, 1-9
	 *
	 * Returns:
	 *  A random string.
	 */
	public static function random($type = 'alnum', $length = 8)
	{
		switch ($type)
		{
			case 'unique':
				return sha1(uniqid(NULL, TRUE));
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			default:
				$pool = (string) $type;
			break;
		}

		$str = '';
		$pool_size = utf8::strlen($pool);

		for ($i = 0; $i < $length; $i++)
		{
			$str .= utf8::substr($pool, mt_rand(0, $pool_size - 1), 1);
		}

		return $str;
	}

	/*
	 * Method: reduce_slashes
	 *  Reduce multiple slashes in a string to single slashes.
	 *
	 * Parameters:
	 *  str - string to reduce slashes of
	 *
	 * Returns:
	 *  Sanitized string.
	 */
	public static function reduce_slashes($str)
	{
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

	/*
	 * Method: censor
	 *  Replaces the given words with a string.
	 *
	 * Parameters:
	 *  str                   - phrase to replace words in
	 *  badwords              - words to replace
	 *  replacement           - replacement string
	 *  replace_partial_words - replace words across word boundries (space, period, etc)
	 *
	 * Returns:
	 *  The input string with the given words replaced.
	 */
	public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = FALSE)
	{
		foreach ((array) $badwords as $key => $badword)
		{
			$badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
		}

		$regex = '('.implode('|', $badwords).')';

		if ($replace_partial_words == TRUE)
		{
			// Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
			$regex = '(?<=\b|\s|^)'.$regex.'(?=\b|\s|$)';
		}

		$regex = '!'.$regex.'!ui';

		if (utf8::strlen($replacement) == 1)
		{
			$regex .= 'e';
			return preg_replace($regex, 'str_repeat($replacement, utf8::strlen(\'$1\'))', $str);
		}

		return preg_replace($regex, $replacement, $str);
	}

	/*
	 * Method: bytes
	 *  Return human readable sizes.
	 *  Based on original functions written by:
	 *  - Aidan Lister: http://aidanlister.com/repos/v/function.size_readable.php
	 *  - Quentin Zervaas: http://www.phpriot.com/d/code/strings/filesize-format/
	 *
	 * Parameters:
	 *  bytes      - size in bytes
	 *  force_unit - a definitive unit
	 *  format     - the return string format
	 *  si         - whether to use SI prefixes or IEC
	 *
	 * Returns:
	 *  Human readable size.
	 */
	public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
	{
		// Format string
		$format = ($format === NULL) ? '%01.2f %s' : (string) $format;

		// IEC prefixes (binary)
		if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
		{
			$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$mod   = 1024;
		}
		// SI prefixes (decimal)
		else
		{
			$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1000;
		}

		// Determine unit to use
		if (($power = array_search((string) $force_unit, $units)) === FALSE)
		{
			$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		}

		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}

} // End text