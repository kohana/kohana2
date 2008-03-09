<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Assert helper class.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class assert_Core {

	public static function true($value)
	{
		if ($value != TRUE)
			throw new Exception;
	}

	public static function true_strict($value)
	{
		if ($value !== TRUE)
			throw new Exception;
	}

	public static function false($value)
	{
		if ($value != FALSE)
			throw new Exception;
	}

	public static function false_strict($value)
	{
		if ($value !== FALSE)
			throw new Exception;
	}

	public static function equal($value1, $value2)
	{
		if ($value1 != $value2)
			throw new Exception;
	}

	public static function not_equal($value1, $value2)
	{
		if ($value1 == $value2)
			throw new Exception;
	}

	public static function identical($value1, $value2)
	{
		if ($value1 !== $value2)
			throw new Exception;
	}

	public static function not_identical($value1, $value2)
	{
		if ($value1 === $value2)
			throw new Exception;
	}

	public static function is_boolean($value)
	{
		if ( ! is_bool($value))
			throw new Exception;
	}

	public static function not_boolean($value)
	{
		if (is_bool($value))
			throw new Exception;
	}

	public static function is_integer($value)
	{
		if ( ! is_int($value))
			throw new Exception;
	}

	public static function not_integer($value)
	{
		if (is_int($value))
			throw new Exception;
	}

	public static function is_float($value)
	{
		if ( ! is_float($value))
			throw new Exception;
	}

	public static function not_float($value)
	{
		if (is_float($value))
			throw new Exception;
	}

	public static function is_array($value)
	{
		if ( ! is_array($value))
			throw new Exception;
	}

	public static function not_array($value)
	{
		if (is_array($value))
			throw new Exception;
	}

	public static function is_object($value)
	{
		if ( ! is_object($value))
			throw new Exception;
	}

	public static function not_object($value)
	{
		if (is_object($value))
			throw new Exception;
	}

	public static function is_null($value)
	{
		if ($value !== NULL)
			throw new Exception;
	}

	public static function not_null($value)
	{
		if ($value === NULL)
			throw new Exception;
	}

	public static function is_empty($value)
	{
		if ( ! empty($value))
			throw new Exception;
	}

	public static function not_empty($value)
	{
		if (empty($value))
			throw new Exception;
	}

	public static function pattern($value, $regex)
	{
		if ( ! preg_match($regex, $value))
			throw new Exception;
	}

	public static function not_pattern($value, $regex)
	{
		if (preg_match($regex, $value))
			throw new Exception;
	}

} // End assert