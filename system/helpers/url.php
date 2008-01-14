<?php defined('SYSPATH') or die('No direct script access.');
/**
 * URL helper class.
 *
 * $Id:$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class url_Core {

	/**
	 * Method: base
	 *  Base URL, with or without the index page.
	 *
	 * Parameters:
	 *  index    - include the index page
	 *  protocol - non-default protocol
	 *
	 * Returns:
	 *  The base URL string.
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
	 * Method: site
	 *  Fetches a site URL based on a URI segment.
	 *
	 * Parameters:
	 *  uri      - site URI to convert
	 *  protocol - non-default protocol
	 *
	 * Returns:
	 *  A URL string.
	 */
	public static function site($uri = '', $protocol = FALSE)
	{
		$uri = trim($uri, '/');

		$id = ''; // anchor#id
		$qs = ''; // anchor?query=string

		if (strpos($uri, '?') !== FALSE)
		{
			list ($uri, $qs) = explode('?', $uri, 2);
			$qs = '?'.$qs;
		}

		if (strpos($uri, '#') !== FALSE)
		{
			list ($uri, $id) = explode('#', $uri, 2);
			$id = '#'.$id;
		}

		$index_page = Config::item('core.index_page', TRUE);
		$url_suffix = ($uri != '') ? Config::item('core.url_suffix') : '';

		return self::base(FALSE, $protocol).$index_page.$uri.$url_suffix.$qs.$id;
	}

	/**
	 * Method: current
	 *  Fetches the current URI.
	 *
	 * Returns:
	 *  The current URI string.
	 */
	public static function current($qs = FALSE)
	{
		return Router::$current_uri.($qs === TRUE ? Router::$query_string : '');
	}

	/**
	 * Method: title
	 *  Convert a phrase to a URL-safe title.
	 *
	 * Parameters:
	 *  title     - phrase to convert
	 *  separator - word separator
	 *
	 * Returns:
	 *  A URL-safe title string.
	 */
	public static function title($title, $separator = '-')
	{
		$separator = ($separator == '-') ? '-' : '_';

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
	 * Method: redirect
	 *  Sends a page redirect header.
	 *
	 * Parameters:
	 *  uri    - site URI or URL to redirect to
	 *  method - HTTP method of redirect
	 *
	 * Returns:
	 *  A HTML anchor, but sends HTTP headers. The anchor should never be seen
	 *  by the user, unless their browser does not understand the headers sent.
	 */
	public static function redirect($uri = '', $method = '302')
	{
		if (Event::has_run('system.send_headers'))
			return;

		if (strpos($uri, '://') === FALSE)
		{
			$uri = self::site($uri);
		}

		if ($method == 'refresh')
		{
			header('Refresh: 0; url='.$uri);
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
		exit('<a href="'.$uri.'">'.$uri.'</a>');
	}

} // End url