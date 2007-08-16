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
	public static function item($key)
	{
		// Configuration autoloading
		if (self::$conf == NULL)
		{
			require APPPATH.'config/config'.EXT;

			// Invalid config file
			(isset($config) AND is_array($config)) or die('Core configuration file is not valid.');

			// Normalize all paths to be absolute and have a trailing slash
			foreach($config['include_paths'] as $n => $path)
			{
				if (substr($path, 0, 1) !== '/')
				{
					$config['include_paths'][$n] = realpath(DOCROOT.$path).'/';
				}
				else
				{
					$config['include_paths'][$n] = rtrim($path, '/').'/';
				}
			}

			$config['include_paths'] = array_merge
			(
				array(APPPATH), // APPPATH first
				$config['include_paths'],
				array(SYSPATH)  // SYSPATH last
			);

			self::$conf = $config;
		}

		return (isset(self::$conf[$key]) ? self::$conf[$key] : FALSE);
	}

	/**
	 * Load a config file
	 *
	 * @access  public
	 * @param   string
	 * @return  array
	 */
	public static function load($name)
	{
		try
		{
			$configuration = array();

			foreach(Kohana::find_file('config', $name, TRUE) as $filename)
			{
				include $filename;

				// Merge in configuration
				if (isset($config) AND is_array($config))
				{
					$configuration = array_merge($configuration, $config);
				}
			}
		}
		catch (file_not_found $execption)
		{
			/**
			 * @todo this needs to be handled better
			 */
			exit('Your <kbd>config/'.$name.EXT.'</kbd> file could not be loaded.');
		}

		return $configuration;
	}

} // End Config class