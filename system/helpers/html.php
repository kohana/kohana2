<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana: The swift, secure, and lightweight PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * HTML Generation Helper
 *
 * $Id$
 *
 * @package     Kohana
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/helpers/html.html
 */
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
		// Do encode existing HTML entities (default)
		if ($double_encode == TRUE)
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

		// Do not encode existing HTML entities
		// From PHP 5.2.3 this functionality is built-in, otherwise use a regex
		if (version_compare(PHP_VERSION, '5.2.3', '>='))
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', FALSE);

		$str = preg_replace('/&(?!(?:#\d+|[a-z]+);)/i', '&amp;', $str);
		$str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $str);

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
			$id = ''; // anchor#id
			$qs = ''; // anchor?query=string

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
		else
		{
			$site_url = $uri;
		}

		$title = ($title == FALSE) ? $site_url : str_replace(' ', '&nbsp;', $title);

		$attributes = ($attributes == TRUE) ? self::attributes($attributes) : '';

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}

	public static function stylesheet($style, $index = FALSE, $media = FALSE)
	{
		$compiled = '';

		if (is_array($style))
		{
			foreach($style as $name)
			{
				$compiled .= self::stylesheet($name, $index, $media)."\n";
			}
		}
		else
		{
			$media = ($media == FALSE) ? '' : ' media="'.$media.'"';

			$compiled = '<link rel="stylesheet" href="'.url::base($index).$style.'.css"'.$media.' />';
		}

		return $compiled;
	}

	/**
	 * Script generator
	 *
	 * @access public
	 * @param  mixed    String or array of script names
	 * @param  boolean  Add index to the URL
	 * @return string
	 */
	public static function script($script, $index = FALSE)
	{
		$compiled = '';

		if (is_array($script))
		{
			foreach($script as $name)
			{
				$compiled .= self::script($name, $index)."\n";
			}
		}
		else
		{
			$compiled = '<script type="text/javascript" src="'.url::base($index).$script.'.js"></script>';
		}

		return $compiled;
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