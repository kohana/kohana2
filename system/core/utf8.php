<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: utf8
 *  A port of phputf8 to a unified file/class. This single file will check PHP
 *  to ensure that UTF-8 support is available and normalize global variables to
 *  UTF-8. It also provides multi-byte aware replacement string functions. These
 *  functions have been adapted from phputf8 to be optimized and fit our needs.
 *
 * Server Requirements:
 *  - PCRE needs to be compiled with UTF-8 support.
 *    <http://php.net/manual/reference.pcre.pattern.modifiers.php>
 *  - UTF-8 conversion will be much more reliable if the iconv extension is loaded.
 *    <http://php.net/iconv>
 *  - The mbstring extension is highly recommended but must not be overloading string functions.
 *    <http://php.net/mbstring>
 *
 * Note:
 *  This file is licensed differently from the rest of Kohana. As a port of
 *  phputf8, which is LGPL software, this file is released under the LGPL.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt>
 *
 * Credits:
 *  phputf8 - UTF-8 functions <http://phputf8.sourceforge.net> (c) 2005 Harry Fuecks
 */

if (preg_match('/^.$/u', 'ñ') !== 1)
{
	trigger_error
	(
		'<a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support. '.
		'See <a href="http://php.net/manual/reference.pcre.pattern.modifiers.php">PCRE Pattern Modifiers</a> '.
		'for more information. This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if ( ! extension_loaded('iconv'))
{
	trigger_error
	(
		'The <a href="http://php.net/iconv">iconv</a> extension is not loaded. '.
		'Without iconv, strings cannot be properly translated to UTF-8 from user input. '.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

if (extension_loaded('mbstring') AND (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING))
{
	trigger_error
	(
		'The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP\'s native string functions. '.
		'Disable this by setting mbstring.func_overload to 0, 1, 4 or 5 in php.ini or a .htaccess file.'.
		'This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}

// SERVER_UTF8 ? use mb_* functions : use non-native functions
if (extension_loaded('mbstring'))
{
	mb_internal_encoding('UTF-8');
	define('SERVER_UTF8', TRUE);
}
else
{
	define('SERVER_UTF8', FALSE);
}

// Convert all global variables to UTF-8.
$_GET    = utf8::clean($_GET);
$_POST   = utf8::clean($_POST);
$_COOKIE = utf8::clean($_COOKIE);
$_SERVER = utf8::clean($_SERVER);

if (PHP_SAPI == 'cli')
{
	// Convert command line arguments
	$_SERVER['argv'] = utf8::clean($_SERVER['argv']);
}

final class utf8 {

	/**
	 * Method: clean
	 *  Recursively cleans arrays, objects, and strings. Removes ASCII control
	 *  codes and converts to UTF-8 while silently discarding incompatible
	 *  UTF-8 characters.
	 *
	 * Parameters:
	 *  str - string to clean
	 *
	 * Returns:
	 *  Clean UTF-8 string.
	 */
	public static function clean($str)
	{
		if (is_array($str) OR is_object($str))
		{
			foreach($str as $key => $val)
			{
				$str[self::clean($key)] = self::clean($val);
			}
		}
		elseif (is_string($str) AND $str != '')
		{
			// iconv is fairly expensive, so it is only used when needed
			if ( ! self::is_ascii($str))
			{
				$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			}

			$str = self::strip_ascii_ctrl($str);
		}

		return $str;
	}

	/**
	 * Method: is_ascii
	 *  Tests whether a string contains only 7bit ASCII bytes. This is used to
	 *  determine when to use native functions or UTF-8 functions.
	 *
	 * Parameters:
	 *  str - string to check
	 *
	 * Returns:
	 *  TRUE or FALSE, whether the string is ASCII.
	 */
	public static function is_ascii($str)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Method: strip_ascii_ctrl
	 *  Strips out device control codes in the ASCII range.
	 *
	 * Parameters:
	 *  str - string to clean
	 *
	 * Returns:
	 *  Clean UTF-8 string.
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Method: strip_non_ascii
	 *  Strips out all non-7bit ASCII bytes.
	 *
	 * Parameters:
	 *  str - string to clean
	 *
	 * Returns:
	 *  Clean UTF-8 string.
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Method: transliterate_to_ascii
	 *  Replaces special/accented UTF-8 characters by ASCII-7 'equivalents'.
	 *  Original function (accents_to_ascii) written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str  - string to transliterate
	 *  case - -1 lowercase only, +1 uppercase only, 0 both cases
	 *
	 * Returns:
	 *  String with only ASCII characters.
	 */
	public static function transliterate_to_ascii($str, $case = 0)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _transliterate_to_ascii($str, $case);
	}

	/**
	 * Method: strlen
	 *  Returns the length of the given string.
	 *
	 * Parameters:
	 *  str - string being measured for length
	 *
	 * Returns:
	 *  Length of the given string.
	 */
	public static function strlen($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strlen($str);
	}

	/**
	 * Method: strpos
	 *  Finds position of first occurrence of a UTF-8 string.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str    - haystack
	 *  search - needle
	 *  offset - allows you to specify from which character in haystack to start searching
	 *
	 * Returns:
	 *  The position as an integer. If needle is not found, FALSE will be returned.
	 */
	public static function strpos($str, $search, $offset = 0)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strpos($str, $search, $offset);
	}

	/**
	 * Method: strrpos
	 *  Finds position of last occurrence of a char in a UTF-8 string.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str    - haystack
	 *  search - needle
	 *  offset - allows you to specify from which character in haystack to start searching
	 *
	 * Returns:
	 *  The position as an integer. If needle is not found, FALSE will be returned.
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strrpos($str, $search, $offset);
	}

	/**
	 * Method: substr
	 *  Returns part of a UTF-8 string.
	 *  Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8.
	 *
	 * Parameters:
	 *  str    - input string
	 *  offset - start
	 *  length - 
	 *
	 * Returns:
	 *  The portion of string specified by the start and length parameters or FALSE on failure.
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _substr($str, $offset, $length);
	}

	/**
	 * Method: substr_replace
	 *  Replaces text within a portion of a UTF-8 string.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str         - input string
	 *  replacement - replacement string
	 *  offset      - start
	 *  length      - 
	 *
	 * Returns:
	 *  The result string is returned.
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _substr_replace($str, $replacement, $offset, $length);
	}

	/**
	 * Method: strtolower
	 *  Makes a UTF-8 string lowercase.
	 *  Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str - input string
	 *
	 * Returns:
	 *  Lowercase UTF-8 string.
	 */
	public static function strtolower($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strtolower($str);
	}

	/**
	 * Method: strtoupper
	 *  Makes a UTF-8 string uppercase.
	 *  Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str - input string
	 *
	 * Returns:
	 *  Uppercase UTF-8 string.
	 */
	public static function strtoupper($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strtoupper($str);
	}

	/**
	 * Method: ucfirst
	 *  Makes a UTF-8 string's first character uppercase.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str - input string
	 *
	 * Returns:
	 *  UTF-8 string with first character in uppercase.
	 */
	public static function ucfirst($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _ucfirst($str);
	}

	/**
	 * Method: ucwords
	 *  Makes the first character of every word in a UTF-8 string uppercase.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str - input string
	 *
	 * Returns:
	 *  UTF-8 string with first character of every word in uppercase.
	 */
	public static function ucwords($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _ucwords($str);
	}

	/**
	 * Method: strcasecmp
	 *  Case-insensitive UTF-8 string comparison.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str1 - first string
	 *  str2 - second string
	 *
	 * Returns:
	 *  integer < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	 */
	public static function strcasecmp($str1, $str2)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strcasecmp($str1, $str2);
	}

	/**
	 * Method: str_ireplace
	 *  Returns a string or an array with all occurrences of search in subject (ignoring case).
	 *  replaced with the given replace value.
	 *  Note: it's not fast and gets slower if $search and/or $replace are arrays.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  search  - string or array with text to replace
	 *  replace - string or array with replacement text
	 *  str     - string or array with subject text
	 *  count   - number of matched and replaced needles will be returned via this parameter which is passed by reference
	 *
	 * Returns:
	 *  A string or an array of replacements.
	 */
	public static function str_ireplace($search, $replace, $str, & $count = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _str_ireplace($search, $replace, $str, $count);
	}

	/**
	 * Method: stristr
	 *  Case-insenstive UTF-8 version of strstr. Returns all of input string
	 *  from the first occurrence of needle to the end.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str    - input string
	 *  search - needle
	 *
	 * Returns:
	 *  The matched substring. If search is not found, FALSE is returned.
	 */
	public static function stristr($str, $search)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _stristr($str, $search);
	}

	/**
	 * Method: strspn
	 *  Finds the length of the initial segment matching mask.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str    - input string
	 *  mask   - mask for search
	 *  offset - optional integer: start position of the string to examine
	 *  length - optional integer: length of the string to examine
	 *
	 * Returns:
	 *  The length (int) of the initial segment of str
	 *  which consists entirely of characters in mask.
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strspn($str, $mask, $offset, $length);
	}

	/**
	 * Method: strcspn
	 *  Finds the length of the initial segment not matching mask.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str    - input string
	 *  mask   - negative mask for search
	 *  offset - optional integer: start position of the string to examine
	 *  length - optional integer: length of the string to examine
	 *
	 * Returns:
	 *  The length (int) of the initial segment of str
	 *  which does not contain any of the characters in mask.
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strcspn($str, $mask, $offset, $length);
	}

	/**
	 * Method: str_pad
	 *  Pads a UTF-8 string to a certain length with another string.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str              - input string
	 *  final_str_length - desired string length after padding
	 *  pad_str          - string to use as padding
	 *  pad_type         - STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
	 *
	 * Returns:
	 *  The padded string.
	 */
	public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _str_pad($str, $final_str_length, $pad_str, $pad_type);
	}

	/**
	 * Method: str_split
	 *  Converts a UTF-8 string to an array.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str          - input string
	 *  split_length - maximum length of each chunk (default = 1 char)
	 *
	 * Returns:
	 *  An array with str chunks, or FALSE if split_length is less than 1.
	 */
	public static function str_split($str, $split_length = 1)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _str_split($str, $split_length);
	}

	/**
	 * Method: strrev
	 *  Reverses a UTF-8 string.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  str - string to be reversed
	 *
	 * Returns:
	 *  The reversed string.
	 */
	public static function strrev($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _strrev($str);
	}

	/**
	 * Method: trim
	 *  Strips whitespace (or other UTF-8 characters)
	 *  from the beginning and end of a string.
	 *  Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str      - input string
	 *  charlist - string of characters that need to be stripped
	 *
	 * Returns:
	 *  The trimmed string.
	 */
	public static function trim($str, $charlist = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _trim($str, $charlist);
	}

	/**
	 * Method: ltrim
	 *  Strips whitespace (or other UTF-8 characters) from the beginning of a string.
	 *  Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str      - input string
	 *  charlist - string of characters that need to be stripped
	 *
	 * Returns:
	 *  The left trimmed string.
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _ltrim($str, $charlist);
	}

	/**
	 * Method: rtrim
	 *  Strips whitespace (or other UTF-8 characters) from the end of a string.
	 *  Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8.
	 *
	 * Parameters:
	 *  str      - input string
	 *  charlist - string of characters that need to be stripped
	 *
	 * Returns:
	 *  The right trimmed string.
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _rtrim($str, $charlist);
	}

	/**
	 * Method: ord
	 *  Returns the unicode ordinal for a character.
	 *  Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8.
	 *
	 * Parameters:
	 *  chr - a UTF-8 encoded character
	 *
	 * Returns:
	 *  The unicode ordinal for the given character.
	 */
	public static function ord($chr)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _ord($chr);
	}

	/**
	 * Method: to_unicode
	 *  Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
	 *  Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 *  Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 *  The Original Code is Mozilla Communicator client code.
	 *  The Initial Developer of the Original Code is Netscape Communications Corporation.
	 *  Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 *  Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
	 *  Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * Parameters:
	 *  str - a UTF-8 encoded string
	 *
	 * Returns:
	 *  Array of unicode code points or FALSE if UTF-8 invalid.
	 */
	public static function to_unicode($str)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _to_unicode($str);
	}

	/**
	 * Method: from_unicode
	 *  Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
	 *  Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 *  Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 *  The Original Code is Mozilla Communicator client code.
	 *  The Initial Developer of the Original Code is Netscape Communications Corporation.
	 *  Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 *  Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
	 *  Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * Parameters:
	 *  arr - array of unicode code points representing a string
	 *
	 * Returns:
	 *  UTF-8 string or FALSE if array contains invalid code points.
	 */
	public static function from_unicode($arr)
	{
		require_once SYSPATH.'core/utf8/'.__FUNCTION__.EXT;
		return _from_unicode($arr);
	}

} // End utf8 class