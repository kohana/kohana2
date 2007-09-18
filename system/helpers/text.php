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

		preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

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
	
	/**
	 * Alternator
	 *
	 * @access	public
	 * @param	string (as many parameters as needed)
	 * @return	string
	 */		
	public static function alternate()
	{
		static $i;	

		if (func_num_args() == 0)
		{
			$i = 0;
			return '';
		}
		
		$args = func_get_args();
		return $args[($i++ % count($args))];
	}
	
	/**
	 * Random string generator
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @return	string
	 */
	public static function random($type = 'alnum', $length = 8)
	{
		switch ($type)
		{
			case 'unique':
				return md5(uniqid(mt_rand()));
			case '':
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			default:
				$pool = (string) $type;
			break;
		}

		$str = '';
		$pool_size = utf8::strlen($pool);

		for ($i = 0; $i < $length; $i++)
		{
			$str .= utf8::substr($pool, mt_rand(0, $pool_size - 1), 1);
		}

		return $str;
	}
	
	/**
	 * Word censor
	 *
	 * @access	public
	 * @param	string
	 * @param	mixed
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = FALSE)
	{
		if ( ! is_array($badwords))
		{
			$badwords = (array) $badwords;
		}

		foreach ($badwords as $key => $badword)
		{
			$badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
		}

		$regex = '('.implode('|', $badwords).')';

		if ( ! $replace_partial_words)
		{
			// Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
			$regex = '(?<=\b|\s|^)'.$regex.'(?=\b|\s|$)';
		}

		$regex = '!'.$regex.'!ui';

		if (utf8::strlen($replacement) == 1)
		{
			$regex .= 'e';
			return preg_replace($regex, 'str_repeat($replacement, utf8::strlen(\'$1\'))', $str);
		}

		return preg_replace($regex, $replacement, $str);
	}

} // End text class