<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Format helper class.
 *
 * $Id: form.php 2404 2008-04-02 09:24:52Z Geert $
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
		if (empty($number))
			return '';

		// Number of parts
		// $parts = strlen(preg_replace('/\D+/', '', $format));

		// Create the search string
		// $search = preg_replace('/[()]/', '\\\\$0', $format);
		// $search = preg_replace('/\d/', '(\d{$0})', $search);

		// Create the replace string
		// $replace =

		return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1-$2-$3', $number);
	}

} // End format