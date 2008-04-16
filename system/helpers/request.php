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

	/**
	 * Possible http methods
	 *
	 * @access     protected
	 * @staticvar  ARRAY
	 */
	protected static $http_methods = array('get', 'head', 'options', 'post', 'put', 'delete');

	/**
	 * Content Types from client's HTTP Request Header, Accepts
	 *
	 * @access     protected
	 * @staticvar  ARRAY
	 */
	protected static $accept_types;

	/**
	 * Tests if the current request is an AJAX request by checking the X-Requested-With HTTP
	 * Request Header that most popular JS frameworks now set for AJAX calls
	 *
	 * @access  public
	 * @static
	 * @return  BOOLEAN
	 */
	public static function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	/**
	 * Returns boolean whether user agent accepts xhtml
	 *
	 * @access  public
	 * @static
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  BOOLEAN
	 */
	public static function accepts_xhtml($explicit_checking = FALSE)
	{
		 return self::accepts('xhtml',$explicit_checking);
	}

	/**
	 * Returns boolean whether user agent accepts xml
	 *
	 * @access  public
	 * @static
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  BOOLEAN
	 */
	public static function accepts_xml($explicit_checking = FALSE)
	{
		 return self::accepts('xml',$explicit_checking);
	}

	/**
	 * Returns boolean whether user agent accepts rss
	 *
	 * @access  public
	 * @static
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  BOOLEAN
	 */
	public static function accepts_rss($explicit_checking = FALSE)
	{
		 return self::accepts('rss',$explicit_checking);
	}

	/**
	 * Returns boolean whether user agent accepts atom
	 *
	 * @access  public
	 * @static
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  BOOLEAN
	 */
	public static function accepts_atom($explicit_checking = FALSE)
	{
		 return self::accepts('atom',$explicit_checking);
	}

	/**
	 * Returns current request method
	 *
	 * @access  public
	 * @static
	 * @throws  Kohana_Exception
	 * @return  STRING
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
	 * @access  public
	 * @static
	 * @param   STRING $type
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  BOOLEAN
	 */
	public static function accepts ($type = NULL, $explicit_checking = FALSE)
	 {
		self::parse_accept_header();

		if ($type === NULL)
			return self::$accept_types;

		return (self::accepts_at_quality($type, $explicit_checking) > 0);
	}

	/**
	 * Compare the q values for given array of content types and return the one with the highest value
	 * If items are found to have the same q value, the first one encountered in the given array wins
	 * If all items in the given array have a q value of 0, FALSE is returned
	 * 
	 * @access  public
	 * @static
	 * @param   ARRAY $types
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  MIXED - STRING mime type with highest q value, BOOLEAN FALSE if none of the given types are acceptable
	 */
	public static function preferred_accept($types, $explicit_checking = FALSE)
	{
		$preferred = FALSE;
		$max_q = 0;
		$mime_types = array();
		if (is_array($types))
		{
			foreach ($types as $type)
			{
				if (is_string($type) AND ! empty($type))
				{
					if ( ! isset($mime_types[$type]))
					{
						$mime_types[$type] = self::accepts_at_quality($type, $explicit_checking);
					}
				}
			}

			if (count($mime_types) > 0)
			{
				while (list($type, $q) = each($mime_types))
				{
					if ($q > $max_q)
					{
						$preferred = $type;
					}
				}
			}
		}
		return $preferred;
	}
	
	/**
	 * Returns quality value at which client accepts content type
	 *
	 * @access  public
	 * @static
	 * @param   STRING $type
	 * @param   BOOLEAN $explicit_checking set to TRUE to disable wildcard checking
	 * @return  INTEGER
	 */
	public static function accepts_at_quality($type = NULL, $explicit_checking = FALSE)
	{
		self::parse_accept_header();
		
		if (is_string($type))
		{
			$type = strtolower($type);
			if (strstr($type,'/') !== FALSE)
			{
				list($mime_major,$mime_minor) = explode('/',$type);

				if (isset(self::$accept_types[$mime_major][$mime_minor]))
					return self::$accept_types[$mime_major][$mime_minor];

				if ($explicit_checking === FALSE)
				{
					if (isset(self::$accept_types[$mime_major]['*']))
						return self::$accept_types[$mime_major]['*'];
				}
			}
			else
			{	
				$mapped_mime_types = Config::item('mimes.'.$type);
				if (is_array($mapped_mime_types))
				{
					$q = 0;
					foreach ($mapped_mime_types as $type)
					{
						$current_q = self::accepts_at_quality($type, $explicit_checking);
						if ($current_q > $q)
						{
							$q = $current_q;
						}
					}
					return $q;
				}
			}

			if ($explicit_checking === FALSE)
			{
				if (isset(self::$accept_types['*']))
				{
					if (isset(self::$accept_types['*']['*']))
						return self::$accept_types['*']['*'];
				}
			}
		}
		return 0;
	}
	
	/**
	 * Parses clients HTTP Request Header, Accept, and builds array structure representing it
	 * 
	 * @access  protected
	 * @static
	 * @return  void
	 */
	protected static function parse_accept_header()
	{
		if (self::$accept_types === NULL)
		 {
			self::$accept_types = array();
		 	if (isset($_SERVER['HTTP_ACCEPT']) AND ! empty($_SERVER['HTTP_ACCEPT']))
			{
				$accept_entries = explode(',',$_SERVER['HTTP_ACCEPT']);
				foreach ($accept_entries as $accept_entry)
				{
					$parameters_separated = explode(';', $accept_entry);
					list($mime_major, $mime_minor) = explode('/',$parameters_separated[0],2);
					$q = 1;
					if (isset($parameters_separated[1]) AND ! empty($parameters_separated[1]) AND substr($parameters_separated[1],0,1)==='q')
					{
						list(,$q) = explode('=',$parameters_separated[1],2);
						$q = (round($q * 1000))/1000;
					}

					if ( ! isset(self::$accept_types[$mime_major]))
					{
						self::$accept_types[$mime_major] = array();
					}

					if ( ! isset(self::$accept_types[$mime_major][$mime_minor]) OR $q > self::$accept_types[$mime_major][$mime_minor])
					{
						self::$accept_types[$mime_major][$mime_minor] = $q;
					}
				}
			}
			else
			{
				self::$accept_types['*'] = array('*' => 1);
			}
		}
	}
	
} // End request