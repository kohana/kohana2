<?php

class Kohana_Config_Core {

	// Configuration file values
	protected static $config = array();

	// Configuration values cached by access key
	protected static $cache = array();

	/**
	 * Get a configuration value.
	 *
	 * @param   string  key.string
	 * @param   mixed   value to return if the key is not found
	 * @return  mixed
	 */
	public static function get($key, $default = NULL)
	{
		if (array_key_exists($key, Kohana_Config::$cache))
		{
			// Return the cached value
			return Kohana_Config::$cache[$key];
		}

		if (strpos($key, '.') === FALSE)
		{
			// Only the filename is present
			$file = $key;
			$keys = NULL;
		}
		else
		{
			// Split the file and key
			list($file, $keys) = explode('.', $key, 2);
		}

		if ($file === 'core')
		{
			// Core is an alias to config
			$file = 'config';
		}

		if ( ! isset(Kohana_Config::$config[$file]))
		{
			if (is_array($files = Kohana::find_file('config', $file)))
			{
				$configuration = array();
				foreach ($files as $file)
				{
					include $file;

					// Merge the config files together
					$configuration = array_merge($configuration, $config);
				}

				// Add the file to the config cache
				Kohana_Config::$config[$file] = $configuration;
			}
		}
		else
		{
			// No configuration exists
			Kohana_Config::$config[$file] = array();
		}

		// Cache and return the configuration value
		return Kohana_Config::$cache[$key] = ($keys === NULL) ? Kohana_Config::$config[$file] : Kohana::key_string(Kohana_Config::$config[$file], $keys, $default);
	}

	/**
	 * Set a configuration value.
	 *
	 * @param   string  key.string
	 * @param   mixed   new value
	 * @return  void
	 */
	public static function set($key, $value)
	{
		if (array_key_exists($key, Kohana_Config::$cache))
		{
			// Delete the cache
			unset(Kohana_Config::$cache[$key]);
		}

		if (strpos($key, '.') === FALSE)
		{
			// Only the filename is present
			$file = $key;
			$keys = NULL;
		}
		else
		{
			// Split the file and key
			list($file, $keys) = explode('.', $key, 2);
		}

		if ($keys === NULL)
		{
			Kohana_Config::$config[$file] = $value;
		}
		else
		{
			Kohana::key_string_set(Kohana_Config::$config, $keys, $value);
		}
	}

	/**
	 * Clears a configuration file from the cache.
	 *
	 * @param   string   filename
	 * @return  void
	 */
	public static function clear($file)
	{
		unset(Kohana_Config::$config[$file], Kohana_Config::$cache[$file]);

		// Get the cache keys
		$keys = array_keys(Kohana_Config::$cache);

		// Make the filename into the start of a cache key
		$file .= '.';

		foreach ($keys as $key)
		{
			if (strpos($key, $file) === 0)
			{
				// This key is part of the same file group
				unset(Kohana_Config::$cache[$key]);
			}
		}
	}

} // End Kohana_Config
