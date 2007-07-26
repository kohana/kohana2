<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Router {

	public static $current_uri;
	public static $segments;
	public static $rsegments;

	public static $routes;

	public static $directory;
	public static $controller;
	public static $method;
	public static $arguments;

	public static function initialize()
	{
		try
		{
			require Kohana::find_file('config', 'routes', TRUE);

			self::$segments = '';
			self::$routes   = $config;
		}
		catch (file_not_found $execption)
		{
			/**
			 * @todo this needs to be handled better
			 */
			exit('Your <kbd>config/routes'.EXT.'</kbd> file could not be loaded.');
		}

		/**
		 * The follow block of if/else attempts to retrieve the URI segments automagically
		 *
		 * Supported methods: CLI, GET, PATH_INFO, ORIG_PATH_INFO, REQUEST_URI
		 */
		if (PHP_SAPI == 'cli')
		{
			global $argv;
			// Command line requires a bit of hacking
			if (isset($argv[1]))
			{
				self::$segments = preg_replace('#/+#u', '/', $argv[1]);
				// Remove GET string from segments
				if (($query = strpos(self::$segments, '?')) !== FALSE)
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
		}
		elseif (isset($_SERVER['PATH_INFO']))
		{
			self::$segments = $_SERVER['PATH_INFO'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']))
		{
			self::$segments = $_SERVER['ORIG_PATH_INFO'];
		}
		elseif (isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'])
		{
			/**
			 * @todo this needs a lot more work, and tests need to be made on IIS5/6/7
			 */
			$ruri = urldecode(trim($_SERVER['REQUEST_URI'], '/'));
			$path = trim(preg_replace('!^'.getcwd().'!u', '', DOCROOT), '/');

			$ruri = explode('/', $ruri);
			$path = explode('/', $path);

			$i = 0;
			while($dir = array_shift($path))
			{
				if ( ! $ruri[$i] == $dir)
					break;

				array_shift($ruri);
				$i += 1;
			}

			self::$segments = implode('/', $ruri);
		}

		/**
		 * Use the default route when no segments exist
		 */
		if (self::$segments == '')
		{
			if ( ! isset(self::$routes['_default']))
				trigger_error('Please set a default route in routes'.EXT);

			self::$segments = self::$routes['_default'];
		}

		/**
		 * Remove the URL suffix
		 */
		if ($suffix = Config::item('url_suffix'))
		{
			self::$segments = preg_replace('!'.preg_quote($suffix).'$!u', '', self::$segments);
		}

		/**
		 * Remove extra slashes from the segments that could cause fucked up routing.
		 */
		self::$segments = preg_replace('!/+!u', '/', self::$segments);
		self::$segments = self::$rsegments = self::$current_uri = trim(self::$segments, '/');

		/**
		 * Custom routing
		 */
		if (count(self::$routes) > 1);
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
					if ($key == '_default')
						continue;

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
						// Make sure the regex contains a valid callback
						if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
						{
							self::$rsegments = preg_replace('!^'.$key.'$!u', $val, self::$segments);
						}

						// A valid route was found, stop parsing other routes
						break;
					}
				}
			}
		}

		/**
		 * Explode the segments by slashes
		 */
		self::$segments  = explode('/', self::$segments);
		self::$rsegments = explode('/', self::$rsegments);

		/**
		 * Validate segments to prevent malicious characters
		 */
		foreach(self::$segments as $key => $segment)
		{
			self::$segments[$key] = self::filter_uri($segment);
		}

		/**
		 * Yah, routed segments too, even though it should never happen
		 */
		foreach(self::$rsegments as $key => $segment)
		{
			self::$rsegments[$key] = self::filter_uri($segment);
		}

		/**
		 * Prepare for Controller search
		 */
		self::$directory = '';
		self::$controller = FALSE;

		$include_paths = Config::item('include_paths');

		/**
		 * Search for the Controller and set Controller parameters
		 */
		foreach(self::$rsegments as $key => $segment)
		{
			foreach($include_paths as $path)
			{
				$path .= 'controllers/';

				if (is_file($path.self::$directory.$segment.EXT))
				{
					self::$directory  = $path.self::$directory;
					self::$controller = $segment;
					break;
				}
				elseif (is_dir($path.$segment))
				{
					self::$directory .= $segment.'/';

					// If no controller can be determined, use default
					if ( ! isset(self::$segments[$key+1]))
					{
						self::$directory  = $path.self::$directory;
						self::$controller = 'default';
						break;
					}
				}
			}

			// A controller has been located, set method and arguments
			if (self::$controller)
			{
				$method = $key+1; // First segment after the controller
				$args   = $key+2; // All segments after the method

				// Set method
				if (isset(self::$rsegments[$method]))
				{
					self::$method = iconv('UTF-8', 'ASCII//TRANSLIT', self::$rsegments[$method]);
				}
				else
				{
					self::$method = 'index';
				}

				// Set arguments
				if (isset(self::$rsegments[$args]))
				{
					self::$arguments = array_slice(self::$rsegments, $args);
				}
				else
				{
					self::$arguments = array();
				}

				// Routing is done
				break;
			}
		}

		if (self::$controller == FALSE)
			trigger_error('Page not found', E_USER_ERROR);
	}

	public static function filter_uri($str)
	{
		$str = trim($str);

		if (($allowed = Config::item('permitted_uri_chars')) != '')
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