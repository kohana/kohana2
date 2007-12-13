<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Loads configuration files and retrieves keys. This class is declared as final.
 * 
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
final class Config {

	// Entire configuration
	private static $conf;

	// Include paths
	private static $include_paths;

	/**
	 * Get a config item or group.
	 *
	 * @param   string                item name
	 * @param   boolean               force a forward slash (/) at the end of the item
	 * @param   boolean               is the item required?
	 * @return  string|array|boolean
	 */
	public static function item($key, $slash = FALSE, $required = TRUE)
	{
		// Configuration autoloading
		if (self::$conf === NULL)
		{
			// Load the application configuration file
			require APPPATH.'config/config'.EXT;

			// Invalid config file
			(isset($config) AND is_array($config)) or die
			(
				'Your Kohana application configuration file is not valid.'
			);

			// Load config into self
			self::$conf['core'] = $config;

			// Re-parse the include paths
			self::include_paths(TRUE);
		}

		// Requested group
		$group = current(explode('.', $key));

		// Load the group if not already loaded
		if ( ! isset(self::$conf[$group]))
		{
			self::$conf[$group] = self::load($group, $required);
		}

		// Get the value of the key string
		$value = Kohana::key_string($key, self::$conf);

		return
		// If the value is not an array, and the value should end with /
		( ! is_array($value) AND $slash == TRUE AND $value != '')
		// Trim the string and force a slash on the end
		? rtrim((string) $value, '/').'/'
		// Otherwise, just return the value
		: $value;
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function set($key, $value)
	{
		// Config setting must be enabled
		if (Config::item('core.allow_config_set') == FALSE)
		{
			Log::add('debug', 'Config::set was called, but your configuration file does not allow setting.');
			return FALSE;
		}

		// Empty keys and core.allow_set cannot be set
		if (empty($key) OR $key == 'core.allow_config_set')
			return FALSE;

		// Do this to make sure that the config array is already loaded
		Config::item($key);

		// Convert dot-noted key string to an array
		$keys = explode('.', rtrim($key, '.'));

		// Used for recursion
		$conf =& self::$conf;
		$last = count($keys) - 1;

		foreach($keys as $i => $k)
		{
			if ( ! isset($conf[$k]))
				return FALSE;

			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}

		if ($key == 'core.include_paths')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Get all include paths.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array    include paths, APPPATH first
	 */
	public static function include_paths($process = FALSE)
	{
		if ($process == TRUE)
		{
			// Start setting include paths, APPPATH first
			self::$include_paths = array(APPPATH);

			// Normalize all paths to be absolute and have a trailing slash
			foreach(self::item('core.include_paths') as $path)
			{
				if (($path = str_replace('\\', '/', realpath($path))) == '')
					continue;

				self::$include_paths[] = $path.'/';
			}

			// Finish setting include paths by adding SYSPATH
			self::$include_paths[] = SYSPATH;
		}

		return self::$include_paths;
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array    config items in file
	 */
	public static function load($name, $required = TRUE)
	{
		$configuration = array();

		// Find all the configuartion files matching the name
		foreach(Kohana::find_file('config', $name, $required) as $filename)
		{
			include $filename;

			// Merge in configuration
			if (isset($config) AND is_array($config))
			{
				$configuration = array_merge($configuration, $config);
			}
		}

		return $configuration;
	}

	/**
	 * Emulates array_merge_recursive, but appends numeric keys and replaces
	 * associative keys.
	 *
	 * @param   array   any number of arrays
	 * @return  array
	 */
	public static function merge()
	{
		$total = func_num_args();

		$result = array();
		for($i = 0; $i < $total; $i++)
		{
			foreach(func_get_arg($i) as $key => $val)
			{
				if (isset($result[$key]))
				{
					if (is_array($val))
					{
						// Arrays are merged recursively
						$result[$key] = self::merge($result[$key], $val);
					}
					elseif (is_int($key))
					{
						// Simple arrays are appended
						array_push($result, $val);
					}
					else
					{
						// Associative arrays are replaced
						$result[$key] = $val;
					}
				}
				else
				{
					// New values are added
					$result[$key] = $val;
				}
			}
		}

		return $result;
	}

} // End Config