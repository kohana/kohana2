<?php defined('SYSPATH') or die('No direct access allowed.');

class html {
	
	/**
	 * Convert special characters to HTML entities
	 *
	 * @access public
	 * @param  string
	 * @param  boolean
	 * @return string
	 */
	public static function specialchars($str, $double_encode = TRUE)
	{
		// Do encode existing html entities (default)
		if ($double_encode)
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		
		// Do not encode existing html entities
		// From PHP 5.2.3 this functionality is built-in, otherwise use a regex
		if (version_compare(PHP_VERSION, '5.2.3', '>='))
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', FALSE);
		
		$str = preg_replace('/&(?!(?:#\d+|[a-z]+);)/i', '&amp;', $str);
		$str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#039;', '&quot;'), $str);
		
		return $str;
	}
	
	/**
	 * HTML anchor generator
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return string
	 */
	public static function anchor($uri, $title = FALSE, $attributes = FALSE)
	{
		if (strpos($uri, '://') === FALSE)
		{
			$id = ''; // Anchor #id
			$qs = ''; // Anchor ?query=string

			if (($start = strpos($uri, '?')) !== FALSE)
			{
				$qs  = substr($uri, $start);
				$uri = substr($uri, 0, $start);

				if (($start = strpos($qs, '#')) !== FALSE)
				{
					$id = substr($qs, $start);
					$qs = substr($qs, 0, $start);
				}
			}
			elseif (($start = strpos($uri, '#')) !== FALSE)
			{
				$id  = substr($uri, $start);
				$uri = substr($uri, 0, $start);
			}

			$site_url = url::site($uri).$qs.$id;
		}

		if ($title == '')
		{
			$title = $site_url;
		}

		$attributes = ($attributes == TRUE) ? self::attributes($attributes) : '';

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * HTML Attribute Parser
	 *
	 * @access public
	 * @param  mixed
	 * @return string
	 */
	public static function attributes($attrs)
	{
		if (is_string($attrs))
		{
			return ($attrs == FALSE) ? '' : ' '.$attrs;
		}
		else
		{
			$compiled = '';

			foreach($attrs as $key => $val)
			{
				$compiled .= ' '.$key.'="'.$val.'"';
			}

			return $compiled;
		}
	}

} // End html class