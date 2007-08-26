<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * A secure and lightweight open source web application framework.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 */

// ----------------------------------------------------------------------------

/**
 * Configuration class
 *
 * @category    Core
 * @author      Kohana Development Team
 * @link        http://kohanaphp.com/user_guide/core_classes.html
 */
final class Config {

	public static $conf; // Configuration array

	/**
	 * Return a config item
	 *
	 * @access  public
	 * @param   string
	 * @return  mixed
	 */
	public static function item($key, $slash = FALSE)
	{
		// Configuration autoloading
		if (self::$conf === NULL)
		{
			require APPPATH.'config/config'.EXT;

			// Invalid config file
			(isset($config) AND is_array($config)) or die('Core configuration file is not valid.');

			// Normalize all paths to be absolute and have a trailing slash
			foreach($config['include_paths'] as $path)
			{
				if (substr($path, 0, 1) !== '/')
				{
					$config['include_paths'][] = realpath(DOCROOT.$path).'/';
				}
				else
				{
					$config['include_paths'][] = rtrim($path, '/').'/';
				}
			}

			$config['include_paths'] = array_merge
			(
				array(APPPATH), // APPPATH first
				$config['include_paths'],
				array(SYSPATH)  // SYSPATH last
			);

			self::$conf['core'] = $config;
		}

		// Find the requested key
		$key  = explode('.', $key);
		// Find type and reset the key
		$type = $key[0];
		$key  = isset($key[1]) ? $key[1] : FALSE;

		// Load config arrays
		if ( ! isset(self::$conf[$type]))
		{
			self::$conf[$type] = self::load($type);
		}

		$value = FALSE;

		if ($key === FALSE)
		{
			$value = self::$conf[$type];
		}
		elseif (isset(self::$conf[$type][$key]))
		{
			$value = self::$conf[$type][$key];
			$value = ($slash == TRUE AND $value != '') ? rtrim($value, '/').'/' : $value;
		}

		return $value;
	}

	/**
	 * Load a config file
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */
	public static function load($name, $required = TRUE)
	{
		$required = (bool) $required;
		$configuration = array();

		try
		{
			foreach(Kohana::find_file('config', $name, $required) as $filename)
			{
				include $filename;

				// Merge in configuration
				if (isset($config) AND is_array($config))
				{
					$configuration = array_merge($configuration, $config);
				}
			}
		}
		catch (file_not_found $exception)
		{
			/**
			 * @todo this needs to be handled better
			 */
			exit('Your <kbd>config/'.$name.EXT.'</kbd> file could not be loaded.');
		}

		return $configuration;
	}

} // End Config class