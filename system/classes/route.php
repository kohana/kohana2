<?php

class Kohana_Route {

	public static function factory($uri, array $regex = array())
	{
		return new Route($uri, $regex);
	}

	protected $uri = '';
	protected $regex = array();
	protected $defaults = array();

	// Compiled regex cache
	protected $compiled;

	// Matched URI keys
	protected $keys = array();

	public function __construct($uri, array $regex)
	{
		$this->uri = $uri;
		$this->regex = $regex;
	}

	public function defaults(array $defaults)
	{
		$this->defaults = $defaults;

		return $this;
	}

	public function matches($uri)
	{
		$regex = $this->compile();

		echo Kohana::debug($regex);

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

	protected function compile()
	{
		if ($this->compiled !== NULL)
		{
			return $this->compiled;
		}

		if (strpos($this->uri, '(') === FALSE)
		{
			// No optional parts of the URI
			$regex = $this->uri;
		}
		else
		{
			// Make opational parts of the URI non-capturing and optional
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $this->uri);
		}

		if (preg_match_all('!:[a-zA-Z0-9_]+!', $regex, $keys))
		{
			$replace = $this->compile_keys($keys[0]);

			// Replace each :key with (?<key>REGEX)
			$regex = strtr($regex, $replace);
		}

		return $this->compiled = '^'.$regex.'$';
	}

	protected function compile_groups($uri)
	{
		preg_match('/^.*?\(.+\)/', $uri, $match);
		
		echo Kohana::debug($match);
		
		if (preg_match('/^.*?[\(\)]/', $uri, $match))
		{
			// Start the regex
			$regex = $match = $match[0];

			if (substr($regex, -1) === '(')
			{
				$depth++;

				$regex .= '?:';

				if (strlen($regex) > 1)
				{
					$regex .= ')?';
				}

				$next_uri = substr($uri, strlen($match));
			}
			elseif (strpos($regex, ')') !== FALSE)
			{
				$depth--;

				// Find the position of the opening and closing parenthesis
				$open  = strlen($match);
				$close = strrpos($uri, ')');

				$next_uri = substr($uri, $open, $close - $open);
			}

			// Add the next inner group
			$regex .= $this->compile_groups($next_uri, $dpeth);

			if ($depth > 0)
			{
				// Make the group optional
				$regex .= ')?';
			}

		}
		else
		{
			if ($depth > 0)
			{
				$depth--;
			}

			// There are no groups
			$regex = $uri;

			echo Kohana::debug('no groups', $uri);
		}

		return $regex;
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
				// Use the pre-defined regex
				$regex .= $this->regex[$name];
			}
			else
			{
				// Match anything that is not a slash
				$regex .= '[^/.,;?]++';
			}

			// Add the regex group with its key
			$groups[$key] = $regex.')';
		}
		return $groups;
	}

} // End Kohana_Route
