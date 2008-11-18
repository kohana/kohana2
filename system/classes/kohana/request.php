<?php

class Kohana_Request_Core {

	// The controller of the current URI being processed
	public static $instance;

	/**
	 * Detects the current main request URI and creates a request for it.
	 *
	 * @return  object  main Kohana_Request instance
	 */
	public static function instance()
	{
		static $run;

		if ($run === NULL)
		{
			// Split the URI by the front controller
			$uri = explode(KOHANA, $_SERVER['PHP_SELF'], 2);

			// Use the URI after the front controller
			$uri = $uri[1];

			// Remove all dot-paths from the URI, they are not valid
			$uri = preg_replace('#\.[\s./]*/#', '', $uri);

			// Reduce multiple slashes into single slashes, remove trailing slashes
			$uri = trim(preg_replace('#//+#', '/', $uri), '/');

			// Make sure the URL is not tainted with HTML characters
			$uri = html::specialchars($uri, FALSE);

			if (PHP_SAPI === 'cli')
			{
				// Command line request
				$method = 'CLI';
			}
			elseif (isset($_SERVER['REQUEST_METHOD']))
			{
				// Set the request method
				$method = strtoupper($_SERVER['REQUEST_METHOD']);
			}
			else
			{
				// No request method available
				$method = NULL;
			}

			// Create the main request
			$request = new Kohana_Request($_GET, $_POST, $method);

			// Start output buffering, so that headers will be trapped
			ob_start(array('Kohana_Request', 'output_buffer'));

			// Display the output of the main request
			$output = $request->process($uri);

			// Run the system.display event on the output
			$output = Event::run('system.display', $output);

			if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])
				AND $level = Kohana_Config::get('config.output_compression')
				AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
			{
				if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE
					OR stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE)
				{
					if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
					{
						$compress = 'gzip';
					}
					elseif (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE)
					{
						$compress = 'deflate';
					}

					if (isset($compress))
					{
						if ($level < 1 OR $level > 9)
						{
							// Normalize the level to be an integer between 1 and 9. This
							// step must be done to prevent gzencode from triggering an error
							$level = max(1, min($level, 9));
						}

						switch ($compress)
						{
							case 'gzip':
								// Compress output using gzip
								$output = gzencode($output, $level);
							break;
							case 'deflate':
								// Compress output using zlib (HTTP deflate)
								$output = gzdeflate($output, $level);
							break;
						}

						// This header must be sent with compressed content to prevent
						// browser caches from breaking
						header('Vary: Accept-Encoding');

						// Send the content encoding header
						header('Content-Encoding: '.$compress);

						// Sending Content-Length in CGI can result in unexpected behavior
						if (stripos(PHP_SAPI, 'cgi') === FALSE)
						{
							header('Content-Length: '.strlen($output));
						}
					}
					
				}
			}

			// Display the output
			echo $output;

			// This method has been run
			$run = TRUE;
		}
	}

	/**
	 * Creates a new request and returns the output.
	 *
	 * @param   string   request URI
	 * @param   array    GET array
	 * @param   array    POST array
	 * @param   string   request method
	 * @return  string
	 */
	public static function factory($uri, $get = NULL, $post = NULL, $method = NULL)
	{
		// Create a new request
		$request = new Kohana_Request($get, $post, $method);

		// Return the output
		return $request->process($uri);
	}

	public static function output_buffer($output)
	{
		if (Kohana_Config::get('config.render_stats') === TRUE)
		{
			// Get the total execution time and memory usage
			$benchmark = Benchmark::get('system.total_execution');

			// Replace the global template variables
			$output = str_replace
			(
				array
				(
					'{kohana_version}',
					'{kohana_codename}',
					'{execution_time}',
					'{memory_usage}',
					'{included_files}',
				),
				array
				(
					KOHANA_VERSION,
					KOHANA_CODENAME,
					number_format($benchmark['time'], 4),
					number_format($benchmark['memory'] / 1024 / 1024, 2).'MB',
					count(get_included_files()),
				),
				$output
			);
		}

		return $output;
	}


	// The request method
	protected $request_method = 'GET';

	// Current route, controller, method, and arguments
	protected $route;
	protected $controller;
	protected $method;
	protected $arguments;

	// GET and POST data
	protected $get;
	protected $post;

	/**
	 * Prepares a new request.
	 *
	 * @param   array    GET array
	 * @param   array    POST array
	 * @param   string   request method
	 * @return  string
	 */
	protected function __construct($get = NULL, $post = NULL, $method = NULL)
	{
		if (is_string($method))
		{
			// Set the request method
			$this->method = $method;
		}

		if ( ! is_array($get))
		{
			// Use global GET
			$get = $_GET;
		}

		if ( ! is_array($post))
		{
			// Use global POST
			$post = $_POST;
		}

		// Set GET and POST data
		$this->get  = $get;
		$this->post = $post;
	}

	/**
	 * Returns a value from the request GET data. If the key does not exist in
	 * the current GET data, the default value will be returned.
	 *
	 * @param   string   array key
	 * @param   mixed    default value
	 * @param   boolean  use XSS cleaning on the value
	 * @return  mixed
	 */
	public function get($key, $default = NULL, $xss_clean = FALSE)
	{
		if (isset($this->get[$key]))
		{
			$value = $this->get[$key];

			if ($xss_clean === TRUE)
			{
				// @todo: XSS cleaning
			}

			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Returns a value from the request POST data. If the key does not exist in
	 * the current POST data, the default value will be returned.
	 *
	 * @param   string   array key
	 * @param   mixed    default value
	 * @param   boolean  use XSS cleaning on the value
	 * @return  mixed
	 */
	public function post($key, $default = NULL, $xss_clean = FALSE)
	{
		if (isset($this->post[$key]))
		{
			$value = $this->post[$key];

			if ($xss_clean === TRUE)
			{
				// @todo: XSS cleaning
			}

			return $value;
		}
		else
		{
			return $default;
		}
	}

	public function process($uri)
	{
		if ( ! $this->find_route($uri))
		{
			// No route could be found for this URI
			throw new Kohana_Exception('core.page_not_found', $uri);
		}

		try
		{
			// Start validation of the controller
			$class = new ReflectionClass('Controller_'.ucfirst($this->controller));
		}
		catch (ReflectionException $e)
		{
			// Controller does not exist
			throw new Kohana_Exception('core.page_not_found', $uri);
		}

		if ($class->isAbstract() OR (IN_PRODUCTION AND $class->getConstant('ALLOW_PRODUCTION') == FALSE))
		{
			// Controller is not allowed to run in production
			throw new Kohana_Exception('core.page_not_found', $uri);
		}

		// Start output buffering
		ob_start();

		// Cache the previous controller
		$previous_controller = Kohana_Request::$instance;

		// Create a new controller instance, passing the request to the controller
		Kohana_Request::$instance = $controller = $class->newInstance($this);

		try
		{
			// Load the controller method
			$method = $class->getMethod($this->method);

			if ($method->isProtected() or $method->isPrivate())
			{
				// Do not attempt to invoke protected methods
				throw new ReflectionException('invalid router method');
			}

			// Default arguments
			$arguments = $this->arguments;
		}
		catch (ReflectionException $e)
		{
			// Use __call instead
			$method = $class->getMethod('__call');

			// Use arguments in __call format
			$arguments = array($this->method, $this->arguments);
		}

		// Execute the controller method
		$method->invokeArgs($controller, $arguments);

		// Get the end-of-request method
		$class->getMethod('_end_request')->invoke($controller);

		if (is_object($previous_controller))
		{
			// Restore the previous controller
			Kohana_Request::$instance = $previous_controller;
		}

		// Return the output
		return ob_get_clean();
	}

	/**
	 * Creates a URI for the given route.
	 *
	 * @param   string   route name
	 * @param   array    route key values
	 * @return  string
	 */
	public function uri($route, array $values = array())
	{
		if ($route === TRUE)
		{
			$route = $this->route;

			$values = array_merge
			(
				array('controller' => $this->controller, 'method' => $this->method),
				$this->arguments,
				$values
			);
		}
		
		if ( ! ($route = Kohana_Config::get('routes.'.$route)))
		{
			throw new Kohana_Exception('core.route_not_found', $route);
		}

		// Copy the URI, it will have parameters replaced
		$uri = $route['uri'];

		// Get the URI keys from the route
		$keys = $this->route_keys($uri);

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
	public function route_keys($uri)
	{
		if (strpos($uri, ':') === FALSE)
			return array();

		// Find all keys that start with a colon
		preg_match_all('#(?<=:)[a-z_]{1,32}#', $uri, $keys);

		return $keys[0];
	}

	protected function find_route($uri)
	{
		// Load routes
		$routes = Kohana_Config::get('routes', array());

		if (count($routes) > 1)
		{
			// Get the default route
			$default = $routes['default'];

			// Remove it from the routes
			unset($routes['default']);

			// Add the default route at the end
			$routes['default'] = $default;
		}

		// Controller, method, and arguments
		$controller = $method = NULL;
		$arguments  = array();

		foreach ($routes as $name => $route)
		{
			// Compile the route into regex
			$regex = $this->compile_route($route);

			if (preg_match('#^'.$regex.'$#u', $uri, $matches))
			{
				if (isset($route['request']) AND $route['request'] !== $this->request_method)
				{
					// The request method is invalid
					continue;
				}

				foreach ($matches as $key => $value)
				{
					if (is_int($key))
					{
						// Skip matches that are not named
						continue;
					}

					if ($value !== '')
					{
						// Overload the route with the matched value
						$route['defaults'][$key] = $value;
					}
				}

				if (isset($route['prefix']))
				{
					foreach ($route['prefix'] as $key => $prefix)
					{
						if (isset($route['defaults'][$key]))
						{
							// Add the prefix to the key
							$route['defaults'][$key] = $route['prefix'][$key].$route['defaults'][$key];
						}
					}
				}

				foreach ($route['defaults'] as $key => $val)
				{
					if (is_int($key) OR $key === 'controller' OR $key === 'method')
					{
						// These keys are not arguments, skip them
						continue;
					}

					$this->arguments[$key] = $val;
				}

				// A matching route has been found
				$this->route = $name;

				// Set controller name
				$this->controller = $route['defaults']['controller'];

				if (isset($route['defaults']['method']))
				{
					// Set controller method
					$this->method = $route['defaults']['method'];
				}
				else
				{
					// Default method
					$this->method = 'index';
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	protected function compile_route(array $route)
	{
		if ($route['uri'] === '')
		{
			// Empty route
			return '';
		}

		// Split the route URI by slashes
		$uri = explode('/', $route['uri']);

		// Regular expression end
		$end = '';

		// Nothing is optional yet
		$optional = FALSE;

		foreach ($uri as $i => $segment)
		{
			if ($segment[0] === ':')
			{
				// Find the actual segment key and any trailing garbage
				preg_match('#^:([a-z_]{1,32})(.*)$#', $segment, $matches);

				// Segment key
				$key = $matches[1];

				// Regular expression
				$exp = '';

				if ($optional === FALSE AND isset($route['defaults'][$key]))
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

				if (isset($route['regex'][$key]))
				{
					// Matches specified regex for the segment
					$exp .= '('.$name.$route['regex'][$key].')';
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

} // End Kohana_Request
