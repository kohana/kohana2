<?php
/**
 * PHP UTF-8 Support
 *
 * A port of phputf8 to a unified file/class. This single file will check PHP
 * to ensure that UTF-8 support is available and normalize global variables to
 * UTF-8. It also provides multi-byte aware replacement string functions. These
 * functions have been adapted from phputf8 to be optimized and fit our needs.
 *
 * NOTE: This file is licensed differently from the rest of Kohana. As a port of
 * phputf8, this library is released under the LGPL to prevent license violations.
 *
 * @package          Kohana
 * @subpackage       UTF-8
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007 Kohana Framework Team
 * @link             http://kohanaphp.com
 * @link             http://phputf8.sourceforge.net
 * @license          http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 * @since            Version 1.2
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Check whether the server supports the UTF-8 encoding. We need:
 * - PCRE compiled with UTF-8 support
 * - The iconv extension
 * - The mbstring extension (if loaded) must not be overloading string functions
 */
if (preg_match('/^.{1}/u', 'ñ') !== 1)
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

/**
 * @todo  this should really be detected from either config.php
 * @todo  move this out of this file and into Kohana or Bootstrap so that this file is re-usable
 */
setlocale(LC_ALL, 'en_US.UTF-8');

/**
 * Send default text/html UTF-8 header. Can be overwritten.
 */
header('Content-type: text/html; charset=UTF-8');

/**
 * Set SERVER_UTF8. Possible values are:
 *   TRUE  - use mb_* replacement functions
 *   FALSE - use non-native replacement functions
 */
if (extension_loaded('mbstring'))
{
	mb_internal_encoding('UTF-8');
	define('SERVER_UTF8', TRUE);
}
else
{
	define('SERVER_UTF8', FALSE);
}

/*
 * Make sure that all the global variables are converted to UTF-8.
 */
$_GET    = utf8::clean($_GET);
$_POST   = utf8::clean($_POST);
$_COOKIE = utf8::clean($_COOKIE);
$_SERVER = utf8::clean($_SERVER);
// Convert command line arguments
if (PHP_SAPI == 'cli')
{
	global $argv;
	$argv = utf8::clean($argv);
}

/**
 * UTF-8 helper and replacement functions
 */
final class utf8 {

	/**
	 * UTF-8 Normalizer/Cleaner
	 *
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control characters
	 * and converts to UTF-8 while silently discarding incompatible UTF-8 characters.
	 *
	 * @param  mixed
	 * @return mixed
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
			// iconv is somewhat expensive, so don't do it unless we need to
			if ( ! self::is_ascii($str))
			{
				$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			}
			
			$str = self::strip_ascii_ctrl($str);
		}

		return $str;
	}

	/**
	 * Tests whether a string contains only 7bit ASCII bytes. This is used to
	 * determine when to use native functions or UTF-8 functions.
	 *
	 * @param  string
	 * @return boolean
	 */
	public static function is_ascii($str)
	{
		return ! preg_match('/[^\x00-\x7F]/', $str);
	}
	
	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 * @param  string
	 * @return string
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}
	
	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 * @param  string
	 * @return string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * UTF-8 version of strlen()
	 *
	 * @see    http://php.net/strlen
	 * @param  string
	 * @return integer
	 */
	public static function strlen($str)
	{
		if (self::is_ascii($str))
		{
			$str = strlen($str);
		}
		elseif (SERVER_UTF8)
		{
			$str = mb_strlen($str);
		}
		else
		{
			$str = strlen(utf8_decode($str));
		}

		return $str;
	}

	/**
	 * UTF-8 version of ord()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/ord
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
	 * @see    http://php.net/str_ireplace
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
	 * @see    http://php.net/str_replace
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
	 * @see    http://php.net/str_pad
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
	 * @see    http://php.net/str_split
	 * @param  string
	 * @param  string  search string
	 * @return array
	 */
	public static function str_split($str, $length = 1)
	{
		if (self::is_ascii($str))
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
	 * @see    http://php.net/strcasecmp
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
	 * @see    http://php.net/strpos
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
	 * UTF-8 version of strpos()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strpos
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
	 * @see    http://php.net/strrev
	 * @param  string
	 * @return string
	 */
	public static function strrev($str)
	{
		if (self::is_ascii($str))
		{
			$str = strrev($str);
		}
		else
		{
			preg_match_all('/./us', $str, $chars);
			$str = implode('', $chars[0]);
		}

		return $str;
	}

	/**
	 * UTF-8 version of strspn()
	 *
	 * Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8
	 *
	 * @see    http://php.net/strspn
	 * @param  string
	 * @param  string  mask for search
	 * @param  integer (optional) starting character position
	 * @param  integer (optional) length of return
	 * @return integer
	 */
	public static function strspn($str, $mask, $start = NULL, $length = NULL)
	{
		if (self::is_ascii($str))
		{
			if ($start !== NULL)
			{
				$str = ($length !== NULL) ? strspn($str, $mask, $start, $length) : strspn($str, $mask, $start);
			}
			else
			{
				$str = strspn($str, $mask);
			}
		}
		else
		{
			($start !== NULL OR $length !== NULL) and ($str = self::substr($str, $start, $length));

			$mask = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $mask);

			preg_match('!^['.$mask.']+!u', $str, $chars);

			$str = isset($chars[0]) ? $chars[0] : 0;
		}

		return $str;
	}

	/**
	 * UTF-8 version of substr()
	 *
	 * Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8
	 *
	 * @see    http://php.net/substr
	 * @param  string
	 * @param  integer number of characters to offset
	 * @param  integer (optional) length of return
	 * @return string (success) or FALSE
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		if (self::is_ascii($str))
		{
			$str = ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}
		elseif (SERVER_UTF8)
		{
			$str = ($length === NULL) ? mb_substr($str, $offset) : mb_substr($str, $offset, $length);
		}
		else
		{
			// Make sure the string is a string
			$str = (string) $str;

			// Normalize the offset and length to integers
			$offset = is_numeric($offset) ? (int) $offset : 0;
			$length = ! is_null($length)  ? (int) $length : NULL;

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

			$str = preg_match('#'.$char.$size.'#us', $str, $substr) ? $substr[1] : '';
		}

		return $str;
	}

	/**
	 * UTF-8 version of substr_replace()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/substr_replace
	 * @param  string
	 * @param  string  replacement string
	 * @param  integer number of characters to offset
	 * @param  integer (optional) length of return
	 * @return string
	 */
	public static function substr_replace($str, $replace, $offset, $length = NULL)
	{
		if (self::is_ascii($str))
		{
			$str = ($length === NULL) ? substr_replace($str, $replace, $offset) : substr_replace($str, $replace, $offset, $length);
		}
		else
		{
			preg_match_all('/./us', $str, $chars);
			preg_match_all('/./us', $replace, $change);

			$length = ($length === NULL) ? self::strlen($str) : $length;

			array_splice($chars[0], $offset, $length, $change[0]);

			$str = implode('', $chars[0]);
		}

		return $str;
	}

	/**
	 * UTF-8 version of ucfirst()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/ucfirst
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
	 * @see    http://php.net/ucwords
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