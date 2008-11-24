<?php

class Kohana_Route {

	const REGEX_KEY     = '/:[a-zA-Z0-9_]++/';
	const REGEX_SEGMENT = '[^/.,;?]++';

	public static function factory($uri, array $regex = array())
	{
		// Create a new route
		return new Route($uri, $regex);
	}

	protected $uri = '';
	protected $regex = array();
	protected $defaults = array('method' => 'index');

	// Compiled regex cache
	protected $compiled;

	// Matched URI keys
	protected $keys = array();

	/**
	 * Creates a new route. The URI may contain named "keys", in the format
	 * of :key. Each :key will be translated to a regular expression using a
	 * default regular expression pattern. You can override any :key by
	 * providing a pattern for the key in the second parameter:
	 * 
	 *     // This route will only match when :id is a digit
	 *     new Route('user/edit/:id', array('id' => '\d+'));
	 * 
	 *     // This route will match when :path is anything
	 *     new Route(':path', array('path' => '.*'));
	 * 
	 * It is also possible to create optional segments by using parenthesis in
	 * the URI definition:
	 * 
	 *     // This is the standard default route, and no keys are required
	 *     new Route('(:controller(/:method(/:id)))');
	 * 
	 *     // This route only requires the :file key
	 *     new Route('(:path/):file(:format)', array('path' => '.*', 'format' => '\.\w+'));
	 * 
	 * @param   string   route URI pattern
	 * @param   array    key patterns
	 */
	public function __construct($uri, array $regex = array())
	{
		$this->uri = $uri;
		$this->regex = $regex;
	}

	/**
	 * Provides default values for keys when they are not present. The default
	 * method will always be "index" unless it is overloaded with this method.
	 * 
	 *     $route->defaults(array('controller' => 'welcome', 'method' => 'index'));
	 * 
	 * @chainable
	 * @param   array  key values
	 * @return  Route
	 */
	public function defaults(array $defaults)
	{
		if (empty($defaults['method']))
		{
			$defaults['method'] = 'index';
		}

		$this->defaults = $defaults;

		return $this;
	}

	/**
	 * Tests if the route matches a given URI. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 * 
	 *     // This route will only match if the :controller, :method, and :id exist
	 *     $params = Route::factory(':controller/:method/:id', array('id' => '\d+'))
	 *         ->match('users/edit/10');
	 *     // The parameters are now:
	 *     // controller = users
	 *     // method = edit
	 *     // id = 10
	 * 
	 * This method should almost always be used within an if/else block:
	 * 
	 *     if ($params = $route->match($uri))
	 *     {
	 *         // Parse the parameters
	 *     }
	 * 
	 * @param   string  URI to match
	 * @return  array   on success
	 * @return  FALSE   on failure
	 */
	public function matches($uri)
	{
		// Get the compiled regex
		$regex = $this->compile();

		if (preg_match('!'.$regex.'!', $uri, $matches))
		{
			$params = array();
			foreach ($matches as $key => $value)
			{
				if (is_int($key))
				{
					// Skip all unnamed keys
					continue;
				}

				// Set the value for all matched keys
				$params[$key] = $value;
			}

			foreach ($this->defaults as $key => $value)
			{
				if ( ! isset($params[$key]))
				{
					// Set default values for any key that was not matched
					$params[$key] = $value;
				}
			}

			return $params;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Returns the compiled regular expression for the route. The generated
	 * pattern will be cached after it is compiled.
	 * 
	 * @return  string
	 */
	protected function compile()
	{
		if (isset($this->cache[$this->uri]))
		{
			// The regex has already been compiled
			return $this->cache[$this->uri];
		}

		if (strpos($this->uri, '(') === FALSE)
		{
			// No optional parts of the URI
			$regex = $this->uri;
		}
		else
		{
			// Make optional parts of the URI non-capturing and optional
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $this->uri);
		}

		if (preg_match_all(Route::REGEX_KEY, $regex, $keys))
		{
			// Compile every :key into its regex equivalent
			$replace = $this->compile_keys($keys[0]);

			// Replace each :key with with <key>PATTERN
			$regex = strtr($regex, $replace);
		}

		// Add anchors and cache the compiled regex
		return $this->cache[$this->uri] = '^'.$regex.'$';
	}

	protected function compile_keys(array $keys)
	{
		$groups = array();
		foreach ($keys as $key)
		{
			// Get the key name
			$name = substr($key, 1);

			// Name the matched segment
			$regex = '(?P<'.$name.'>';

			if (isset($this->regex[$name]))
			{
				// Use the pre-defined pattern
				$regex .= $this->regex[$name];
			}
			else
			{
				// Use the default pattern
				$regex .= Route::REGEX_SEGMENT;
			}

			// Add the regex group with its key
			$groups[$key] = $regex.')';
		}

		return $groups;
	}

} // End Kohana_Route
