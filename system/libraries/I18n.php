<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana I18N System
 *
 * $Id: Cache.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

/**
 * Loads the configured driver and validates it.
 *
 * @param   string  Text to output
 * @param   array   Key/Value pairs of arguments to replace in the string
 * @return  string  Translated text
 */
function __($string, $args = NULL)
{
	if (I18n::get_locale() != Kohana::config('locale.language'))
	{
		$string = I18n::get_text($string);
	}

	if ($args === NULL)
		return $string;

	return strtr($string, $args);
}

class I18n_Core
{
	protected static $locale = 'en_US';
	protected static $translations = array();

	public static function set_locale($locale)
	{
		// Reset the translations array
		I18n::$translations = array();

		I18n::$locale = $locale;
	}

	public static function get_locale()
	{
		return I18n::$locale;
	}

	public static function get_text($string)
	{
		$locale = explode('_', I18n::$locale);
		if ( ! I18n::$translations)
		{
			if (I18n::$translations = Kohana::find_file('i18n', $locale[0]) AND isset(I18n::$translation[$string]))
			{
				// Merge the locale translations with the main language translation
				if ($locale = Kohana::find_file('i18n', $locale[0].'/'.$locale[1]))
					I18n::$translations = array_merge(I18n::$translations, $locale);

				return I18n::$translations[$string];
			}
			else
				return $string;
		}
		else
		{
			if (isset(I18n::$translations[$string]))
				return I18n::$translations[$string];
			else
				return $string;
		}
	}

}