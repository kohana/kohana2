<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: html
 *  HTML helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class html {

	/**
	 * Method: specialchars
	 *  Convert special characters to HTML entities
	 *
	 * Parameters:
	 *  str           - string to convert
	 *  double_encode - encode existing entities
	 *
	 * Returns:
	 *  Entity-encoded string.
	 */
	public static function specialchars($str, $double_encode = TRUE)
	{
		// Do encode existing HTML entities (default)
		if ($double_encode == TRUE)
		{
			$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		}
		else
		{
			// Do not encode existing HTML entities
			// From PHP 5.2.3 this functionality is built-in, otherwise use a regex
			if (version_compare(PHP_VERSION, '5.2.3', '>='))
			{
				$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8', FALSE);
			}
			else
			{
				$str = preg_replace('/&(?!(?:#\d+|[a-z]+);)/i', '&amp;', $str);
				$str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $str);
			}
		}

		return $str;
	}

	/**
	 * Method: query_string
	 *  Creates an HTTP query string from an array.
	 *
	 * Parameters:
	 *  array - array of data to convert to string
	 *
	 * Returns:
	 *  An HTTP query string.
	 */
	public static function query_string($array)
	{
		if (empty($array) OR ! is_array($array))
			return '';

		$string = array();

		foreach($array as $key => $value)
		{
			$string[] = $key.'='.rawurlencode($value);
		}

		return implode('&', $string);
	}

	/**
	 * Method: anchor
	 *  Create HTML link anchors.
	 *
	 * Parameters:
	 *  uri        - URL or URI string
	 *  title      - link text
	 *  attributes - HTML anchor attributes
	 *  protocol   - non-default protocol, eg: https
	 *
	 * Returns:
	 *  An HTML link anchor.
	 */
	public static function anchor($uri, $title = FALSE, $attributes = FALSE, $protocol = FALSE)
	{
		if (strpos($uri, '://') === FALSE)
		{
			$site_url = url::site($uri, $protocol);
		}
		else
		{
			$site_url = $uri;
		}

		return
		// Parsed URL
		'<a href="'.$site_url.'"'
		// Attributes empty? Use an empty string
		.(empty($attributes) ? '' : self::attributes($attributes)).'>'
		// Title empty? Use the parsed URL
		.(empty($title) ? $site_url : $title).'</a>';
	}

	/**
	 * Method: file_anchor
	 *  Creates an HTML anchor to a file.
	 *
	 * Parameters:
	 *  file       - name of file to link to
	 *  title      - link text
	 *  attributes - HTML anchor attributes
	 *  protocol   - non-default protocol, eg: ftp
	 *
	 * Returns:
	 *  An HTML link anchor.
	 */
	public static function file_anchor($file, $title = FALSE, $attributes = FALSE, $protocol = FALSE)
	{
		return
		// Base URL + URI = full URL
		'<a href="'.url::base(FALSE, $protocol).$file.'"'
		// Attributes empty? Use an empty string
		.(empty($attributes) ? '' : self::attributes($attributes)).'>'
		// Title empty? Use the filename part of the URI
		.(empty($title) ? end(explode('/', $file)) : $title) .'</a>';
	}

	/**
	 * Method: panchor
	 *  Similar to anchor, but with the protocol parameter first.
	 *
	 * Parameters:
	 *  protocol   - link protocol
	 *  uri        - URI or URL to link to
	 *  title      - link text
	 *  attributes - HTML anchor attributes
	 *
	 * Returns:
	 *  An HTML link anchor.
	 */
	public static function panchor($protocol, $uri, $title = FALSE, $attributes = FALSE)
	{
		return self::anchor($uri, $title, $attributes, $protocol);
	}

	/**
	 * Method: mailto
	 *  Creates a email anchor.
	 *
	 * Parameters:
	 *  email      - email address to send to
	 *  title      - link text
	 *  attributes - HTML anchor attributes
	 *
	 * Returns:
	 *  An HTML link anchor.
	 */
	public static function mailto($email, $title = FALSE, $attributes = FALSE)
	{
		// Remove the subject or other parameters that do not need to be encoded
		$subject = FALSE;
		if (strpos($email, '?') !== FALSE)
		{
			list ($email, $subject) = explode('?', $email);
		}

		$safe = '';
		foreach(str_split($email) as $i => $letter)
		{
			switch (($letter == '@') ? rand(1, 2) : rand(1, 3))
			{
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		// Title defaults to the encoded email address
		$title = ($title == FALSE) ? $safe : $title;

		// URL encode the subject line
		$subject = ($subject == TRUE) ? '?'.rawurlencode($subject) : '';

		// Parse attributes
		$attributes = ($attributes == TRUE) ? self::attributes($attributes) : '';

		// Encoded start of the href="" is a static encoded version of 'mailto:'
		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$safe.$subject.'"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * Method: stylesheet
	 *  Creates a stylesheet link.
	 *
	 * Parameters:
	 *  style - filename
	 *  media - media type of stylesheet
	 *  index  - include the index_page in the link
	 *
	 * Returns:
	 *  An HTML stylesheet link.
	 */
	public static function stylesheet($style, $media = FALSE, $index = TRUE)
	{
		$compiled = '';

		if (is_array($style))
		{
			// Find default media type
			$media_type = is_array($media) ? array_shift($media) : $media;

			foreach($style as $name)
			{
				$compiled .= self::stylesheet($name, $media_type, $index)."\n";

				if (is_array($media))
				{
					// Advance the media type to the next type
					$media_type = array_shift($media);
				}
			}
		}
		else
		{
			// Add the suffix only when it's not already present
			$suffix   = (strpos($script, '.css') === FALSE) ? '.css' : '';
			$media    = ($media == FALSE) ? '' : ' media="'.$media.'"';
			$compiled = '<link rel="stylesheet" href="'.url::base((bool) $index).$style.$suffix.'"'.$media.' />';
		}

		return $compiled;
	}

	/**
	 * Method: script
	 *  Creates a script link.
	 *
	 * Parameters:
	 *  script - filename
	 *  index  - include the index_page in the link
	 *
	 * Returns:
	 *  An HTML script link.
	 */
	public static function script($script, $index = TRUE)
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
			// Add the suffix only when it's not already present
			$suffix   = (strpos($script, '.js') === FALSE) ? '.js' : '';
			$compiled = '<script type="text/javascript" src="'.url::base((bool) $index).$script.$suffix.'"></script>';
		}

		return $compiled;
	}

	/**
	 * Method: image
	 *  Creates a image link.
	 *
	 * Parameters:
	 *  attr  - array of html attributes, or an image name
	 *  index - include the index_page in the link
	 *
	 * Returns:
	 *  An HTML image link.
	 */
	public static function image($attr = NULL, $index = TRUE)
	{
		if ( ! is_array($attr))
		{
			$attr = array('src' => $attr);
		}

		if (strpos($attr['src'], '://') === FALSE)
		{
			// Make the src attribute into an absolute URL
			$attr['src'] = url::base($index).$attr['src'];
		}

		return '<img'.self::attributes($attr).' />';
	}

	/**
	 * Method: attributes
	 *  Compiles an array of HTML attributes into an attribute string.
	 *
	 * Parameters:
	 *  attrs - array of attributes
	 *
	 * Returns:
	 *  HTML attribute string.
	 */
	public static function attributes($attrs)
	{
		if (is_string($attrs))
			return ($attrs == FALSE) ? '' : ' '.$attrs;

		$compiled = '';

		foreach($attrs as $key => $val)
		{
			$compiled .= ' '.$key.'="'.$val.'"';
		}

		return $compiled;
	}

} // End html