<?php

class Core {

	private static $instance;

	/**
	 * Pluralize a string
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public static function plural($str)
	{
		$str = trim($str);
		$end = substr($str, -1);
		$low = (strcmp($end, strtolower($end)) === 0) ? TRUE : FALSE;

		if (preg_match('/[sxz]$/i', $str) OR preg_match('/[^aeioudgkprt]h$/i', $str))
		{
			$end = 'es';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}
		elseif (preg_match('/[^aeiou]y$/i', $str))
		{
			$end = 'ies';
			$end = ($low == FALSE) ? strtoupper($end) : $end;
			$str = substr_replace($str, $end, -1);
		}
		else
		{
			$end = 's';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}

		return $str;
	}

	/**
	 * Singularize a string
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public static function singular($str)
	{
		$str = trim($str);
		$end = substr($str, -3);

		if ($end == 'ies')
		{
			$str = substr($str, 0, strlen($str)-3).'y';
		}
		elseif ($end == 'ses' || $end == 'zes' || $end == 'xes')
		{
			$str = substr($str, 0, strlen($str)-2);
		}
		else
		{
			$end = substr($str, -1);

			if ($end == 's')
			{
				$str = substr($str, 0, strlen($str)-1);
			}
		}

		return $str;
	}

	/**
	 * Fetch an item from the config array
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public static function config_item($item)
	{
		static $config;

		if ($config === NULL)
		{
			(@include(APPPATH.'configs/core'.EXT)) OR die
			(
				'<kbd>application/configs/core'.EXT.'</kbd> not found.'
			);

			(is_array($config)) OR die
			(
				'Your <kbd>application/configs/core'.EXT.'</kbd> file is not valid.'
			);
		}

		return (isset($config[$item]) ? $config[$item] : FALSE);
	}

	/**
	 * Fetch include paths
	 *
	 * @access  public
	 * @return  array
	 */
	public static function include_paths()
	{
		static $paths;

		if ($paths === NULL)
		{
			$paths = self::config_item('include_paths');

			foreach($paths as $key => $path)
			{
				if (substr($path, 0, 1) !== '/')
				{
					$path = realpath(DOCROOT.$path);

					$paths[$key] = $path.'/';
				}
				else
				{
					$paths[$key] = rtrim($path, '/').'/';
				}
			}

			$paths = array_merge
			(
				array(APPPATH),
				$paths,
				array(SYSPATH)
			);
		}

		return $paths;
	}

	/**
	 * Load a resource file
	 *
	 * @access  public
	 * @param   string  type of resource
	 * @param   string  name of resource
	 * @return  mixed
	 */
	public static function load_file($type, $name)
	{
		static $loaded;

		if ($type == FALSE OR $name == FALSE)
			return FALSE;

		// Search for filename
		if (($filename = self::find_file($type, $name)) == FALSE)
			return FALSE;

		switch($type)
		{
			case 'library':
				if (isset($loaded[$name]))
					return TRUE;

				include($filename);

				$class = ucfirst($name);

				if (($extension = self::find_file($type, self::config_item('subclass_prefix').$name)) == FALSE)
				{
					eval('class '.$class.' extends Core_'.$class.' {}');
				}
				else
				{
					include $extension;
				}

				$loaded[$name] = TRUE;

				return TRUE;
			break;
			case 'config':
				include $filename;

				return (isset($config) ? $config : array());
			break;
			default:
				include $filename;

				return TRUE;
			break;
		}
	}

	/**
	 * Find a resource file
	 *
	 * @access  public
	 * @param   string  filename, NO EXTENSION
	 * @return  string
	 */
	public static function find_file($type, $name)
	{
		$filename = self::plural($type).'/'.$name;

		foreach(self::include_paths() as $path)
		{
			$search = realpath($path.'/'.$filename.EXT);

			if ($search === FALSE OR ! file_exists($search))
				continue;

			return $search;
		}

		return FALSE;
	}

	/**
	 * Show an error
	 *
	 * @access  public
	 * @param   string  type of error
	 * @param   string  line of message
	 * @param   mixed   variables to insert into the message
	 * @return  string
	 */
	public static function show_error($type, $line, $args = FALSE)
	{
		static $language;
		static $messages;

		if ($language == NULL)
		{
			(($language = self::config_item('locale')) != FALSE) OR die
			(
				'You need to set the <code>locale</code> parameter in <kbd>config'.EXT.'</kbd>.'
			);
		}

		if ( ! isset($messages[$type]))
		{
			include SYSPATH.'i18n/'.$language.'/'.ucfirst($type).EXT;
			$messages[$type] = $lang;
		}

		if (isset($messages[$type][$line]))
		{
			list ($heading, $message) = $messages[$type][$line];

			if ($args !== FALSE)
			{
				$message = vsprintf($message, (array) $args);
			}
		}
		else
		{
			$heading = 'Unknown Error';
			$message = 'An unknown error has occurred.';
		}

		// Load error message
		if (IS_CLI)
		{
			print implode("\n", array
			(
				'Error: '.$heading,
				'---------------------------------------------',
				strip_tags($message),
			));
			print "\n";
		}
		else
		{
			switch($line)
			{
				case 'page_not_found':
					header('HTTP/1.1 404 Not Found');
				break;
			}
			@include(SYSPATH.'core/Message'.EXT);
		}
		exit;
	}

	/**
	 * Initialize the Controller
	 *
	 * @access  public
	 * @return  void
	 */
	public static function initialize()
	{
		if ( ! is_object(self::$instance))
		{
			// Run the pre_controller hook
			self::load_file('hook', 'pre_controller');

			// Load the controller
			require_once Router::$directory.Router::$controller.EXT;

			// Set Controller class name
			$controller = ucfirst(Router::$controller);

			// Validate the Controller
			(class_exists($controller) AND substr(Router::$method, 0, 1) != '_' AND in_array(Router::$method, get_class_methods($controller), TRUE)) OR self::show_error
			(
				'core', 'page_not_found', $controller
			);

			// Initialize the Controller
			self::$instance = new $controller();

			// Run the post_controller_constructor hook
			self::load_file('hook', 'post_controller_constructor');

			// Call the routed method
			call_user_func_array(array(self::$instance, Router::$method), Router::$arguments);

			// Run the post_controller hook
			self::load_file('hook', 'post_controller');
		}
	}

} // End Core class
