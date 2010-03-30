<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The inflector helper class provides convenience methods for
 * pluralization and singularization of words as well as other methods
 * for working with phrases.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class inflector_Core {

	// Cached inflections
	protected static $cache = array();

	// Uncountable and irregular words
	protected static $uncountable;
	protected static $irregular;

	/**
	 * This method checks if a given string is an uncountable word. It
	 * uses a word list defined in the `inflector.php` configuration
	 * file.
	 *
	 * ###### Example
	 *
	 *     echo Kohana::debug(inflector::uncountable('money'));
	 *     
	 *     // Output:
	 *     (boolean) true
	 *     
	 *     echo Kohana::debug(inflector::uncountable('book'));
	 *     
	 *     // Output:
	 *     (boolean) false
	 *
	 * @param   string   $str word to check
	 * @return  boolean
	 */
	public static function uncountable($str)
	{
		if (inflector::$uncountable === NULL)
		{
			// Cache uncountables
			inflector::$uncountable = Kohana::config('inflector.uncountable');

			// Make uncountables mirroed
			inflector::$uncountable = array_combine(inflector::$uncountable, inflector::$uncountable);
		}

		return isset(inflector::$uncountable[strtolower($str)]);
	}

	/**
	 * This method returns a singularized plural word.
	 *
	 * The second function argument specifies the number of "things"
	 * being represented so that the proper
	 * pluralization/singularization can be applied.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::singular('chupacabra_killings', 1));
	 *     
	 *     // Output:
	 *     (string) chupacabra_killing
	 *
	 * @param   string   $str   Word to singularize
	 * @param   integer  $count Number of things to determine singularizatio
	 * @return  string
	 */
	public static function singular($str, $count = NULL) {
		$parts = explode('_', $str);

		$last = inflector::_singular(array_pop($parts), $count);

		$pre = implode('_', $parts);
		if (strlen($pre))
			$pre .= '_';

		return $pre.$last;
	}


	/**
	 * This method returns a singularized plural word.
	 *
	 * The second function argument specifies the number of "things"
	 * being represented so that the proper
	 * pluralization/singularization can be applied.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::_singular('books'));
	 *     
	 *     // Output:
	 *     (string) book
	 *
	 * @param   string   $str   Word to singularize
	 * @param   integer  $count Number of things to determine singularizatio
	 * @return  string
	 */
	public static function _singular($str, $count = NULL)
	{
		// Remove garbage
		$str = strtolower(trim($str));

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with a single count
		if ($count === 0 OR $count > 1)
			return $str;

		// Cache key name
		$key = 'singular_'.$str.$count;

		if (isset(inflector::$cache[$key]))
			return inflector::$cache[$key];

		if (inflector::uncountable($str))
			return inflector::$cache[$key] = $str;

		if (empty(inflector::$irregular))
		{
			// Cache irregular words
			inflector::$irregular = Kohana::config('inflector.irregular');
		}

		if ($irregular = array_search($str, inflector::$irregular))
		{
			$str = $irregular;
		}
		elseif (preg_match('/[sxz]es$/', $str) OR preg_match('/[^aeioudgkprt]hes$/', $str))
		{
			// Remove "es"
			$str = substr($str, 0, -2);
		}
		elseif (preg_match('/[^aeiou]ies$/', $str))
		{
			$str = substr($str, 0, -3).'y';
		}
		elseif (substr($str, -1) === 's' AND substr($str, -2) !== 'ss')
		{
			$str = substr($str, 0, -1);
		}

		return inflector::$cache[$key] = $str;
	}

	/**
	 * This method returns a pluralized singular word.
	 *
	 * The second function argument specifies the number of "things"
	 * being represented so that the proper
	 * pluralization/singularization can be applied.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::plural('mating_bunny', 2));
	 *     
	 *     // Output:
	 *     (string) mating_bunnies
	 *
	 * @param   string   $str   Word to pluralize
	 * @param   integer  $count Number of things to determine singularizatio
	 * @return  string
	 */
	public static function plural($str, $count = NULL)
	{
		if ( ! $str)
			return $str;

		$parts = explode('_', $str);

		$last = inflector::_plural(array_pop($parts), $count);

		$pre = implode('_', $parts);
		if (strlen($pre))
			$pre .= '_';

		return $pre.$last;
	}


	/**
	 * This method returns a pluralized singular word.
	 *
	 * The second function argument specifies the number of "things"
	 * being represented so that the proper
	 * pluralization/singularization can be applied.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::_plural('plate'));
	 *     
	 *     // Output:
	 *     (string) plates
	 *
	 * @param   string   $str   Word to pluralize
	 * @param   integer  $count Number of things to determine singularizatio
	 * @return  string
	 */
	public static function _plural($str, $count = NULL)
	{
		// Remove garbage
		$str = strtolower(trim($str));

		if (is_string($count))
		{
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with singular
		if ($count === 1)
			return $str;

		// Cache key name
		$key = 'plural_'.$str.$count;

		if (isset(inflector::$cache[$key]))
			return inflector::$cache[$key];

		if (inflector::uncountable($str))
			return inflector::$cache[$key] = $str;

		if (empty(inflector::$irregular))
		{
			// Cache irregular words
			inflector::$irregular = Kohana::config('inflector.irregular');
		}

		if (isset(inflector::$irregular[$str]))
		{
			$str = inflector::$irregular[$str];
		}
		elseif (preg_match('/[sxz]$/', $str) OR preg_match('/[^aeioudgkprt]h$/', $str))
		{
			$str .= 'es';
		}
		elseif (preg_match('/[^aeiou]y$/', $str))
		{
			// Change "y" to "ies"
			$str = substr_replace($str, 'ies', -1);
		}
		else
		{
			$str .= 's';
		}

		// Set the cache and return
		return inflector::$cache[$key] = $str;
	}

	/**
	 * This method returns a given word in possessive form.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::possessive('Matilda'));
	 *     
	 *     // Output:
	 *     (string) Matilda's
	 *
	 * @param   string  $str Word to to make possessive
	 * @return  string
	 */
	public static function possessive($str)
	{
		$length = strlen($str);

		if (substr($str, $length - 1, $length) == 's')
		{
			return $str.'\'';
		}

		return $str.'\'s';
	}

	/**
	 * This method returns a given string in CamelCase form.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::camelize('my name is pookie'));
	 *     
	 *     // Output:
	 *     (string) myNameIsPookie
	 *
	 * @param   string  $str Phrase to CamelCase
	 * @return  string
	 */
	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * This method returns a given string with spaces replaced by
	 * underscores.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(inflector::underscore('dude where is my car?'));
	 *     
	 *     // Output:
	 *     (string) dude_where_is_my_car?
	 *
	 * @param   string  $str Phrase to underscore
	 * @return  string
	 */
	public static function underscore($str)
	{
		return trim(preg_replace('/[\s_]+/', '_', $str), '_');
	}

	/**
	 * This method returns a given string with hyphens or underscores
	 * replaced by spaces.
	 *
	 * ###### Example
	 *     
	 *     echo
	 *     Kohana::debug(inflector::humanize('it_is_a_break-dancing_stripper_emergency!'));
	 *     
	 *     // Output:
	 *     (string) it is a break dancing stripper emergency!
	 * 
	 * @param   string  $str Phrase to make human-readable
	 * @return  string
	 */
	public static function humanize($str)
	{
		return trim(preg_replace('/[_-\s]+/', ' ', $str));
	}

} // End inflector