<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Controls headers that effect client caching of pages
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class expires_Core {

	/**
	 * Sets the amount of time before a page expires
	 *
	 * @param  integer Seconds before the page expires 
	 * @return boolean
	 */
	public static function set($seconds = 60)
	{
		if (expires::check_headers())
		{
			$now = time();
			$expires = $now + $seconds;

			header('Last-Modified: '.gmdate('D, d M Y H:i:s T', $now));

			// HTTP 1.0
			header('Expires: '.gmdate('D, d M Y H:i:s T', $expires));

			// HTTP 1.1
			header('Cache-Control: max-age='.$seconds);

			return $expires;
		}

		return FALSE;
	}

	/**
	 * Parses the If-Modified-Since header
	 *
	 * @return  integer|boolean Timestamp or FALSE when header is lacking or malformed
	 */
	public static function get()
	{
		if ( ! empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			// Some versions of IE6 append "; length=####"
			if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== FALSE)
			{
				$mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
			}
			else
			{
				$mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			}

			return strtotime($mod_time);
		}

		return FALSE;
	}

	/**
	 * Checks to see if content should be updated otherwise sends Not Modified status
	 * and exits.
	 *
	 * @uses    exit()
	 * @uses    expires::get()
	 *
	 * @param   integer         Maximum age of the content in seconds
	 * @return  integer|boolean Timestamp of the If-Modified-Since header or FALSE when header is lacking or malformed
	 */
	public static function check($seconds = 60)
	{
		if ($last_modified = expires::get() AND expires::check_headers())
		{
			$expires = $last_modified + $seconds;
			$max_age = $expires - time();

			if ($max_age > 0)
			{
				// Content has not expired
				header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s T', $last_modified));

				// HTTP 1.0
				header('Expires: '.gmdate('D, d M Y H:i:s T', $expires));

				// HTTP 1.1
				header('Cache-Control: max-age='.$max_age);

				// Clear any output
				Event::add('system.display', create_function('', 'Kohana::$output = "";'));

				exit;
			}
		}

		return $last_modified;
	}

	/**
	 * Check headers already created to not step on download or Img_lib's feet
	 *
	 * @return boolean
	 */
	public static function check_headers()
	{
		foreach (headers_list() as $header)
		{
			if ((session_cache_limiter() == '' AND stripos($header, 'Last-Modified:') === 0)
			    OR stripos($header, 'Expires:') === 0)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

} // End expires
