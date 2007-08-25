<?php defined('SYSPATH') or die('No direct access allowed.');

class html {

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