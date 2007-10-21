<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Cookie Class
 *
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/helpers/cookie.html
 */
class url {

	/**
	 * Base URL, with or without the index page
	 *
	 * @access public
	 * @param  boolean
	 * @param  string
	 * @return string
	 */
	public static function base($index = FALSE, $protocol = FALSE)
	{
		$protocol = ($protocol == FALSE) ? Config::item('core.site_protocol') : strtolower($protocol);

 		$base_url = $protocol.'://'.Config::item('core.site_domain', TRUE);

		if ($index == TRUE AND $index = Config::item('core.index_page'))
		{
			$base_url = $base_url.$index.'/';
		}

		return $base_url;
	}

	/**
	 * Site URL from a URI
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function site($uri = '', $protocol = FALSE)
	{
		$uri = trim($uri, '/');

		$index_page = Config::item('core.index_page', TRUE);
		$url_suffix = Config::item('core.url_suffix');

		return self::base(FALSE, $protocol).$index_page.$uri.$url_suffix;
	}

	/**
	 * URL-safe Title
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function title($title, $separator = 'dash')
	{
		$separator = ($separator == 'dash') ? '-' : '_';

		// Replace all dashes, underscores and whitespace by the separator
		$title = preg_replace('/[-_\s]+/', $separator, $title);

		// Replace accented characters by their unaccented equivalents
		$title = utf8::transliterate_to_ascii($title);

		// Remove all characters that are not a-z, 0-9, or the separator
		$title = preg_replace('/[^a-z0-9'.$separator.']+/', '', strtolower($title));

		// Trim separators from the beginning and end
		$title = trim($title, $separator);

		return $title;
	}

	/**
	 * Send a redirect header
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public static function redirect($uri = '', $method = '302')
	{
		if (strpos($uri, '://') === FALSE)
		{
			$uri = self::site($uri);
		}

		if ($method == 'refresh')
		{
			header('Refresh: 0; url='. $uri);
		}
		else
		{
			$codes = array
			(
				'300' => 'Multiple Choices',
				'301' => 'Moved Permanently',
				'302' => 'Found',
				'303' => 'See Other',
				'304' => 'Not Modified',
				'305' => 'Use Proxy',
				'307' => 'Temporary Redirect'
			);

			$method = isset($codes[$method]) ? $method : '302';

			header('HTTP/1.1 '.$method.' '.$codes[$method]);
			header('Location: '.$uri);
		}

		// Last resort, exit and display the URL
		exit('<a href="'.$uri.'">'.$uri.'</a>.');
	}

} // End url class