<?php
/**
 * Router
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Router_Core {

	public static $current_route;
	public static $request_method;

	public static $current_uri  = '';
	public static $query_string = '';
	public static $complete_uri = '';

	public static $controller;
	public static $method;
	public static $arguments = array();

	/**
	 * Router setup routine. Called during the [system.routing][ref-esr]
	 * Event by default.
	 *
	 * [ref-esr]: http://docs.kohanaphp.com/events/system.routing
	 *
	 * @return  boolean
	 */
	public static function setup()
	{
		// Set the complete URI
		self::$complete_uri = self::$current_uri.self::$query_string;

		// Load routes
		$routes = Kohana::config('routes');

		if (isset($routes['_default']) OR count($routes) > 1 AND isset($routes[1]))
		{
			throw new Kohana_User_Exception
			(
				'Routing API Changed!',
				'Routing has been significantly changed, and your configuration files are not up to date. '.
				'Please check http://dev.kohanaphp.com/changeset/3366 for more details.'
			);
		}

		if (count($routes) > 1)
		{
			// Get the default route
			$default = $routes['default'];

			// Remove it from the routes
			unset($routes['default']);

			// Add the default route at the end
			$routes['default'] = $default;
		}

		foreach ($routes as $name => $route)
		{
			// Compile the route into regex
			$regex = Router::compile($route);

			if (preg_match('#^'.$regex.'$#u', self::$current_uri, $matches))
			{
				if (isset($route['route']['request']) AND $route['route']['request'] !== Router::$request_method)
				{
					// The request method is invalid
					continue;
				}

				if (isset($route['route']['global']))
				{
					foreach ($route['route']['global'] as $type => $keys)
					{
						switch ($type)
						{
							case 'get':   $global = $_GET;   break;
							case 'post':  $global = $_POST;  break;
							case 'files': $global = $_FILES; break;
						}

						foreach ($keys as $key)
						{
							if ( ! isset($global[$key]))
							{
								// The request does not have a required global value
								continue 3;
							}
						}
					}
				}

				foreach ($matches as $key => $value)
				{
					if (is_int($key) OR $key === 'route')
					{
						// Skip matches that are not named or readonly
						continue;
					}

					if ($value !== '')
					{
						// Overload the route with the matched value
						$route[$key] = $value;
					}
				}

				if (isset($route['route']['prefix']))
				{
					foreach ($route['route']['prefix'] as $key => $prefix)
					{
						if (isset($route[$key]))
						{
							// Add the prefix to the key
							$route[$key] = $route['route']['prefix'][$key].$route[$key];
						}
					}
				}

				foreach ($route as $key => $val)
				{
					if (is_int($key) OR $key === 'controller' OR $key === 'method' OR $key === 'route')
					{
						// These keys are not arguments, skip them
						continue;
					}

					self::$arguments[$key] = $val;
				}

				// Set controller name
				self::$controller = $route['controller'];

				if (isset($route['method']))
				{
					// Set controller method
					self::$method = $route['method'];
				}
				else
				{
					// Default method
					self::$method = 'index';
				}

				// A matching route has been found!
				self::$current_route = $name;

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Attempts to determine the current URI using CLI, GET, PATH_INFO, ORIG_PATH_INFO, or PHP_SELF.
	 *
	 * @return  void
	 */
	public static function find_uri()
	{
		if (PHP_SAPI === 'cli')
		{
			// Command line requires a bit of hacking
			if (isset($_SERVER['argv'][1]))
			{
				self::$current_uri = $_SERVER['argv'][1];

				// Remove GET string from segments
				if (($query = strpos(self::$current_uri, '?')) !== FALSE)
				{
					list (self::$current_uri, $query) = explode('?', self::$current_uri, 2);

					// Parse the query string into $_GET
					parse_str($query, $_GET);

					// Convert $_GET to UTF-8
					$_GET = utf8::clean($_GET);
				}
			}

			// Disable HTML errors, they will be messy in CLI
			Kohana_Exception::$html_output = FALSE;
		}
		elseif (isset($_GET['kohana_uri']))
		{
			// Use the URI defined in the query string
			self::$current_uri = $_GET['kohana_uri'];

			// Remove the URI from $_GET
			unset($_GET['kohana_uri']);

			// Remove the URI from $_SERVER['QUERY_STRING']
			$_SERVER['QUERY_STRING'] = preg_replace('~\bkohana_uri\b[^&]*+&?~', '', $_SERVER['QUERY_STRING']);
		}
		elseif (isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO'])
		{
			self::$current_uri = $_SERVER['PATH_INFO'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO'])
		{
			self::$current_uri = $_SERVER['ORIG_PATH_INFO'];
		}
		elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'])
		{
			self::$current_uri = $_SERVER['PHP_SELF'];
		}

		if (PHP_SAPI === 'cli')
		{
			// The request method is command line
			Router::$request_method = 'cli';
		}
		elseif (isset($_SERVER['REQUEST_METHOD']))
		{
			// Set the request method using server information
			Router::$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		}

		// The front controller directory and filename
		$fc = substr(realpath($_SERVER['SCRIPT_FILENAME']), strlen(DOCROOT));

		if (($strpos_fc = strpos(self::$current_uri, $fc)) !== FALSE)
		{
			// Remove the front controller from the current URI
			self::$current_uri = substr(self::$current_uri, $strpos_fc + strlen($fc));
		}

		// Remove all dot-paths from the URI, they are not valid
		self::$current_uri = preg_replace('#\.[\s./]*/#', '', self::$current_uri);

		// Reduce multiple slashes into single slashes, remove trailing slashes
		self::$current_uri = trim(preg_replace('#//+#', '/', self::$current_uri), '/');

		// Make sure the URL is not tainted with HTML characters
		self::$current_uri = html::specialchars(self::$current_uri, FALSE);

		if ( ! empty($_SERVER['QUERY_STRING']))
		{
			// Set the query string to the current query string
			self::$query_string = '?'.trim($_SERVER['QUERY_STRING'], '&');
		}
	}

	/**
	 * Creates a URI for the given route.
	 *
	 * @param   string   route name
	 * @param   array    route key values
	 * @return  string
	 */
	public static function uri($route, array $values = array())
	{
		if ($route === TRUE)
		{
			$route = Router::$current_route;

			$values = array_merge
			(
				array('controller' => Router::$controller, 'method' => Router::$method),
				Router::$arguments,
				$values
			);
		}
		
		if ( ! ($route = Kohana::config('routes.'.$route)))
		{
			// @todo: This should be an exception
			return FALSE;
		}

		// Get the URI keys from the route
		$keys = Router::keys($route['route']['uri']);

		// Copy the URI, it will have parameters replaced
		$uri = $route['route']['uri'];

		// String searches and replacements
		$search = $replace = array();

		foreach ($keys as $key)
		{
			if (isset($values[$key]))
			{
				$search[] = ':'.$key;
				$replace[] = $values[$key];
			}
		}

		// Replace all the keys with the values
		$uri = str_replace($search, $replace, $uri);

		// Remove trailing parts from the URI
		$uri = preg_replace('#/?:.+$#', '', $uri);

		return $uri;
	}

	/**
	 * Finds all of the :keys in a URI and returns them as a simple array.
	 *
	 * @param   string   URI string
	 * @return  array
	 */
	public static function keys($uri)
	{
		if (strpos($uri, ':') === FALSE)
			return array();

		// Find all keys that start with a colon
		preg_match_all('#(?<=:)[a-z]+#', $uri, $keys);

		return $keys[0];
	}

	/**
	 * Creates a [regular expression][ref-reg] that can be used to match a
	 * route against a URI with [preg_match][ref-prm].
	 *
	 * [ref-reg]: http://php.net/manual/book.pcre.php
	 * [ref-prm]: http://php.net/preg_match
	 *
	 * @param   array   route array
	 * @return  string  regular expression
	 */
	public static function compile(array $route)
	{
		// Split the route URI by slashes
		$uri = explode('/', $route['route']['uri']);

		// Regular expression end
		$end = '';

		// Nothing is optional yet
		$optional = FALSE;

		foreach ($uri as $i => $segment)
		{
			if ($segment[0] === ':')
			{
				// Find the actual segment key and any trailing garbage
				preg_match('#^:([a-z]++)(.*)$#', $segment, $matches);

				// Segment key
				$key = $matches[1];

				// Regular expression
				$exp = '';

				if ($optional === FALSE AND isset($route[$key]))
				{
					// This key has a default value, so all following matches
					// will be optional as well.
					$optional = TRUE;
				}

				if ($optional === TRUE)
				{
					// Start the expression as non-capturing group
					$exp .= '(?:';

					// End the expression as an optional match
					$end .= ')?';
				}

				if ($i > 0)
				{
					// Add the slash from the previous segment
					$exp .= '/';
				}

				// Use the key as the regex subpattern name
				$name = '?P<'.$key.'>';

				if (isset($route['route']['regex'][$key]))
				{
					// Matches specified regex for the segment
					$exp .= '('.$name.$route['route']['regex'][$key].')';
				}
				else
				{
					// Default regex matches all characters except slashes
					$exp .= '('.$name.'[^/]++)';
				}

				if ($matches[2] !== '')
				{
					// Add trailing segment junk
					$exp .= preg_quote($matches[2], '#');
				}

				// Replace the segment with the segment expression
				$uri[$i] = $exp;
			}
			else
			{
				// Quote the raw segment
				$uri[$i] = preg_quote($segment, '#');

				if ($i > 0)
				{
					// Add slash from previous segment
					$uri[$i - 1] .= '/';
				}
			}
		}

		return implode('', $uri).$end;
	}

} // End Router