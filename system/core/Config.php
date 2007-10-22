<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Config
 *  Loads configuration files and retrieves keys. This class is declared as final.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
final class Config {

	// Entire configuration
	private static $conf;

	// Include paths
	private static $include_paths;

	/*
	 * Method: item
	 *  Get a config item or group.
	 *
	 * Parameters:
	 *  key      - item name
	 *  slash    - force a forward slash (/) at the end of the item
	 *  required - is the item required?
	 *
	 * Returns:
	 *  The item defined by key. This can be a string, array, or boolean value.
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

			// Start setting include paths, APPPATH first
			self::$include_paths = array(APPPATH);

			// Normalize all paths to be absolute and have a trailing slash
			foreach($config['include_paths'] as $path)
			{
				if (($path = str_replace('\\', '/', realpath($path))) == '')
					continue;

				self::$include_paths[] = $path.'/';
			}

			// Finish setting include paths by adding SYSPATH
			self::$include_paths[] = SYSPATH;

			// Load config into self
			self::$conf['core'] = $config;
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

	/*
	 * Method: include_paths
	 *  Get all include paths.
	 *
	 * Returns:
	 *  Include paths as an array, APPPATH first.
	 */
	public static function include_paths()
	{
		return self::$include_paths;
	}

	/*
	 * Method: load
	 *  Load a config file.
	 *
	 * Parameters:
	 *  name     - config filename, without extension
	 *  required - is the file required?
	 *
	 * Returns:
	 *  Array of config items in file.
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

} // End Config