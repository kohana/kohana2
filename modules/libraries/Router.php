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

	public static function load_segments()
	{
		self::$segments = '';
		self::$routes   = Core::load_file('config', 'routes');

		/**
		 * The follow block of if/else attempts to retrieve the URI segments automagically
		 *
		 * Supported methods: CLI, GET, PATH_INFO, REQUEST_URI
		 */
		if (IS_CLI)
		{
			global $argv;
			// Command line requires a bit of hacking
			if (isset($argv[1]))
			{
				self::$segments = preg_replace('#/+#', '/', $argv[1]);
				// Remove GET string from segments
				if (($query = strpos(self::$segments, '?')) !== FALSE)
				{
					list (self::$segments, $query) = explode('?', self::$segments);

					// Insert query into GET array
					foreach(explode('&', $query) as $pair)
					{
						list ($key, $val) = array_pad(explode('=', $pair), 1, '');
						$_GET[$key] = $val;
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
		elseif (isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'])
		{
			$ruri = trim($_SERVER['REQUEST_URI'], '/');
			$path = trim(preg_replace('#^'.$_SERVER['DOCUMENT_ROOT'].'#', '', DOCROOT), '/');

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
			(isset(self::$routes['_default'])) OR die
			(
				Core::show_error('core', 'default_route_not_set')
			);

			self::$segments = self::$routes['_default'];
		}

		/**
		 * Remove the URL suffix
		 */
		if ($suffix = Core::config_item('url_suffix'))
		{
			self::$segments = preg_replace('#'.preg_quote($suffix).'$#D', '', self::$segments);
		}

		/**
		 * Remove extra slashes from the segments that could cause fucked up routing.
		 */
		self::$segments = preg_replace('#/+#', '/', self::$segments);
		self::$segments = self::$current_uri = trim(self::$segments, '/');

		/**
		 * Explode the segments by slashes
		 */
		self::$segments = explode('/', self::$segments);

		/**
		 * Prepare for Controller search
		 */
		self::$directory = '';
		self::$controller = FALSE;

		/**
		 * Search for the Controller and set Controller parameters
		 */
		$paths = Core::include_paths();
		foreach(self::$segments as $key => $segment)
		{
			// Make sure segments are valid
			$segments[$key] = self::filter_uri($segment);

			foreach($paths as $path)
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
				$method = $key+1;
				$args   = $key+2;

				// Set method
				if (isset(self::$segments[$method]))
				{
					self::$method = self::$segments[$method];
				}
				else
				{
					self::$method = 'index';
				}

				// Set arguments
				if (isset(self::$segments[$args]))
				{
					self::$arguments = array_slice(self::$segments, $args);
				}
				else
				{
					self::$arguments = array();
				}

				// Stop searching for the controller
				break;
			}
		}

		(self::$controller == FALSE) AND Core::show_error('core', 'page_not_found', self::$current_uri);

		print "controller is: ".self::$controller."<br/>\n";
		print "method is: ".self::$method."<br/>\n";
		print "arguments are: ".print_r(self::$arguments, TRUE)."<br/>\n";
	}

	public static function filter_uri($str)
	{
		$str = trim($str);

		if (($allowed = Core::config_item('permitted_uri_chars')) != '')
		{
			if ( ! preg_match('|^['.preg_quote($allowed).']+$|i', $str))
			{
				header('HTTP/1.1 400 Bad Request');
				exit('The URI you submitted has disallowed characters.');
			}
		}

		return $str;
	}

} // End Router class