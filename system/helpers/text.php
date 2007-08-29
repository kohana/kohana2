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
	 * @param   boolean
	 * @return	string
	 */
	public static function limit_chars($str, $limit = 100, $end_char = '&#8230;', $preserve_words = FALSE)
	{
		$limit = (int) $limit;
		$end_char = ($end_char === NULL) ? '&#8230;' : $end_char;

		if (trim($str) == '' OR utf8::strlen($str) <= $limit)
			return $str;
		
		if ($limit <= 0)
			return $end_char;
		
		if ( ! $preserve_words)
			return rtrim(utf8::substr($str, 0, $limit)).$end_char;
		
		preg_match('/^.{'.($limit - 1).'}\S*/us', $str, $matches);

		if (strlen($matches[0]) == strlen($str))
		{
			$end_char = '';
		}

		return rtrim($matches[0]).$end_char;
	}

} // End text class