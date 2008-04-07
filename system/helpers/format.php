<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Format helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class format_Core {

	/**
	 * Formats a phone number according to the specified format.
	 *
	 * @param   string  phone number
	 * @param   string  format string
	 * @return  string
	 */
	public static function phone($number, $format = '3-3-4')
	{
		// Get rid of all non-digit characters in number string
		$number = preg_replace('/\D+/', '', (string) $number);

		// Array of digits we need for a valid format
		$format_parts = preg_split('/\D+/', $format);

		// Number must match digit count of a valid format
		if (strlen($number) !== array_sum($format_parts))
			return '';

		// Build regex
		$regex = '(\d{'.implode('})(\d{', $format_parts).'})';

		// Build replace string
		for ($i = 1, $c = count($format_parts); $i <= $c; $i++)
		{
			$format = preg_replace('/(?<!\$)[1-9][0-9]*/', '\$'.$i, $format, 1);
		}

		// Hocus pocus!
		return preg_replace('/^'.$regex.'$/', $format, $number);
	}

} // End format