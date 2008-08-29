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

	public static $readonly_keys = array('regex', 'prefix');

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
		// Remove all dot-paths from the URI, they are not valid
		self::$current_uri = trim(preg_replace('#\.[\s./]*/#', '', self::$current_uri), '/');

		// Set the complete URI
		self::$complete_uri = self::$current_uri.self::$query_string;

		// Load routes
		$routes = Kohana::config('routes');

		if (isset($routes['_default']) OR count($routes) > 1 AND isset($routes[1]))
		{
			throw new Kohana_User_Exception
			(
				'Routing has been significantly changed, and your configuration '.
				'files are not up to date. Please check http://dev.kohanaphp.com/changeset/3366 '.
				'for more details.'
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
				// If matches exist and there are keys for the URI, parse them
				if (count($matches) > 1 AND $keys = Router::keys($route[0]))
				{
					// Remove the matched string
					$matches = array_slice($matches, 1);

					foreach ($keys as $i => $key)
					{
						if (in_array($key, Router::$readonly_keys))
						{
							// Skip keys that are readonly, such as "regex"
							continue;
						}

						if (isset($matches[$i]) AND $matches[$i] !== '')
						{
							// Set the route value from the URI
							$route[$key] = $matches[$i];
						}

						if (isset($route['prefix'][$key]))
						{
							// Add the prefix to the key
							$route[$key] = $route['prefix'][$key].$route[$key];
						}

						if ($key !== 'controller' AND $key !== 'method' AND isset($route[$key]))
						{
							// Add the value to the arguments
							self::$arguments[$key] = $route[$key];
						}
					}
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
		}
		elseif (isset($_GET['kohana_uri']))
		{
			// Use the URI defined in the query string
			self::$current_uri = $_GET['kohana_uri'];

			// Remove the URI from $_GET
			unset($_GET['kohana_uri']);

			// Remove the URI from $_SERVER['QUERY_STRING']
			$_SERVER['QUERY_STRING'] = preg_replace('~\bkohana_uri\b[^&]*+&?~', '', $_SERVER['QUERY_STRING']);

			// Fixes really strange handling of a suffix in a GET string
			if ($suffix = Kohana::config('core.url_suffix') AND substr(self::$current_uri, -(strlen($suffix))) === '_'.substr($suffix, 1))
			{
				self::$current_uri = substr(self::$current_uri, 0, -(strlen($suffix)));
			}
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

		// The front controller directory and filename
		$fc = substr(realpath($_SERVER['SCRIPT_FILENAME']), strlen(DOCROOT));

		if (($strpos_fc = strpos(self::$current_uri, $fc)) !== FALSE)
		{
			// Remove the front controller from the current uri
			self::$current_uri = substr(self::$current_uri, $strpos_fc + strlen($fc));
		}

		// Remove slashes from the start and end of the URI
		self::$current_uri = trim(self::$current_uri, '/');

		if (self::$current_uri !== '')
		{
			// Reduce multiple slashes into single slashes
			self::$current_uri = preg_replace('#//+#', '/', self::$current_uri);

			// Make sure the URL is not tainted with HTML characters
			self::$current_uri = html::specialchars(self::$current_uri, FALSE);
		}

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
	public static function uri($name, array $values = array())
	{
		$route = Kohana::config('routes.'.$name);

		throw new Kohana_User_Exception('Router::uri() is not functional yet');
	}

	/**
	 * Finds all of the :keys in a URI and returns them as a simple array.
	 *
	 * @param   string   URI string
	 * @return  array
	 */
	protected static function keys($uri)
	{
		if (strpos($uri, ':') === FALSE)
			return array();

		// Find all keys that start with a colon
		preg_match_all('#:([a-z]+)#', $uri, $keys);

		return $keys[1];
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
	protected static function compile(array $route)
	{
		// Split the route URI by slashes
		$uri = explode('/', $route[0]);

		// Regular expression end
		$end = array();

		// Nothing is optional yet
		$optional = FALSE;

		foreach ($uri as $i => $segment)
		{
			// Regular expression
			$exp = array();

			if ($segment[0] === ':')
			{
				// Find the actual segment key and any trailing garbage
				preg_match('#^:([a-z]+)(.*)$#', $segment, $matches);

				// Segment key
				$key = $matches[1];

				if ($optional === FALSE AND isset($route[$key]))
				{
					// This key has a default value, so all following matches
					// will be optional as well.
					$optional = TRUE;
				}

				if ($optional === TRUE)
				{
					// Start the expression as an optional match
					$exp[] = '(?:';
				}

				if ($i > 0)
				{
					// Add the slash from the previous segment
					$exp[] = '/';
				}

				if (isset($route['regex'][$key]))
				{
					// Matches specified regex for the segment
					$exp[] = '('.$route['regex'][$key].')';
				}
				else
				{
					// Default regex matches all characters
					$exp[] = '([^/]+)';
				}

				if ($matches[2] !== '')
				{
					// Add trailing segment junk
					$exp[] = preg_quote($matches[2], '#');
				}

				if ($optional === TRUE)
				{
					// End the expression
					$end[] = ')?';
				}

				// Replace the segment with the segment expression
				$uri[$i] = implode('', $exp);
			}
			else
			{
				// Quote the raw segment
				$uri[$i] = preg_quote($segment, '#');

				if ($i > 0)
				{
					// Add slash from previous segment
					$uri[$i] .= '/';
				}
			}
		}

		return implode('', $uri).implode('', $end);
	}

} // End Router