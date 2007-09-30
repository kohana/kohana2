<?php defined('SYSPATH') or die('No direct access allowed.');
/* $Id$ */

class Router_Core {

	protected static $routes = array();

	public static $current_uri = '';
	public static $segments    = array();
	public static $rsegments   = array();

	public static $query_string = '';

	public static $directory  = FALSE;
	public static $controller = FALSE;
	public static $method     = FALSE;
	public static $arguments  = FALSE;

	public static function setup()
	{
		self::$routes = Config::item('routes');

		// Make sure the default route is set
		if ( ! isset(self::$routes['_default']))
			throw new Kohana_Exception('core.no_default_route');

		// The follow block of if/else attempts to retrieve the URI segments automagically
		// Supported methods: CLI, GET, PATH_INFO, ORIG_PATH_INFO, PHP_SELF
		if (PHP_SAPI === 'cli')
		{
			// Command line requires a bit of hacking
			if (isset($_SERVER['argv'][1]))
			{
				self::$segments = $_SERVER['argv'][1];

				// Remove GET string from segments
				if (($query = strrpos(self::$segments, '?')) !== FALSE)
				{
					list (self::$segments, $query) = explode('?', self::$segments);

					// Insert query into GET array
					foreach(explode('&', $query) as $pair)
					{
						list ($key, $val) = array_pad(explode('=', $pair), 1, '');

						$_GET[utf8::clean($key)] = utf8::clean($val);
					}
				}
			}
		}
		elseif (count($_GET) === 1 AND current($_GET) == '')
		{
			self::$segments = current(array_keys($_GET));

			// Fixes really stange handling of a suffix in a GET string
			if ($suffix = Config::item('core.url_suffix') AND substr(self::$segments, -(strlen($suffix))) === '_'.substr($suffix, 1))
			{
				self::$segments = substr(self::$segments, 0, -(strlen($suffix)));
			}

			// Destroy GET
			$_GET = array();
		}
		elseif (isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO'])
		{
			self::$segments = $_SERVER['PATH_INFO'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO'])
		{
			self::$segments = $_SERVER['ORIG_PATH_INFO'];
		}
		elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'])
		{
			self::$segments = $_SERVER['PHP_SELF'];
		}

		// Find the URI string based on the location of the front controller
		if (($offset = strpos(self::$segments, KOHANA)) !== FALSE)
		{
			// Add the length of the index file to the offset
			$offset += strlen(KOHANA);

			// Get the segment part of the URL
			self::$segments = substr(self::$segments, $offset);
			self::$segments = trim(self::$segments, '/');
		}


		// Use the default route when no segments exist
		if (self::$segments == '' OR self::$segments == '/')
		{
			self::$segments = self::$routes['_default'];
			$default_route = TRUE;
		}
		else
		{
			$default_route = FALSE;
		}

		// Remove the URL suffix
		if ($suffix = Config::item('core.url_suffix'))
		{
			self::$segments = preg_replace('!'.preg_quote($suffix).'$!u', '', self::$segments);
		}

		// Remove extra slashes from the segments that could cause fucked up routing
		self::$segments = preg_replace('!//+!', '/', self::$segments);

		// At this point, set the segments, rsegments, and current URI
		// In many cases, all of these variables will match
		self::$segments = self::$rsegments = self::$current_uri = trim(self::$segments, '/');

		(self::$segments === 'L0LEAST3R') and include SYSPATH.'views/kohana_holiday.php';

		// Custom routing
		if ($default_route == FALSE AND count(self::$routes) > 1)
		{
			if (isset(self::$routes[self::$current_uri]))
			{
				// Literal match, no need for regex
				self::$rsegments = self::$routes[self::$current_uri];
			}
			else
			{
				// Loop through the routes and see if anything matches
				foreach(self::$routes as $key => $val)
				{
					if ($key == '_default') continue;

					// Replace helper strings
					$key = str_replace
					(
						array(':any', ':num'),
						array('.+',   '[0-9]+'),
						$key
					);

					// Does this route match the current URI?
					if (preg_match('!^'.$key.'$!u', self::$segments))
					{
						// If the regex contains a valid callback, we'll use it
						if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
						{
							self::$rsegments = preg_replace('!^'.$key.'$!u', $val, self::$segments);
						}
						else
						{
							self::$rsegments = $val;
						}

						// A valid route was found, stop parsing other routes
						break;
					}
				}
			}
		}

		// Explode the segments by slashes
		if ($default_route == TRUE OR self::$segments == '')
		{
			self::$segments = array();
		}
		else
		{
			self::$segments = explode('/', self::$segments);
		}
		// Routed segments will never be blank
		self::$rsegments = explode('/', self::$rsegments);

		// Validate segments to prevent malicious characters
		if ( ! empty(self::$segments))
		{
			foreach(self::$segments as $key => $segment)
			{
				self::$segments[$key] = self::filter_uri($segment);
			}
		}

		// Yah, routed segments too, even though it should never happen
		if ( ! empty(self::$rsegments))
		{
			foreach(self::$rsegments as $key => $segment)
			{
				self::$rsegments[$key] = self::filter_uri($segment);
			}
		}

		// Prepare for Controller search
		self::$directory  = '';
		self::$controller = '';

		// We check this path statically, because it's overwhelmingly the most
		// common path for controllers to be located at
		if (is_file(APPPATH.'controllers/'.self::$rsegments[0].EXT))
		{
			self::$directory  = APPPATH.'controllers'.'/';
			self::$controller = self::$rsegments[0];
			self::$method     = isset(self::$rsegments[1]) ? self::$rsegments[1] : 'index';
		}
		else
		{
			// Fetch the include paths
			$include_paths = Config::include_paths();

			// Path to be added to as we search deeper
			$search = 'controllers';

			// Use the rsegments to find the controller
			foreach(self::$rsegments as $key => $segment)
			{
				foreach($include_paths as $path)
				{
					// The controller has been found, all arguments can be set
					if (is_file($path.$search.'/'.$segment.EXT))
					{
						self::$directory  = $path.$search.'/';
						self::$controller = $segment;
						self::$method     = isset(self::$rsegments[$key + 1]) ? self::$rsegments[$key + 1] : 'index';
						self::$arguments  = isset(self::$rsegments[$key + 2]) ? array_slice(self::$rsegments, $key + 2) : array();

						// Stop searching
						break;
					}
				}

				// Stop searching
				if (self::$controller !== FALSE) break;

				// Add the segment to the search
				$search .= '/'.$segment;
			}
		}

		if (empty(self::$controller))
		{
			Kohana::show_404();
		}
	}

	public static function filter_uri($str)
	{
		$str = trim($str);

		if ($str != '' AND ($allowed = Config::item('core.permitted_uri_chars')) != '')
		{
			if ( ! preg_match('|^['.preg_quote($allowed).']+$|iu', $str))
			{
				header('HTTP/1.1 400 Bad Request');
				exit('The URI you submitted has disallowed characters.');
			}
		}

		return $str;
	}

} // End Router class