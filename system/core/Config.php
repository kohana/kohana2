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

	// Cached configuration
	private static $cache;
	private static $cache_changed = FALSE;

	// Include paths
	private static $include_paths;

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function item($key, $slash = FALSE, $required = TRUE)
	{
		// Configuration autoloading
		if (self::$conf === NULL)
		{
			// Load core configuration
			self::$conf['core'] = self::load('core');

			// Re-parse the include paths
			self::include_paths(TRUE);
		}

		// Requested group
		$group = current(explode('.', $key));

		if ( ! isset(self::$conf[$group]))
		{
			// Load the group
			self::$conf[$group] = self::load($group, $required);
		}

		// Get the value of the key string
		$value = Kohana::key_string(self::$conf, $key);

		if ($slash === TRUE AND ! empty($value) AND ! is_array($value))
		{
			// Force the value to end with "/"
			$value = rtrim((string) $value, '/').'/';
		}

		return $value;
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
		if (Config::item('core.allow_config_set') !== TRUE)
		{
			// Config setting is not allowed
			Log::add('error', 'Config::set was called, but your configuration file does not allow setting.');
			return FALSE;
		}

		// Empty keys and core.allow_set cannot be set
		if (empty($key) OR $key === 'core.allow_config_set')
			return FALSE;

		// Do this to make sure that the config array is already loaded
		Config::item($key);

		// Clean key
		$key = rtrim($key, '.');

		if (substr($key, 0, 7) === 'routes.')
		{
			// Routes cannot contain sub keys due to possible dots in regex
			$keys = explode('.', $key, 2);
		}
		else
		{
			// Convert dot-noted key string to an array
			$keys = explode('.', $key);
		}

		// Used for recursion
		$conf =& self::$conf;
		$last = count($keys) - 1;

		foreach ($keys as $i => $k)
		{
			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}

		if ($key == 'core.modules')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Clears a config group from the cached configuration.
	 *
	 * @param   string  config group
	 * @return  TRUE
	 */
	public static function clear($key)
	{
		unset(self::$conf[$key], self::$cache[$key]);

		if (self::$cache_changed === FALSE)
		{
			// Cache has changed
			self::$cache_changed = TRUE;

			// Save cache at shutdown
			Event::add('system.shutdown', array(__CLASS__, 'write_config_cache'));
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
		if ($process === TRUE)
		{
			// Start setting include paths, APPPATH first
			self::$include_paths = array(APPPATH);

			// Normalize all paths to be absolute and have a trailing slash
			foreach (self::item('core.modules') as $path)
			{
				if (($path = str_replace('\\', '/', realpath($path))) === '')
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
		if ($name === 'core')
		{
			// Load the application configuration file
			include APPPATH.'config/config'.EXT;

			if (empty($config['site_domain']))
			{
				// Invalid config file
				die('Your Kohana application configuration file is not valid.');
			}

			return $config;
		}

		if (self::$cache === NULL)
		{
			// Load the config caches
			self::$cache = (array) Kohana::load_cache('configuration');
		}

		if (isset(self::$cache[$name]))
			return self::$cache[$name];

		// Load matching configs
		$configuration = array();
		foreach (Kohana::find_file('config', $name, $required) as $filename)
		{
			// Import the config
			include $filename;

			if (isset($config) AND is_array($config))
			{
				// Merge in configuration
				$configuration = array_merge($configuration, $config);
			}
		}

		if (self::$cache_changed === FALSE)
		{
			// Cache has changed
			self::$cache_changed = TRUE;

			// Save cache at shutdown
			Event::add('system.shutdown', array(__CLASS__, 'write_config_cache'));
		}

		return self::$cache[$name] = $configuration;
	}

	/**
	 * Writes the configuration cache.
	 *
	 * @return  boolean
	 */
	public static function write_config_cache()
	{
		// Save updated cache
		return Kohana::save_cache('configuration', self::$cache);
	}

} // End Config