<?php

namespace Helper;

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * The format helper provides convenience methods for many common
 * formatting needs; such as, converting a phone number `5054432674`
 * to a human readable phone number `505-443-2674`.
 *
 * #### Using the format helper
 *
 *     $str = "232.553.2662";
 *     
 *     echo \Kernel\Kohana::debug(format::phone($str));
 *     
 *     // Output:
 *     (string) 232-553-2662
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class format {

	/**
	 * This method formats a number according to the current locale.
	 * 
	 * The second function argument specifies the decimal precision
	 * with which to format the provided number - passing FALSE
	 * returns the number formatted without decimal precision.
	 *
	 * ###### Example
	 *
	 *     // Using the en_US locale
	 *     echo \Kernel\Kohana::debug(format::number(232, 2));
	 *     
	 *     // Output:
	 *     (string) 232.00
	 *
	 * @param   mixed $number   Number to be formatted
	 * @param   mixed $decimals Number of fractional digits or TRUE to use the locale default
	 * @return  string
	 */
	public static function number($number, $decimals = 0)
	{
		$locale = localeconv();

		if ($decimals === TRUE)
			return number_format($number, $locale['frac_digits'], $locale['decimal_point'], $locale['thousands_sep']);

		return number_format($number, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
	}

	/**
	 * This method formats a phone number to be human readable.
	 *
	 * The second function argument is a formatting pattern denoting
	 * the number of digits per block and the seperator between blocks.
	 *
	 * ###### Example
	 *
	 *    $str = "232.553.2662";
	 *    
	 *    // Using the default pattern: 3-3-4
	 *    echo \Kernel\Kohana::debug(format::phone($str));
	 *    
	 *    // Output:
	 *    (string) 232-553-2662
	 *    
	 *    $str = "232-553-2662";
	 *    
	 *    // Using a different pattern: 3.3.4
	 *    echo \Kernel\Kohana::debug(format::phone($str, '3.3.4'));
	 *    
	 *    // Output:
	 *    (string) 232.553.2662
	 *
	 * @param   string  $number Number to be formatted
	 * @param   string  $format Formatting pattern, default: 3-3-4
	 * @return  string
	 */
	public static function phone($number, $format = '3-3-4')
	{
		// Get rid of all non-digit characters in number string
		$number_clean = preg_replace('/\D+/', '', (string) $number);

		// Array of digits we need for a valid format
		$format_parts = preg_split('/[^1-9][^0-9]*/', $format, -1, PREG_SPLIT_NO_EMPTY);

		// Number must match digit count of a valid format
		if (strlen($number_clean) !== array_sum($format_parts))
			return $number;

		// Build regex
		$regex = '(\d{'.implode('})(\d{', $format_parts).'})';

		// Build replace string
		for ($i = 1, $c = count($format_parts); $i <= $c; $i++)
		{
			$format = preg_replace('/(?<!\$)[1-9][0-9]*/', '\$'.$i, $format, 1);
		}

		// Hocus pocus!
		return preg_replace('/^'.$regex.'$/', $format, $number_clean);
	}

	/**
	 * This method formats a given URL, prefixing `http://` to the URL
	 * if there is none, or if so, returning it unchanged.
	 *
	 * ###### Example
	 *
	 *    echo \Kernel\Kohana::debug(format::url('console/users'));
	 *    
	 *    // Output:
	 *    (string) http://console/users
	 *
	 * @param   string  $str URL string
	 * @return  string
	 */
	public static function url($str = '')
	{
		// Clear protocol-only strings like "http://"
		if ($str === '' OR substr($str, -3) === '://')
			return '';

		// If no protocol given, prepend "http://" by default
		if (strpos($str, '://') === FALSE)
			return 'http://'.$str;

		// Return the original URL
		return $str;
	}

	/**
	 * Normalizes a hexadecimal HTML color value, converting all characters
	 * to lowercase, prefixing the value with an octothorpe ("#") and
	 * expanding shorthand notation to a valid six character
	 * hexadecimal value.
	 *
	 * [!!] If passed an invalid value, this method will return an *empty* string.
	 *
	 * ###### Example
	 *
	 *    echo \Kernel\Kohana::debug(format::color('f60'));
	 *    
	 *    // Output:
	 *    (string) #ff6600
	 *
	 * @param   string  $str Hexadecimal HTML color value
	 * @return  string
	 */
	public static function color($str = '')
	{
		// Reject invalid values
		if ( ! \Helper\valid::color($str))
			return '';

		// Convert to lowercase
		$str = strtolower($str);

		// Prepend "#"
		if ($str[0] !== '#')
		{
			$str = '#'.$str;
		}

		// Expand short notation
		if (strlen($str) === 4)
		{
			$str = '#'.$str[1].$str[1].$str[2].$str[2].$str[3].$str[3];
		}

		return $str;
	}

} // End format
