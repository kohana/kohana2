<?php defined('SYSPATH') or die('No direct script access.');
/*
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

	/*
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

	/*
	 * Method: is_ascii
	 *  Tests whether a string contains only 7bit ASCII bytes. This is used to
	 *  determine when to use native functions or UTF-8 functions.
	 *
	 * Parameters:
	 *  str - string to check
	 *
	 * Returns:
	 *  TRUE or FALSE, whether the string is ASCII
	 */
	public static function is_ascii($str)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 * @access public
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
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Replaces accented UTF-8 characters by unaccented ASCII-7 'equivalents'.
	 *
	 * Original function (accents_to_ascii) written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @access public
	 * @param  string
	 * @param  integer (optional) -1 lowercase only, +1 uppercase only, 0 both cases
	 * @return string  accented chars replaced with ASCII equivalents
	 */
	public static function transliterate_to_ascii($str, $case = 0)
	{
		static $UTF8_LOWER_ACCENTS = NULL;
		static $UTF8_UPPER_ACCENTS = NULL;

		if ($case <= 0)
		{
			if ($UTF8_LOWER_ACCENTS === NULL)
			{
				$UTF8_LOWER_ACCENTS = array(
					'à' => 'a',  'ô' => 'o',  'ď' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o',
					'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  'ā' => 'a',  'ķ' => 'k',
					'ŝ' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o',
					'ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  'ẁ' => 'w',  'ċ' => 'c',  'õ' => 'o',
					'ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ș' => 's',  'ė' => 'e',  'ĉ' => 'c',
					'ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't',
					'ū' => 'u',  'č' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l',
					'ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z',
					'ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't',
					'ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o',
					'ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  'ĝ' => 'g',  'đ' => 'd',  'ĵ' => 'j',
					'ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o',
					'â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g',
					'ṁ' => 'm',  'ō' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a',
					'û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e',
				);
			}

			$str = str_replace(
				array_keys($UTF8_LOWER_ACCENTS),
				array_values($UTF8_LOWER_ACCENTS),
				$str
			);
		}

		if ($case >= 0)
		{
			if ($UTF8_UPPER_ACCENTS === NULL)
			{
				$UTF8_UPPER_ACCENTS = array(
					'À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O',
					'Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E',
					'Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O',
					'Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O',
					'Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C',
					'Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T',
					'Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  'Ł' => 'L',
					'Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z',
					'Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  'Ï' => 'I',  'Ḋ' => 'D',  'Ť' => 'T',
					'Ŗ' => 'R',  'Ä' => 'A',  'Í' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O',
					'Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  'Đ' => 'D',  'Ĵ' => 'J',
					'Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  'Ý' => 'Y',  'Ő' => 'O',
					'Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G',
					'Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  'Á' => 'A',
					'Û' => 'U',  'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae',
				);
			}

			$str = str_replace(
				array_keys($UTF8_UPPER_ACCENTS),
				array_values($UTF8_UPPER_ACCENTS),
				$str
			);
		}

		return $str;
	}

	/**
	 * UTF-8 version of strlen()
	 *
	 * @see    http://php.net/strlen
	 * @access public
	 * @param  string
	 * @return integer
	 */
	public static function strlen($str)
	{
		// Try mb_strlen() first because it's faster than combination of is_ascii() and strlen()
		if (SERVER_UTF8)
		{
			return mb_strlen($str);
		}
		if (self::is_ascii($str))
		{
			return strlen($str);
		}

		return strlen(utf8_decode($str));
	}

	/**
	 * UTF-8 version of strpos()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strpos
	 * @access public
	 * @param  string
	 * @param  string  search string
	 * @param  integer (optional) characters to offset
	 * @return integer (FALSE on failure)
	 */
	public static function strpos($str, $search, $offset = 0)
	{
		$offset = (int) $offset;

		if (SERVER_UTF8)
		{
			return mb_strpos($str, $search, $offset);
		}
		if (self::is_ascii($str) AND self::is_ascii($search))
		{
			return strpos($str, $search, $offset);
		}

		if ($offset == 0)
		{
			$array = explode($search, $str, 2);
			return (isset($array[1])) ? self::strlen($array[0]) : FALSE;
		}

		$str = self::substr($str, $offset);
		$pos = self::strpos($str, $search);
		return ($pos === FALSE) ? FALSE : $pos + $offset;
	}

	/**
	 * UTF-8 version of strrpos()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strrpos
	 * @access public
	 * @param  string
	 * @param  string  search string
	 * @param  integer (optional) characters to offset
	 * @return integer (FALSE on failure)
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		$offset = (int) $offset;

		if (SERVER_UTF8)
		{
			return mb_strrpos($str, $search, $offset);
		}
		if (self::is_ascii($str) AND self::is_ascii($search))
		{
			return strrpos($str, $search, $offset);
		}

		if ($offset == 0)
		{
			$array = explode($search, $str, -1);
			return (isset($array[0])) ? self::strlen(implode($search, $array)) : FALSE;
		}

		$str = self::substr($str, $offset);
		$pos = self::strrpos($str, $search);
		return ($pos === FALSE) ? FALSE : $pos + $offset;
	}

	/**
	 * UTF-8 version of substr()
	 *
	 * Original function written by Chris Smith <chris@jalakai.co.uk> for phputf8
	 *
	 * @see    http://php.net/substr
	 * @access public
	 * @param  string
	 * @param  integer characters to offset
	 * @param  integer (optional) length of return
	 * @return string  (FALSE on failure)
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		if (SERVER_UTF8)
		{
			return ($length === NULL) ? mb_substr($str, $offset) : mb_substr($str, $offset, $length);
		}
		if (self::is_ascii($str))
		{
			return ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}

		// Normalize params
		$str    = (string) $str;
		$strlen = self::strlen($str);
		$offset = (int) ($offset < 0) ? max(0, $strlen + $offset) : $offset; // Normalize to positive offset
		$length = ($length === NULL) ? NULL : (int) $length;

		// Impossible
		if ($length === 0 OR $offset >= $strlen OR ($length < 0 AND $length <= $offset - $strlen))
		{
			return '';
		}

		// Whole string
		if ($offset == 0 AND ($length === NULL OR $length >= $strlen))
		{
			return $str;
		}

		// Build regex
		$regex = '^';

		// Create an offset expression
		if ($offset > 0)
		{
			// PCRE repeating quantifiers must be less than 65536, so repeat when necessary
			$x = (int) ($offset / 65535);
			$y = (int) ($offset % 65535);
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= ($y == 0) ? '' : '.{'.$y.'}';
		}

		// Create a length expression
		if ($length === NULL)
		{
			$regex .= '(.*)'; // No length set, grab it all
		}
		else
		{
			// Find length from the left (positive length)
			if ($length > 0)
			{
				// Reduce length so that it can't go beyond the end of the string
				$length = min($strlen - $offset, $length);

				$x = (int) ($length / 65535);
				$y = (int) ($length % 65535);
				$regex .= '(';
				$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
				$regex .= '.{'.$y.'})';
			}
			// Find length from the right (negative length)
			else
			{
				$x = (int) (-$length / 65535);
				$y = (int) (-$length % 65535);
				$regex .= '(.*)';
				$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
				$regex .= '.{'.$y.'}';
			}
		}

		preg_match('/'.$regex.'/us', $str, $matches);
		return $matches[1];
	}

	/**
	 * UTF-8 version of substr_replace()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/substr_replace
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  integer characters to offset
	 * @param  integer (optional) length of part to replace
	 * @return string
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		if (self::is_ascii($str))
		{
			return ($length === NULL) ? substr_replace($str, $replacement, $offset) : substr_replace($str, $replacement, $offset, $length);
		}

		$length = ($length === NULL) ? self::strlen($str) : $length;
		preg_match_all('/./us', $str, $str_array);
		preg_match_all('/./us', $replacement, $replacement_array);

		array_splice($str_array[0], $offset, $length, $replacement_array[0]);
		return implode('', $str_array[0]);
	}

	/**
	 * UTF-8 version of strtolower()
	 *
	 * Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @see    http://php.net/strtolower
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function strtolower($str)
	{
		if (SERVER_UTF8)
		{
			return mb_strtolower($str);
		}
		if (self::is_ascii($str))
		{
			return strtolower($str);
		}

		static $UTF8_UPPER_TO_LOWER = NULL;

		if ($UTF8_UPPER_TO_LOWER === NULL)
		{
			$UTF8_UPPER_TO_LOWER = array(
				0x0041=>0x0061, 0x03A6=>0x03C6, 0x0162=>0x0163, 0x00C5=>0x00E5, 0x0042=>0x0062,
				0x0139=>0x013A, 0x00C1=>0x00E1, 0x0141=>0x0142, 0x038E=>0x03CD, 0x0100=>0x0101,
				0x0490=>0x0491, 0x0394=>0x03B4, 0x015A=>0x015B, 0x0044=>0x0064, 0x0393=>0x03B3,
				0x00D4=>0x00F4, 0x042A=>0x044A, 0x0419=>0x0439, 0x0112=>0x0113, 0x041C=>0x043C,
				0x015E=>0x015F, 0x0143=>0x0144, 0x00CE=>0x00EE, 0x040E=>0x045E, 0x042F=>0x044F,
				0x039A=>0x03BA, 0x0154=>0x0155, 0x0049=>0x0069, 0x0053=>0x0073, 0x1E1E=>0x1E1F,
				0x0134=>0x0135, 0x0427=>0x0447, 0x03A0=>0x03C0, 0x0418=>0x0438, 0x00D3=>0x00F3,
				0x0420=>0x0440, 0x0404=>0x0454, 0x0415=>0x0435, 0x0429=>0x0449, 0x014A=>0x014B,
				0x0411=>0x0431, 0x0409=>0x0459, 0x1E02=>0x1E03, 0x00D6=>0x00F6, 0x00D9=>0x00F9,
				0x004E=>0x006E, 0x0401=>0x0451, 0x03A4=>0x03C4, 0x0423=>0x0443, 0x015C=>0x015D,
				0x0403=>0x0453, 0x03A8=>0x03C8, 0x0158=>0x0159, 0x0047=>0x0067, 0x00C4=>0x00E4,
				0x0386=>0x03AC, 0x0389=>0x03AE, 0x0166=>0x0167, 0x039E=>0x03BE, 0x0164=>0x0165,
				0x0116=>0x0117, 0x0108=>0x0109, 0x0056=>0x0076, 0x00DE=>0x00FE, 0x0156=>0x0157,
				0x00DA=>0x00FA, 0x1E60=>0x1E61, 0x1E82=>0x1E83, 0x00C2=>0x00E2, 0x0118=>0x0119,
				0x0145=>0x0146, 0x0050=>0x0070, 0x0150=>0x0151, 0x042E=>0x044E, 0x0128=>0x0129,
				0x03A7=>0x03C7, 0x013D=>0x013E, 0x0422=>0x0442, 0x005A=>0x007A, 0x0428=>0x0448,
				0x03A1=>0x03C1, 0x1E80=>0x1E81, 0x016C=>0x016D, 0x00D5=>0x00F5, 0x0055=>0x0075,
				0x0176=>0x0177, 0x00DC=>0x00FC, 0x1E56=>0x1E57, 0x03A3=>0x03C3, 0x041A=>0x043A,
				0x004D=>0x006D, 0x016A=>0x016B, 0x0170=>0x0171, 0x0424=>0x0444, 0x00CC=>0x00EC,
				0x0168=>0x0169, 0x039F=>0x03BF, 0x004B=>0x006B, 0x00D2=>0x00F2, 0x00C0=>0x00E0,
				0x0414=>0x0434, 0x03A9=>0x03C9, 0x1E6A=>0x1E6B, 0x00C3=>0x00E3, 0x042D=>0x044D,
				0x0416=>0x0436, 0x01A0=>0x01A1, 0x010C=>0x010D, 0x011C=>0x011D, 0x00D0=>0x00F0,
				0x013B=>0x013C, 0x040F=>0x045F, 0x040A=>0x045A, 0x00C8=>0x00E8, 0x03A5=>0x03C5,
				0x0046=>0x0066, 0x00DD=>0x00FD, 0x0043=>0x0063, 0x021A=>0x021B, 0x00CA=>0x00EA,
				0x0399=>0x03B9, 0x0179=>0x017A, 0x00CF=>0x00EF, 0x01AF=>0x01B0, 0x0045=>0x0065,
				0x039B=>0x03BB, 0x0398=>0x03B8, 0x039C=>0x03BC, 0x040C=>0x045C, 0x041F=>0x043F,
				0x042C=>0x044C, 0x00DE=>0x00FE, 0x00D0=>0x00F0, 0x1EF2=>0x1EF3, 0x0048=>0x0068,
				0x00CB=>0x00EB, 0x0110=>0x0111, 0x0413=>0x0433, 0x012E=>0x012F, 0x00C6=>0x00E6,
				0x0058=>0x0078, 0x0160=>0x0161, 0x016E=>0x016F, 0x0391=>0x03B1, 0x0407=>0x0457,
				0x0172=>0x0173, 0x0178=>0x00FF, 0x004F=>0x006F, 0x041B=>0x043B, 0x0395=>0x03B5,
				0x0425=>0x0445, 0x0120=>0x0121, 0x017D=>0x017E, 0x017B=>0x017C, 0x0396=>0x03B6,
				0x0392=>0x03B2, 0x0388=>0x03AD, 0x1E84=>0x1E85, 0x0174=>0x0175, 0x0051=>0x0071,
				0x0417=>0x0437, 0x1E0A=>0x1E0B, 0x0147=>0x0148, 0x0104=>0x0105, 0x0408=>0x0458,
				0x014C=>0x014D, 0x00CD=>0x00ED, 0x0059=>0x0079, 0x010A=>0x010B, 0x038F=>0x03CE,
				0x0052=>0x0072, 0x0410=>0x0430, 0x0405=>0x0455, 0x0402=>0x0452, 0x0126=>0x0127,
				0x0136=>0x0137, 0x012A=>0x012B, 0x038A=>0x03AF, 0x042B=>0x044B, 0x004C=>0x006C,
				0x0397=>0x03B7, 0x0124=>0x0125, 0x0218=>0x0219, 0x00DB=>0x00FB, 0x011E=>0x011F,
				0x041E=>0x043E, 0x1E40=>0x1E41, 0x039D=>0x03BD, 0x0106=>0x0107, 0x03AB=>0x03CB,
				0x0426=>0x0446, 0x00DE=>0x00FE, 0x00C7=>0x00E7, 0x03AA=>0x03CA, 0x0421=>0x0441,
				0x0412=>0x0432, 0x010E=>0x010F, 0x00D8=>0x00F8, 0x0057=>0x0077, 0x011A=>0x011B,
				0x0054=>0x0074, 0x004A=>0x006A, 0x040B=>0x045B, 0x0406=>0x0456, 0x0102=>0x0103,
				0x039B=>0x03BB, 0x00D1=>0x00F1, 0x041D=>0x043D, 0x038C=>0x03CC, 0x00C9=>0x00E9,
				0x00D0=>0x00F0, 0x0407=>0x0457, 0x0122=>0x0123,
			);
		}

		$uni = self::to_unicode($str);

		if ($uni === FALSE)
		{
			return FALSE;
		}

		for ($i = 0, $c = count($uni); $i < $c; $i++)
		{
			if (isset($UTF8_UPPER_TO_LOWER[$uni[$i]]))
			{
				$uni[$i] = $UTF8_UPPER_TO_LOWER[$uni[$i]];
			}
		}

		return self::from_unicode($uni);
	}

	/**
	 * UTF-8 version of strtoupper()
	 *
	 * Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @see    http://php.net/strtoupper
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function strtoupper($str)
	{
		if (SERVER_UTF8)
		{
			return mb_strtoupper($str);
		}
		if (self::is_ascii($str))
		{
			return strtoupper($str);
		}

		static $UTF8_LOWER_TO_UPPER = NULL;

		if ($UTF8_LOWER_TO_UPPER === NULL)
		{
			$UTF8_LOWER_TO_UPPER = array(
				0x0061=>0x0041, 0x03C6=>0x03A6, 0x0163=>0x0162, 0x00E5=>0x00C5, 0x0062=>0x0042,
				0x013A=>0x0139, 0x00E1=>0x00C1, 0x0142=>0x0141, 0x03CD=>0x038E, 0x0101=>0x0100,
				0x0491=>0x0490, 0x03B4=>0x0394, 0x015B=>0x015A, 0x0064=>0x0044, 0x03B3=>0x0393,
				0x00F4=>0x00D4, 0x044A=>0x042A, 0x0439=>0x0419, 0x0113=>0x0112, 0x043C=>0x041C,
				0x015F=>0x015E, 0x0144=>0x0143, 0x00EE=>0x00CE, 0x045E=>0x040E, 0x044F=>0x042F,
				0x03BA=>0x039A, 0x0155=>0x0154, 0x0069=>0x0049, 0x0073=>0x0053, 0x1E1F=>0x1E1E,
				0x0135=>0x0134, 0x0447=>0x0427, 0x03C0=>0x03A0, 0x0438=>0x0418, 0x00F3=>0x00D3,
				0x0440=>0x0420, 0x0454=>0x0404, 0x0435=>0x0415, 0x0449=>0x0429, 0x014B=>0x014A,
				0x0431=>0x0411, 0x0459=>0x0409, 0x1E03=>0x1E02, 0x00F6=>0x00D6, 0x00F9=>0x00D9,
				0x006E=>0x004E, 0x0451=>0x0401, 0x03C4=>0x03A4, 0x0443=>0x0423, 0x015D=>0x015C,
				0x0453=>0x0403, 0x03C8=>0x03A8, 0x0159=>0x0158, 0x0067=>0x0047, 0x00E4=>0x00C4,
				0x03AC=>0x0386, 0x03AE=>0x0389, 0x0167=>0x0166, 0x03BE=>0x039E, 0x0165=>0x0164,
				0x0117=>0x0116, 0x0109=>0x0108, 0x0076=>0x0056, 0x00FE=>0x00DE, 0x0157=>0x0156,
				0x00FA=>0x00DA, 0x1E61=>0x1E60, 0x1E83=>0x1E82, 0x00E2=>0x00C2, 0x0119=>0x0118,
				0x0146=>0x0145, 0x0070=>0x0050, 0x0151=>0x0150, 0x044E=>0x042E, 0x0129=>0x0128,
				0x03C7=>0x03A7, 0x013E=>0x013D, 0x0442=>0x0422, 0x007A=>0x005A, 0x0448=>0x0428,
				0x03C1=>0x03A1, 0x1E81=>0x1E80, 0x016D=>0x016C, 0x00F5=>0x00D5, 0x0075=>0x0055,
				0x0177=>0x0176, 0x00FC=>0x00DC, 0x1E57=>0x1E56, 0x03C3=>0x03A3, 0x043A=>0x041A,
				0x006D=>0x004D, 0x016B=>0x016A, 0x0171=>0x0170, 0x0444=>0x0424, 0x00EC=>0x00CC,
				0x0169=>0x0168, 0x03BF=>0x039F, 0x006B=>0x004B, 0x00F2=>0x00D2, 0x00E0=>0x00C0,
				0x0434=>0x0414, 0x03C9=>0x03A9, 0x1E6B=>0x1E6A, 0x00E3=>0x00C3, 0x044D=>0x042D,
				0x0436=>0x0416, 0x01A1=>0x01A0, 0x010D=>0x010C, 0x011D=>0x011C, 0x00F0=>0x00D0,
				0x013C=>0x013B, 0x045F=>0x040F, 0x045A=>0x040A, 0x00E8=>0x00C8, 0x03C5=>0x03A5,
				0x0066=>0x0046, 0x00FD=>0x00DD, 0x0063=>0x0043, 0x021B=>0x021A, 0x00EA=>0x00CA,
				0x03B9=>0x0399, 0x017A=>0x0179, 0x00EF=>0x00CF, 0x01B0=>0x01AF, 0x0065=>0x0045,
				0x03BB=>0x039B, 0x03B8=>0x0398, 0x03BC=>0x039C, 0x045C=>0x040C, 0x043F=>0x041F,
				0x044C=>0x042C, 0x00FE=>0x00DE, 0x00F0=>0x00D0, 0x1EF3=>0x1EF2, 0x0068=>0x0048,
				0x00EB=>0x00CB, 0x0111=>0x0110, 0x0433=>0x0413, 0x012F=>0x012E, 0x00E6=>0x00C6,
				0x0078=>0x0058, 0x0161=>0x0160, 0x016F=>0x016E, 0x03B1=>0x0391, 0x0457=>0x0407,
				0x0173=>0x0172, 0x00FF=>0x0178, 0x006F=>0x004F, 0x043B=>0x041B, 0x03B5=>0x0395,
				0x0445=>0x0425, 0x0121=>0x0120, 0x017E=>0x017D, 0x017C=>0x017B, 0x03B6=>0x0396,
				0x03B2=>0x0392, 0x03AD=>0x0388, 0x1E85=>0x1E84, 0x0175=>0x0174, 0x0071=>0x0051,
				0x0437=>0x0417, 0x1E0B=>0x1E0A, 0x0148=>0x0147, 0x0105=>0x0104, 0x0458=>0x0408,
				0x014D=>0x014C, 0x00ED=>0x00CD, 0x0079=>0x0059, 0x010B=>0x010A, 0x03CE=>0x038F,
				0x0072=>0x0052, 0x0430=>0x0410, 0x0455=>0x0405, 0x0452=>0x0402, 0x0127=>0x0126,
				0x0137=>0x0136, 0x012B=>0x012A, 0x03AF=>0x038A, 0x044B=>0x042B, 0x006C=>0x004C,
				0x03B7=>0x0397, 0x0125=>0x0124, 0x0219=>0x0218, 0x00FB=>0x00DB, 0x011F=>0x011E,
				0x043E=>0x041E, 0x1E41=>0x1E40, 0x03BD=>0x039D, 0x0107=>0x0106, 0x03CB=>0x03AB,
				0x0446=>0x0426, 0x00FE=>0x00DE, 0x00E7=>0x00C7, 0x03CA=>0x03AA, 0x0441=>0x0421,
				0x0432=>0x0412, 0x010F=>0x010E, 0x00F8=>0x00D8, 0x0077=>0x0057, 0x011B=>0x011A,
				0x0074=>0x0054, 0x006A=>0x004A, 0x045B=>0x040B, 0x0456=>0x0406, 0x0103=>0x0102,
				0x03BB=>0x039B, 0x00F1=>0x00D1, 0x043D=>0x041D, 0x03CC=>0x038C, 0x00E9=>0x00C9,
				0x00F0=>0x00D0, 0x0457=>0x0407, 0x0123=>0x0122,
			);
		}

		$uni = self::to_unicode($str);

		if ($uni === FALSE)
		{
			return FALSE;
		}

		for ($i = 0, $c = count($uni); $i < $c; $i++)
		{
			if (isset($UTF8_LOWER_TO_UPPER[$uni[$i]]))
			{
				$uni[$i] = $UTF8_LOWER_TO_UPPER[$uni[$i]];
			}
		}

		return self::from_unicode($uni);
	}

	/**
	 * UTF-8 version of ucfirst()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/ucfirst
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function ucfirst($str)
	{
		if (self::is_ascii($str))
		{
			return ucfirst($str);
		}

		preg_match('/^(.?)(.*)$/us', $str, $matches);
		return self::strtoupper($matches[1]).$matches[2];
	}

	/**
	 * UTF-8 version of ucwords()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/ucwords
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function ucwords($str)
	{
		if (SERVER_UTF8)
		{
			return mb_convert_case($str, MB_CASE_TITLE);
		}
		if (self::is_ascii($str))
		{
			return ucwords($str);
		}

		// [\x0c\x09\x0b\x0a\x0d\x20] matches form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns.
		// This corresponds to the definition of a 'word' defined at http://php.net/ucwords
		return preg_replace(
			'/(?<=^|[\x0c\x09\x0b\x0a\x0d\x20])[^\x0c\x09\x0b\x0a\x0d\x20]/ue',
			'self::strtoupper(\'$0\')',
			$str
		);
	}

	/**
	 * UTF-8 version of strcasecmp()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strcasecmp
	 * @access public
	 * @param  string
	 * @param  string
	 * @return integer
	 */
	public static function strcasecmp($str1, $str2)
	{
		if (self::is_ascii($str1) AND self::is_ascii($str2))
		{
			return strcasecmp($str1, $str2);
		}

		$str1 = self::strtolower($str1);
		$str2 = self::strtolower($str2);
		return strcmp($str1, $str2);
	}

	/**
	 * UTF-8 version of str_ireplace()
	 *
	 * NOTE: it's not fast and gets slower if $search and/or $replace are arrays
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/str_ireplace
	 * @access public
	 * @param  string  (or array) text to replace
	 * @param  string  (or array) replacement text
	 * @param  string  (or array) subject text
	 * @param  integer (optional) number of matched and replaced needles will be returned in count which is passed by reference
	 * @return string
	 */
	public static function str_ireplace($search, $replace, $str, &$count = NULL)
	{
		if (self::is_ascii($search) AND self::is_ascii($replace) AND self::is_ascii($str))
		{
			return str_ireplace($search, $replace, $str, $count);
		}

		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = self::str_ireplace($search, $replace, $val, $count);
			}
			return $str;
		}

		if (is_array($search))
		{
			foreach (array_keys($search) as $k)
			{
				if (is_array($replace))
				{
					if (array_key_exists($k, $replace))
					{
						$str = self::str_ireplace($search[$k], $replace[$k], $str, $count);
					}
					else
					{
						$str = self::str_ireplace($search[$k], '', $str, $count);
					}
				}
				else
				{
					$str = self::str_ireplace($search[$k], $replace, $str, $count);
				}
			}
			return $str;
		}

		$search = self::strtolower($search);
		$str_lower = self::strtolower($str);

		$total_matched_strlen = 0;
		$i = 0;

		while (preg_match('/(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches))
		{
			$matched_strlen = strlen($matches[0]);
			$str_lower = substr($str_lower, $matched_strlen);

			$offset = $total_matched_strlen + strlen($matches[1]) + ($i * (strlen($replace) - 1));
			$str = substr_replace($str, $replace, $offset, strlen($search));

			$total_matched_strlen += $matched_strlen;
			$i++;
		}

		$count += $i;
		return $str;
	}

	/**
	 * UTF-8 version of stristr()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/stristr
	 * @access public
	 * @param  string
	 * @param  string  search string
	 * @return string  (FALSE if search string not found)
	 */
	public static function stristr($str, $search)
	{
		if (self::is_ascii($str) AND self::is_ascii($search))
		{
			return stristr($str, $search);
		}

		if ($search == '')
		{
			return $str;
		}

		$str_lower = self::strtolower($str);
		$search_lower = self::strtolower($search);

		preg_match('/^(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches);

		if (isset($matches[1]))
		{
			return substr($str, strlen($matches[1]));
		}

		return FALSE;
	}

	/**
	 * UTF-8 version of strspn()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strspn
	 * @access public
	 * @param  string
	 * @param  string  mask for search
	 * @param  integer (optional) start position of the string to examine
	 * @param  integer (optional) length of the string to examine
	 * @return integer
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ($str == '' OR $mask == '')
		{
			return 0;
		}

		if (self::is_ascii($str) AND self::is_ascii($mask))
		{
			return ($offset === NULL) ? strspn($str, $mask) : (($length === NULL) ? strspn($str, $mask, $offset) : strspn($str, $mask, $offset, $length));
		}

		if ($offset !== NULL OR $length !== NULL)
		{
			$str = self::substr($str, $offset, $length);
		}

		// Escape these characters:  - [ ] . : \ ^ /
		// The . and : are escaped to prevent possible warnings about POSIX regex elements
		$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
		preg_match('/^[^'.$mask.']+/u', $str, $matches);

		return (isset($matches[0])) ? self::strlen($matches[0]) : 0;
	}

	/**
	 * UTF-8 version of strcspn()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strspn
	 * @access public
	 * @param  string
	 * @param  string  negative mask for search
	 * @param  integer (optional) start position of the string to examine
	 * @param  integer (optional) length of the string to examine
	 * @return integer
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ($str == '' OR $mask == '')
		{
			return 0;
		}

		if (self::is_ascii($str) AND self::is_ascii($mask))
		{
			return ($offset === NULL) ? strcspn($str, $mask) : (($length === NULL) ? strcspn($str, $mask, $offset) : strcspn($str, $mask, $offset, $length));
		}

		if ($start !== NULL OR $length !== NULL)
		{
			$str = self::substr($str, $offset, $length);
		}

		// Escape these characters:  - [ ] . : \ ^ /
		// The . and : are escaped to prevent possible warnings about POSIX regex elements
		$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
		preg_match('/^[^'.$mask.']+/u', $str, $matches);

		return (isset($matches[0])) ? self::strlen($matches[0]) : 0;
	}

	/**
	 * UTF-8 version of str_pad()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/str_pad
	 * @access public
	 * @param  string
	 * @param  integer desired string length after padding
	 * @param  string  string to use as padding
	 * @param  define  STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH
	 * @return string
	 */
	public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		if (self::is_ascii($str) AND self::is_ascii($pad_str))
		{
			return str_pad($str, $final_str_length, $pad_str, $pad_type);
		}

		$str_length = self::strlen($str);

		if ($final_str_length <= 0 OR $final_str_length <= $str_length)
		{
			return $str;
		}

		$pad_str_length = self::strlen($pad_str);
		$pad_length = $final_str_length - $str_length;

		if ($pad_type == STR_PAD_RIGHT)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return self::substr($str.str_repeat($pad_str, $repeat), 0, $final_str_length);
		}

		if ($pad_type == STR_PAD_LEFT)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return self::substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)).$str;
		}

		if ($pad_type == STR_PAD_BOTH)
		{
			$pad_length /= 2;
			$pad_length_left = floor($pad_length);
			$pad_length_right = ceil($pad_length);
			$repeat_left = ceil($pad_length_left / $pad_str_length);
			$repeat_right = ceil($pad_length_right / $pad_str_length);

			$pad_left = self::substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
			$pad_right = self::substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_left);
			return $pad_left.$str.$pad_right;
		}

		trigger_error('utf8::str_pad: Unknown padding type (' . $type . ')', E_USER_ERROR);
	}

	/**
	 * UTF-8 version of str_split()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/str_split
	 * @access public
	 * @param  string
	 * @param  integer maximum length of chunk
	 * @return array   (FALSE if split_length < 1)
	 */
	public static function str_split($str, $split_length = 1)
	{
		$split_length = (int) $split_length;

		if (self::is_ascii($str))
		{
			return str_split($str, $split_length);
		}

		if ($split_length < 1)
		{
			return FALSE;
		}

		if (self::strlen($str) <= $split_length)
		{
			return array($str);
		}

		preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);

		return $matches[0];
	}

	/**
	 * UTF-8 version of strrev()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/strrev
	 * @access public
	 * @param  string
	 * @return string
	 */
	public static function strrev($str)
	{
		if (self::is_ascii($str))
		{
			return strrev($str);
		}

		preg_match_all('/./us', $str, $matches);
		return implode('', array_reverse($matches[0]));
	}

	/**
	 * UTF-8 version of trim()
	 *
	 * Note: if you don't need the $charlist you can use PHP's native trim function.
	 *
	 * Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @see    http://php.net/trim
	 * @access public
	 * @param  string
	 * @param  string  (optional) characters that need to be stripped (specify a range of characters with ..)
	 * @return string
	 */
	public static function trim($str, $charlist = NULL)
	{
		if ($charlist === NULL OR (self::is_ascii($str) AND self::is_ascii($charlist)))
		{
			return ($charlist === NULL) ? trim($str) : trim($str, $charlist);
		}

		return self::ltrim(self::rtrim($str, $charlist), $charlist);
	}

	/**
	 * UTF-8 version of ltrim()
	 *
	 * Note: if you don't need the $charlist you can use PHP's native ltrim function.
	 *
	 * Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @see    http://php.net/ltrim
	 * @access public
	 * @param  string
	 * @param  string  (optional) characters that need to be stripped (specify a range of characters with ..)
	 * @return string
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		if ($charlist === NULL OR (self::is_ascii($str) AND self::is_ascii($charlist)))
		{
			return ($charlist === NULL) ? ltrim($str) : ltrim($str, $charlist);
		}

		$charlist = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $charlist);

		// Try to support .. character ranges, if they cause errors drop its support
		$charlist_ranged = str_replace('\.\.', '-', $charlist);
		$str_ranged = @preg_replace('/^['.$charlist_ranged.']+/u', '', $str);

		return ($str_ranged !== NULL) ? $str_ranged : preg_replace('/^['.$charlist.']+/u', '', $str);
	}

	/**
	 * UTF-8 version of rtrim()
	 *
	 * Note: if you don't need the $charlist you can use PHP's native rtrim function.
	 *
	 * Original function written by Andreas Gohr <andi@splitbrain.org> for phputf8
	 *
	 * @see    http://php.net/rtrim
	 * @access public
	 * @param  string
	 * @param  string  (optional) characters that need to be stripped (specify a range of characters with ..)
	 * @return string
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		if ($charlist === NULL OR (self::is_ascii($str) AND self::is_ascii($charlist)))
		{
			return ($charlist === NULL) ? rtrim($str) : rtrim($str, $charlist);
		}

		$charlist = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $charlist);

		$charlist_ranged = str_replace('\.\.', '-', $charlist);
		$str_ranged = @preg_replace('/['.$charlist_ranged.']+$/u', '', $str);

		return ($str_ranged !== NULL) ? $str_ranged : preg_replace('/['.$charlist.']+$/u', '', $str);
	}

	/**
	 * UTF-8 version of ord()
	 *
	 * Original function written by Harry Fuecks <hfuecks@gmail.com> for phputf8
	 *
	 * @see    http://php.net/ord
	 * @access public
	 * @param  string  UTF-8 encoded character
	 * @return integer unicode ordinal for that character
	 */
	public static function ord($chr)
	{
		$ord0 = ord($chr);

		if ($ord0 >= 0 AND $ord0 <= 127)
		{
			return $ord0;
		}

		if ( ! isset($chr[1]))
		{
			trigger_error('Short sequence - at least 2 bytes expected, only 1 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord1 = ord($chr[1]);

		if ($ord0 >= 192 AND $ord0 <= 223)
		{
			return ($ord0 - 192) * 64 + ($ord1 - 128);
		}

		if ( ! isset($chr[2]))
		{
			trigger_error('Short sequence - at least 3 bytes expected, only 2 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord2 = ord($chr[2]);

		if ($ord0 >= 224 AND $ord0 <= 239)
		{
			return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
		}

		if ( ! isset($chr[3]))
		{
			trigger_error('Short sequence - at least 4 bytes expected, only 3 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord3 = ord($chr[3]);

		if ($ord0 >= 240 AND $ord0 <= 247)
		{
			return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2-128) * 64 + ($ord3 - 128);
		}

		if ( ! isset($chr[4]))
		{
			trigger_error('Short sequence - at least 5 bytes expected, only 4 seen', E_USER_WARNING);
			return FALSE;
		}

		$ord4 = ord($chr[4]);

		if ($ord0 >= 248 AND $ord0 <= 251)
		{
			return ($ord0 - 248) * 16777216 + ($ord1-128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
		}

		if ( ! isset($chr[5]))
		{
			trigger_error('Short sequence - at least 6 bytes expected, only 5 seen', E_USER_WARNING);
			return FALSE;
		}

		if ($ord0 >= 252 AND $ord0 <= 253)
		{
			return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($c[5]) - 128);
		}

		if ($ord0 >= 254 AND $ord0 <= 255)
		{
			trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0, E_USER_WARNING);
			return FALSE;
		}
	}

	/**
	 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
	 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 * Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @see    http://hsivonen.iki.fi/php-utf8/
	 * @access public
	 * @param  string  UTF-8 encoded string
	 * @return mixed   array of unicode code points or FALSE if UTF-8 invalid
	 */
	public static function to_unicode($str)
	{
		$mState = 0; // cached expected number of octets after the current octet until the beginning of the next UTF8 character sequence
		$mUcs4  = 0; // cached Unicode character
		$mBytes = 1; // cached expected number of octets in the current sequence

		$out = array();

		$len = strlen($str);

		for($i = 0; $i < $len; $i++)
		{
			$in = ord($str[$i]);

			if ($mState == 0)
			{
				// When mState is zero we expect either a US-ASCII character or a
				// multi-octet sequence.
				if (0 == (0x80 & $in))
				{
					// US-ASCII, pass straight through.
					$out[] = $in;
					$mBytes = 1;
				}
				elseif (0xC0 == (0xE0 & $in))
				{
					// First octet of 2 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;
				}
				elseif (0xE0 == (0xF0 & $in))
				{
					// First octet of 3 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;
				}
				elseif (0xF0 == (0xF8 & $in))
				{
					// First octet of 4 octet sequence
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;
				}
				elseif (0xF8 == (0xFC & $in))
				{
					// First octet of 5 octet sequence.
					//
					// This is illegal because the encoded codepoint must be either
					// (a) not the shortest form or
					// (b) outside the Unicode range of 0-0x10FFFF.
					// Rather than trying to resynchronize, we will carry on until the end
					// of the sequence and let the later error handling code catch it.
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 0x03) << 24;
					$mState = 4;
					$mBytes = 5;
				}
				elseif (0xFC == (0xFE & $in))
				{
					// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = $in;
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;
				}
				else
				{
					// Current octet is neither in the US-ASCII range nor a legal first octet of a multi-octet sequence.
					trigger_error('utf8::to_unicode: Illegal sequence identifier in UTF-8 at byte '.$i, E_USER_WARNING);
					return FALSE;
				}
			}
			else
			{
				// When mState is non-zero, we expect a continuation of the multi-octet sequence
				if (0x80 == (0xC0 & $in))
				{
					// Legal continuation
					$shift = ($mState - 1) * 6;
					$tmp = $in;
					$tmp = ($tmp & 0x0000003F) << $shift;
					$mUcs4 |= $tmp;

					// End of the multi-octet sequence. mUcs4 now contains the final Unicode codepoint to be output
					if (0 == --$mState)
					{
						// Check for illegal sequences and codepoints

						// From Unicode 3.1, non-shortest form is illegal
						if (((2 == $mBytes) AND ($mUcs4 < 0x0080)) OR
							((3 == $mBytes) AND ($mUcs4 < 0x0800)) OR
							((4 == $mBytes) AND ($mUcs4 < 0x10000)) OR
							(4 < $mBytes) OR
							// From Unicode 3.2, surrogate characters are illegal
							(($mUcs4 & 0xFFFFF800) == 0xD800) OR
							// Codepoints outside the Unicode range are illegal
							($mUcs4 > 0x10FFFF))
						{
							trigger_error('utf8::to_unicode: Illegal sequence or codepoint in UTF-8 at byte '.$i, E_USER_WARNING);
							return FALSE;
						}

						if (0xFEFF != $mUcs4)
						{
							// BOM is legal but we don't want to output it
							$out[] = $mUcs4;
						}

						// Initialize UTF-8 cache
						$mState = 0;
						$mUcs4  = 0;
						$mBytes = 1;
					}
				}
				else
				{
					// ((0xC0 & (*in) != 0x80) AND (mState != 0))
					// Incomplete multi-octet sequence
					trigger_error('utf8::to_unicode: Incomplete multi-octet sequence in UTF-8 at byte '.$i, E_USER_WARNING);
					return FALSE;
				}
			}
		}

		return $out;
	}

	/**
	 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
	 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 * Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @see    http://hsivonen.iki.fi/php-utf8/
	 * @access public
	 * @param  array  of unicode code points representing a string
	 * @return mixed  UTF-8 string or FALSE if array contains invalid code points
	 */
	public static function from_unicode($arr)
	{
		ob_start();

		foreach (array_keys($arr) as $k)
		{
			// ASCII range (including control chars)
			if (($arr[$k] >= 0) AND ($arr[$k] <= 0x007f))
			{
				echo chr($arr[$k]);
			}
			// 2 byte sequence
			elseif ($arr[$k] <= 0x07ff)
			{
				echo chr(0xc0 | ($arr[$k] >> 6));
				echo chr(0x80 | ($arr[$k] & 0x003f));
			}
			// Byte order mark (skip)
			elseif($arr[$k] == 0xFEFF)
			{
				// nop -- zap the BOM
			}
			// Test for illegal surrogates
			elseif ($arr[$k] >= 0xD800 AND $arr[$k] <= 0xDFFF)
			{
				// Found a surrogate
				trigger_error('utf8::from_unicode: Illegal surrogate at index: '.$k.', value: '.$arr[$k], E_USER_WARNING);
				return FALSE;
			}
			// 3 byte sequence
			elseif ($arr[$k] <= 0xffff)
			{
				echo chr(0xe0 | ($arr[$k] >> 12));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
				echo chr(0x80 | ($arr[$k] & 0x003f));
			}
			// 4 byte sequence
			elseif ($arr[$k] <= 0x10ffff)
			{
				echo chr(0xf0 | ($arr[$k] >> 18));
				echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
				echo chr(0x80 | ($arr[$k] & 0x3f));
			}
			// Out of range
			else
			{
				trigger_error('utf8::from_unicode: Codepoint out of Unicode range at index: '.$k.', value: '.$arr[$k], E_USER_WARNING);
				return FALSE;
			}
		}

		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

} // End utf8 class