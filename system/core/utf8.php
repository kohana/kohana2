<?php defined('SYSPATH') or die('No direct access allowed.');

if (@preg_match('/^.{1}/u', 'ñ') === 1 AND extension_loaded('iconv'))
{
	/**
	 * @todo  this should really be detected from either config.php or from $_SERVER['HTTP_ACCEPT_LANGUAGE']
	 */
	setlocale(LC_ALL, 'en_US.UTF-8');

	if (extension_loaded('mbstring'))
	{
		mb_internal_encoding('UTF-8');
		define('SERVER_UTF8', (@ini_get('mbstring.func_overload') ? 3 : 2));
	}
	else
	{
		define('SERVER_UTF8', 1);
	}
	// Make sure that all the global variables are converted to UTF-8
	$_POST   = utf8::clean($_POST);
	$_GET    = utf8::clean($_GET);
	$_SERVER = utf8::clean($_SERVER);
	$_COOKIE = utf8::clean($_COOKIE);

	if (PHP_SAPI == 'cli')
	{
		global $argv;
		$argv = utf8::clean($argv);
	}
}
else
{
	/**
	 * @todo Maybe this should be a bit more helpful?
	 */
	die('Your server does not support UTF-8 encoding. ');
}

final class utf8 {

	/**
	 * UTF-8 String Checking
	 *
	 * Checks if a given string has multi-byte characters. This is used to
	 * determine when to use native functions or UTF-8 functions. When using
	 * this function, be sure to check it's return value explicitly. When
	 * calling this function on non-string variables, the variable is returned
	 * without issuing any kind of warning.
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @param  string
	 * @return mixed
	 */
	public static function is_multibyte($str)
	{
		if ($str == '' OR ! is_string($str))
			return $str;

		// Attempts to locate 1 byte outside the ASCII range
		return (bool) preg_match('/(?:[^\x00-\x7F])/', $str);
	}

	/**
	 * UTF-8 Normalizer/Cleaner
	 *
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control codes
	 * and converts to UTF-8 while silently discarding incompatible UTF-8 characters
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @param  string
	 * @return string
	 */
	public static function clean($str)
	{
		if (empty($str))
			return $str;

		if (is_array($str) OR is_object($str))
		{
			foreach($str as $key => $val)
			{
				$str[self::clean($key)] = self::clean($val);
			}
		}
		elseif (is_string($str))
		{
			/**
			 * @todo need to fix this, it breaks things
			 */
			// $str = preg_replace('/^[\x09\x0A\x0D\x20-\x7E]/u', '', $str);
			// iconv is somewhat expensive, so don't do it unless we need to
			(self::is_multibyte($str)) and ($str = @iconv('', 'UTF-8//IGNORE', $str));
		}

		return $str;
	}

	/**
	 * UTF-8 version of ord()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/ord
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string  character to return code of
	 * @return integer
	 *
	 * @todo FIXME!
	 */
	public static function ord($chr)
	{
		return ord($chr);
	}

	/**
	 * UTF-8 version of str_ireplace()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/str_ireplace
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string  text to replace
	 * @param  string  replacement text
	 * @param  string
	 * @param  integer (optional) number of replacements to make
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function str_ireplace($search, $replace, $str, &$count = NULL)
	{
		return ($count === NULL) ? str_ireplace($search, $replace, $str) : str_ireplace($search, $replace, $str, $count);
	}

	/**
	 * UTF-8 version of str_replace()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/str_replace
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string  text to replace
	 * @param  string  replacement text
	 * @param  string
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function str_replace($search, $replace, $string)
	{
		return str_replace($search, $replace, $string);
	}

	/**
	 * UTF-8 version of str_pad()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/str_pad
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  integer length of return
	 * @param  string  string to use as padding
	 * @param  define  STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function str_pad($str, $length, $padding = ' ', $type = STR_PAD_RIGHT)
	{
		return str_pad($str, $length, $padding, $type);
	}

	/**
	 * UTF-8 version of str_split()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/str_split
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string  search string
	 * @return array
	 */
	public static function str_split($str, $length = 1)
	{
		if (self::is_multibyte($str) === FALSE)
		{
			return str_split($str, $length);
		}
		elseif ( ! ctype_digit($length) OR $length < 1)
		{
			return FALSE;
		}

		if (self::strlen($str) <= $length)
		{
			return array($str);
		}

		preg_match_all('!.{'.$length.'}|[^\x00]{1,'.$length.'}$!us', $str, $chars);

		return $chars[0];
	}

	/**
	 * UTF-8 version of strcasecmp()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strcasecmp
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string
	 * @return integer
	 *
	 * @todo FIXME!
	 */
	public static function strcasecmp($one, $two)
	{
		return strcmp($one, $two);
	}

	/**
	 * UTF-8 version of stristr()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strpos
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string  search string
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function stristr($str, $search)
	{
		if ($search == FALSE)
			return $str;

		return stristr($str, $search);
	}

	/**
	 * UTF-8 version of strlen()
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strlen
	 * @param  string
	 * @return integer
	 */
	public static function strlen($str)
	{
		if (self::is_multibyte($str) === FALSE OR SERVER_UTF8 === 3)
		{
			return strlen($str);
		}

		switch(SERVER_UTF8)
		{
			case 2:
				return mb_strlen($str);
			default:
				// Fastest way to find the length of a unicode string
				return strlen(utf8_decode($str));
		}
	}

	/**
	 * UTF-8 version of strpos()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strpos
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string  search string
	 * @param  integer (optional) characters to offset
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function strpos($str, $search, $offset = NULL)
	{
		return ($offset === NULL) ? strpos($str, $search) : strpos($str, $search, $offset);
	}

	/**
	 * UTF-8 version of strrev()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strrev
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @return string
	 */
	public static function strrev($str)
	{
		switch(self::is_multibyte($str))
		{
			case TRUE:
				preg_match_all('/./us', $str, $chars);
				$str = implode('', $chars[0]);
			break;
			case FALSE:
				$str = strrev($str);
			break;
		}

		return $str;
	}

	/**
	 * UTF-8 version of strspn()
	 *
	 * Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/strspn
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string  mask for search
	 * @param  integer (optional) starting character position
	 * @param  integer (optional) length of return
	 * @return integer
	 */
	public static function strspn($str, $mask, $start = NULL, $length = NULL)
	{
		if (self::is_multibyte($str) === FALSE)
		{
			if ($start !== NULL)
			{
				return ($length !== NULL) ? strspn($str, $mask, $start, $length) : strspn($str, $mask, $start);
			}
			return strspn($str, $mask);
		}

		$mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);

		($start !== NULL OR $length !== NULL) and ($str = self::substr($str, $start, $length));

		preg_match('!^['.$mask.']+!u', $str, $chars);

		return isset($chars[0]) ? $chars[0] : 0;
	}

	/**
	 * UTF-8 version of substr()
	 *
	 * Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/substr
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  integer number of characters to offset
	 * @param  integer (optional) length of return
	 * @return string (success) or FALSE
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		// If the string does not have multibyte characters or the server is
		// using mb_* overloading, we can call the native function
		if (self::is_multibyte($str) == FALSE OR SERVER_UTF8 === 3)
		{
			return ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}

		switch(SERVER_UTF8)
		{
			case 2:
				return ($length === NULL) ? mb_substr($str, $offset) : mb_substr($str, $offset, $length);
			default:
				// Make sure the string is a string
				$str = (string) $str;

				// Normalize the offset and length to integers
				$offset = is_numeric($offset) ? (int) $offset : 0;
				$length = !is_null($length)   ? (int) $length : NULL;

				// Length is 0, or impossible search
				if ($length === 0 OR ($length < 0 AND $offset < 0 AND $length < $offset))
					return '';

				// Normalize negative offset to a positive one
				if ($offset < 0)
				{
					$strlen = self::strlen($str);
					$offset = (($strlen + $offset) > 0) ? $strlen + $offset : 0;
				}

				// Will be concantated for the regex
				$char = '';
				$size = '';

				// Create an offset expression.
				if ($offset > 0)
				{
					// PCRE only supports 65535 repeitions, so we need to repeat when necessary
					$x = (int) ($offset / 65535);
					$y = $offset % 65535;

					($x == TRUE) and ($char = '(?:.{65535}){'.$x.'}');

					$char = '^(?:'.$char.'.{'.$y.'})';
				}
				// No offset necessary, just anchor
				else
				{
					$char = '^';
				}

				// Create a length expression
				if ($length !== NULL)
				{
					// Get string length if it's not set yet
					(isset($strlen)) or ($strlen = self::strlen($str));

					// Nothing will be found
					if ($offset > $strlen)
						return '';

					// Find length from the left (position length)
					if ($length > 0)
					{
						// Reduce length so that it can't go beyond the end of the string
						$length = min($strlen - $offset, $length);

						$x = (int) ($length / 65535);
						$y = $length % 65535;

						($x == TRUE) and ($size = '(?:.{65535}){'.$x.'}');

						$size = '('.$size.'.{'.$y.'})';
					}
					// Find length from the right (negative length)
					elseif ($length < 0)
					{
						if ($length < ($offset - $strlen))
							return '';

						$x = (int) ((-$length) / 65535);
						$y = (-$length) % 65535;

						($x == TRUE) and $size = '(?:.{65535}){'.$x.'}';

						$size = '(.*)(?:'.$size.'.{'.$y.'})$';
					}
				}
				// No length set, grab it all
				else
				{
					$size = '(.*)$';
				}

				return preg_match('#'.$char.$size.'#us', $str, $substr) ? $substr[1] : '';
		}
	}

	/**
	 * UTF-8 version of substr_replace()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/substr_replace
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @param  string  replacement string
	 * @param  integer number of characters to offset
	 * @param  integer (optional) length of return
	 * @return string
	 */
	public static function substr_replace($str, $replace, $offset, $length = NULL)
	{
		if (self::is_multibyte($str) === FALSE)
		{
			return ($length === NULL) ? substr_replace($str, $replace, $offset) : substr_replace($str, $replace, $offset, $length);
		}

		preg_match_all('/./us', $str, $chars);
		preg_match_all('/./us', $replace, $change);

		$length = ($length === NULL) ? self::strlen($str) : $length;

		array_splice($chars[0], $offset, $length, $change[0]);

		return implode('', $chars[0]);
	}

	/**
	 * UTF-8 version of ucfirst()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/ucfirst
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function ucfirst($str)
	{
		return ucfirst($str);
	}

	/**
	 * UTF-8 version of ucwords()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @package Kohana Core
	 * @subpackage UTF-8
	 * @see    http://php.net/ucwords
	 * @see    http://phputf8.sourceforge.net/
	 * @param  string
	 * @return string
	 *
	 * @todo FIXME!
	 */
	public static function ucwords($str)
	{
		return ucwords($str);
	}

} // End utf8 class