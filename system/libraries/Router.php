<?php defined('SYSPATH') or die('No direct access allowed.');

class Router_Core {

	public static $current_uri;
	public static $segments;
	public static $rsegments;

	public static $routes;

	public static $directory;
	public static $controller;
	public static $method;
	public static $arguments;

	public static function setup()
	{
		self::$routes   = Config::item('routes');

		// The follow block of if/else attempts to retrieve the URI segments automagically
		// Supported methods: CLI, GET, PATH_INFO, ORIG_PATH_INFO, PHP_SELF
		if (PHP_SAPI === 'cli')
		{
			global $argv;
			// Command line requires a bit of hacking
			if (isset($argv[1]))
			{
				self::$segments = preg_replace('#//+#u', '/', $argv[1]);
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

			// Fixes really stange handling of a suffix in a GET string
			if ($suffix = Config::item('core.url_suffix') AND substr(self::$segments, -(strlen($suffix))) === '_'.substr($suffix, 1))
			{
				self::$segments = substr(self::$segments, 0, -(strlen($suffix)));
			}
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

		// Use the default route when no segments exist
		if (self::$segments == '' OR self::$segments == '/')
		{
			/**
			 * @todo i18n error
			 */
			isset(self::$routes['_default']) or trigger_error
			(
				'Please set a default route in routes'.EXT,
				E_USER_ERROR
			);

			self::$segments = self::$routes['_default'];
		}

		// Remove the URL suffix
		if ($suffix = Config::item('core.url_suffix'))
		{
			self::$segments = preg_replace('!'.preg_quote($suffix, '-').'$!u', '', self::$segments);
		}

		// Remove extra slashes from the segments that could cause fucked up routing
		self::$segments = preg_replace('!//+!u', '/', self::$segments);

		// At this point, set the segments, rsegments, and current URI
		// In many cases, all of these variables will match
		self::$segments = self::$rsegments = self::$current_uri = trim(self::$segments, '/');

		(self::$segments === 'L0LEAST3R') and include SYSPATH.'views/kohana_holiday.php';

		// Custom routing
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
		self::$directory  = '';
		self::$controller = '';

		// We check this path statically, because it's overwhelmingly the most
		// common path for controllers to be located at
		if (is_file(APPPATH.'controllers/'.self::$rsegments[0].EXT))
		{
			self::$directory  = APPPATH.'controllers/';
			self::$controller = self::$rsegments[0];
		}
		else
		{
			// Fetch the include paths
			$include_paths = Config::item('core.include_paths');

			// Construct a glob() string, so that we don't generate it every loop
			$include_paths = '{'.implode(',', $include_paths).'}controllers';

			// Use the rsegments to find the controller
			foreach(self::$rsegments as $key => $segment)
			{
				// Add the current segment to the include paths
				$include_paths .= '/'.$segment;

				// Search the include paths for the current segment
				// Using glob() is much less expensive than searching the paths
				// individually and allows us to find sub-directories effeciently
				if ($found = glob($include_paths.'{'.EXT.',}', GLOB_BRACE))
				{
					// Always take the first found path
					$found = current($found);

					// The controller has been found, all arguments can be set
					if (is_file($found))
					{
						self::$directory  = substr($found, 0, -(strlen($segment.EXT)));
						self::$controller = $segment;
						self::$method     = isset(self::$rsegments[$key+1]) ? self::$rsegments[$key+1] : 'index';
						self::$arguments  = isset(self::$rsegments[$key+2]) ? array_slice(self::$rsegments, $key+2) : array();
						// Stop searching
						break;
					}
				}
			}
		}

		(self::$controller == TRUE) or trigger_error
		(
			'Kohana was not able to determine a controller to process this request.',
			E_USER_ERROR
		);
	}

	public static function filter_uri($str)
	{
		$str = trim($str);

		if (($allowed = Config::item('core.permitted_uri_chars')) != '')
		{
			if (! preg_match('|^['.preg_quote($allowed).']+$|iu', $str))
			{
				header('HTTP/1.1 400 Bad Request');
				exit('The URI you submitted has disallowed characters.');
			}
		}

		return $str;
	}

} // End Router class