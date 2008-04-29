<?php defined('SYSPATH') or die('No direct script access.');
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

	protected static $routes = array();

	public static $current_uri = '';
	public static $segments    = array();
	public static $rsegments   = array();

	public static $query_string = '';
	public static $url_suffix   = '';

	public static $directory       = FALSE;
	public static $controller      = FALSE;
	public static $controller_path = FALSE;
	public static $method          = FALSE;
	public static $arguments       = FALSE;

	/**
	 * Router setup routine. Automatically called during Kohana setup process.
	 *
	 * @return  void
	 */
	public static function setup()
	{
		if ( ! empty($_SERVER['QUERY_STRING']))
		{
			// Set the query string to the current query string
			self::$query_string = '?'.trim($_SERVER['QUERY_STRING'], '&');
		}

		// Load routing configuration
		self::$routes = Config::item('routes');

		// Default route status
		$default_route = FALSE;

		if (self::$current_uri === '')
		{
			// Make sure the default route is set
			if ( ! isset(self::$routes['_default']))
				throw new Kohana_Exception('core.no_default_route');

			// Use the default route when no segments exist
			self::$current_uri = self::$routes['_default'];

			// Default route is in use
			$default_route = TRUE;
		}

		// Make sure the URL is not tainted with HTML characters
		self::$current_uri = html::specialchars(self::$current_uri, FALSE);

		// At this point segments, rsegments, and current URI are all the same
		self::$segments = self::$rsegments = self::$current_uri = trim(self::$current_uri, '/');

		// Explode the segments by slashes
		self::$segments = ($default_route === TRUE OR self::$segments === '') ? array() : explode('/', self::$segments);

		if ($default_route === FALSE AND count(self::$routes) > 1)
		{
			// Custom routing
			self::$rsegments = self::routed_uri(self::$current_uri);
		}

		// Routed segments will never be empty
		self::$rsegments = explode('/', self::$rsegments);

		// Prepare for Controller search
		self::$directory  = '';
		self::$controller = '';

		// Optimize the check for the most common controller location
		if (is_file(APPPATH.'controllers/'.self::$rsegments[0].EXT))
		{
			self::$directory  = APPPATH.'controllers/';
			self::$controller = self::$rsegments[0];
			self::$method     = isset(self::$rsegments[1]) ? self::$rsegments[1] : 'index';
			self::$arguments  = isset(self::$rsegments[2]) ? array_slice(self::$rsegments, 2) : array();
		}
		else
		{
			// Fetch the include paths
			$include_paths = Config::include_paths();

			// Path to be added to as we search deeper
			$search = 'controllers';
			
			// controller path to be added to as we search deeper
			$controller_path = '';
			
			// Use the rsegments to find the controller
			foreach (self::$rsegments as $key => $segment)
			{
				foreach ($include_paths as $path)
				{
					// The controller has been found, all arguments can be set
					if (is_file($path.$search.'/'.$segment.EXT))
					{
						self::$directory       = $path.$search.'/';
						self::$controller_path = $controller_path;
						self::$controller      = $segment;
						self::$method          = isset(self::$rsegments[$key + 1]) ? self::$rsegments[$key + 1] : 'index';
						self::$arguments       = isset(self::$rsegments[$key + 2]) ? array_slice(self::$rsegments, $key + 2) : array();

						// Stop searching, two levels
						break 2;
					}
				}

				// Add the segment to the search
				$search .= '/'.$segment;
				$controller_path .= $segment.'/';
			}
		}

		// Last chance to set routing before a 404 is triggered
		Event::run('system.post_routing');

		if (empty(self::$controller))
		{
			// No controller was found, so no page can be rendered
			Event::run('system.404');
		}
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
		elseif (count($_GET) === 1 AND current($_GET) === '' AND substr($_SERVER['QUERY_STRING'], -1) !== '=')
		{
			// The URI is the array key, eg: ?this/is/the/uri
			self::$current_uri = key($_GET);

			// Fixes really strange handling of a suffix in a GET string
			if ($suffix = Config::item('core.url_suffix') AND substr(self::$current_uri, -(strlen($suffix))) === '_'.substr($suffix, 1))
			{
				self::$current_uri = substr(self::$current_uri, 0, -(strlen($suffix)));
			}

			// Destroy GET
			$_GET = array();
			$_SERVER['QUERY_STRING'] = '';
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
			if ($suffix = Config::item('core.url_suffix') AND strpos(self::$current_uri, $suffix) !== FALSE)
			{
				// Remove the URL suffix
				self::$current_uri = preg_replace('!'.preg_quote($suffix).'$!u', '', self::$current_uri);

				// Set the URL suffix
				self::$url_suffix = $suffix;
			}

			// Reduce multiple slashes into single slashes
			self::$current_uri = preg_replace('!//+!', '/', self::$current_uri);
		}
	}

	/**
	 * Generates routed URI from given URI.
	 *
	 * @param  string  URI to convert
	 * @return string  Routed uri
	 */
	public static function routed_uri($uri)
	{
		$routes = Config::item('routes');
		$uri    = $routed_uri = trim($uri, '/');
	
		if (isset($routes[$uri]))
		{
			// Literal match, no need for regex
			$routed_uri = $routes[$uri];
		}
		else
		{
			// Loop through the routes and see if anything matches
			foreach ($routes as $key => $val)
			{
				if ($key === '_default' OR $key === '_allowed') continue;

				// Trim slashes
				$key = trim($key, '/');
				$val = trim($val, '/');

				// Does this route match the current URI?
				if (preg_match('#^'.$key.'$#u', $uri))
				{
					// If the regex contains a valid callback, we'll use it
					if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
					{
						$routed_uri = preg_replace('#^'.$key.'$#u', $val, $uri);
					}
					else
					{
						$routed_uri = $val;
					}

					// A valid route was found, stop parsing other routes
					break;
				}
			}
		}

		// Check router one more time to do some magic
		if (isset($routes[$routed_uri]))
		{
			$routed_uri = $routes[$routed_uri];
		}

		return $routed_uri;
	}    

} // End Router class
