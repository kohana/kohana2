<?php defined('SYSPATH') or die('No direct access allowed.');

class text {
	
	/**
	 * Word limiter
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	public static function limit_words($str, $limit = 100, $end_char = '&#8230;')
	{
		$limit = (int) $limit;
		
		if (trim($str) == '')
			return $str;

		if ($limit <= 0)
			return $end_char;

		preg_match('/^\s*(?:\S+\s*){1,'.$limit.'}/u', $str, $matches);

		if (strlen($matches[0]) == strlen($str))
		{
			$end_char = '';
		}

		return rtrim($matches[0]).$end_char;
	}
	
	/**
	 * Character limiter
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	public static function limit_chars($str, $limit = 100, $end_char = '&#8230;')
	{
		$limit = (int) $limit;

		if (trim($str) == '' OR utf8::strlen($str) <= $limit)
			return $str;

		return rtrim(utf8::substr($str, 0, $limit)).$end_char;
	}
	
	/**
	 * Character limiter that preserves words
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	public static function soft_limit_chars($str, $limit = 100, $end_char = '&#8230;')
	{
		$limit = (int) $limit;

		if (trim($str) == '' OR utf8::strlen($str) <= $limit)
			return $str;

		if ($limit <= 0)
			return $end_char;

		preg_match('/^.{'.($limit - 1).'}\S*/us', $str, $matches);

		if (strlen($matches[0]) == strlen($str))
		{
			$end_char = '';
		}

		return rtrim($matches[0]).$end_char;
	}

} // End text class