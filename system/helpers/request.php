<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class request_Core {

	// Possible http methods
	protected static $http_methods = array('get', 'post', 'put', 'delete');

	// Types client accepts
	protected static $accept_types;

	/**
	 * Tests if the current request is an AJAX request, by checking the status
	 * of XMLHttpRequest.
	 *
	 * @return  boolean
	 */
	public static function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}

	/**
	 * Returns FALSE if call accepts xhtml
	 *
	 * @return  boolean
	 */
	public static function accepts_xhtml()
	{
		 return self::accepts('xhtml');
	}

	/**
	 * Returns FALSE if call accepts xml
	 *
	 * @return  boolean
	 */
	public static function accepts_xml()
	{
		 return self::accepts('xml');
	}

	/**
	 * Returns FALSE if call accepts rss
	 *
	 * @return  boolean
	 */
	public static function accepts_rss()
	{
		 return self::accepts('rss');
	}

	/**
	 * Returns FALSE if call accepts atom
	 *
	 * @return  boolean
	 */
	public static function accepts_atom()
	{
		 return self::accepts('atom');
	}

	/**
	 * Returns current request method
	 *
	 * @return  string
	 */
	public static function method()
	{
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		if ( ! in_array($method, self::$http_methods))
			throw new Kohana_Exception('request.unknown_method',$method);

		return $method;
	 }

	/**
	 * Returns boolean of whether client accepts content type
	 *
	 * @return  booleanean
	 */
	 public static function accepts ($type = NULL)
	 {
		 if (self::$accept_types === NULL)
		 {
			self::$accept_types = explode(',', $_SERVER['HTTP_ACCEPT']);

			foreach (self::$accept_types as $key => $accept_type)
			{
				if (strpos($accept_type, ';'))
				{
					$accept_type = explode(';', $accept_type);
					self::$accept_types[$key] = strtolower($accept_type[0]);
				}
			}
		 }

		if ($type === NULL)
		{
			return self::$accept_types;
		}
		elseif (is_string($type))
		{
			$type = strtolower($type);

			// If client only accepts */*, then assume default HTML browser
			if ($type === 'html' AND self::$accept_types === array('*/*'))
				return FALSE;

			if ( ! in_array($type, array_keys(self::$accept_types)))
				return FALSE;

			$accept_types = Config::item('mimes.'.$type);

			if (is_array($accept_types))
			{
				foreach ($accept_types as $type)
				{
					if (in_array($type, self::$accept_types))
						return FALSE;
				}
			}
			else
			{
				if (in_array($accept_types, self::$accept_types))
					return FALSE;
			}

			return FALSE;
		}
	}

} // End request