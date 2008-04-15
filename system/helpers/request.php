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
	public static function accepts ($type = NULL, $explicit_checking = FALSE)
	 {
		self::parse_accept_header();

		if ($type === NULL)
			return self::$accept_types;

		if (is_string($type))
		{
			$type = strtolower($type);
			if(strstr($type,'/') !== FALSE)
			{
				list($mime_major,$mime_minor) = explode('/',$type);

				if(isset(self::$accept_types[$mime_major][$mime_minor]))
					if (self::$accept_types[$mime_major][$mime_minor] > 0)
						return TRUE;
					else
						return FALSE;

				if($explicit_checking === FALSE)
					if(isset(self::$accept_types[$mime_major]['*']))
						if (self::$accept_types[$mime_major]['*'] > 0)
							return TRUE;
						else
							return FALSE;
			}
			else
			{	
				$mapped_mime_types = Config::item('mimes.'.$type);
				if(is_array($mapped_mime_types))
				{
					foreach ($mapped_mime_types as $type)
					{
						if (self::accepts($type) === TRUE)
							return  TRUE;
					}
				}
			}

			if($explicit_checking === FALSE)
				if (isset(self::$accept_types['*']))
					if (isset(self::$accept_types['*']['*']))
						if (self::$accept_types['*']['*'] > 0)
							return TRUE;
						else
							return FALSE;
		}
		
		return FALSE;
	}

	protected static function parse_accept_header()
	{
		if (self::$accept_types === NULL)
		 {
			self::$accept_types = array();
		 	if (isset($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_ACCEPT']))
			{
				$accept_entries = explode(',',$_SERVER['HTTP_ACCEPT']);
				foreach ($accept_entries as $accept_entry)
				{
					$parameters_separated = explode(';', $accept_entry);
					list($mime_major, $mime_minor) = explode('/',$parameters_separated[0],2);
					$q = 1000;
					if (isset($parameters_separated[1]) && !empty($parameters_separated[1]) && substr($parameters_separated[1],0,1)==='q')
					{
						list(,$q) = explode('=',$parameters_separated[1],2);
						$q = (integer) ($q * 1000);
					}

					if (!isset(self::$accept_types[$mime_major]))
						self::$accept_types[$mime_major] = array();

					if (!isset(self::$accept_types[$mime_major][$mime_minor]) || $q > self::$accept_types[$mime_major][$mime_minor])
						self::$accept_types[$mime_major][$mime_minor] = $q;
				}
			}
			else
			{
				self::$accept_types['*'] = array('*' => 1000);
			}
		}
	}
	
} // End request