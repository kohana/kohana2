<?php defined('SYSPATH') or die('No direct script access.');
/**
 * KohanaConfig abstract driver to get and set
 * configuration options.
 * 
 * Specific drivers should implement caching and encryption
 * as they deem appropriate.
 *
 * $Id$
 *
 * @package    KohanaConfig
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 * @abstract
 */
abstract class KohanaConfig_Driver {

	/**
	 * Internal caching
	 *
	 * @var     Cache
	 */
	protected $cache;

	/**
	 * The name of the internal cache
	 *
	 * @var     string
	 */
	protected $cache_name = 'KohanaConfig_Cache';

	/**
	 * The Encryption library
	 *
	 * @var     Encrypt
	 */
	protected $encrypt;

	/**
	 * The config loaded
	 *
	 * @var     array
	 */
	protected $config = array();

	/**
	 * The changed status of configuration values,
	 * current state versus the stored state.
	 *
	 * @var     bool
	 */
	protected $changed = FALSE;

	/**
	 * Gets a value from config. If required is TRUE
	 * then get will throw an exception if value cannot
	 * be loaded.
	 *
	 * @param   string       key  the setting to get
	 * @param   bool         slash  remove trailing slashes
	 * @param   bool         required  is setting required?
	 * @return  mixed
	 * @access  public
	 */
	public function get($key, $slash = FALSE, $required = FALSE)
	{
		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Check for existing value and load it dynamically if required
		if ( ! isset($this->config[$group]))
			$this->config[$group] = $this->load($group, $required);

		// Get the value of the key string
		$value = Kohana::key_string($this->config, $key);

		if ($slash === TRUE AND is_string($value) AND $value !== '')
		{
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Sets a new value to the configuration
	 *
	 * @param   string       key 
	 * @param   mixed        value 
	 * @return  bool
	 * @access  public
	 */
	public function set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		$this->get($key);

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
		$conf =& $this->config;
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

		if ($key === 'core.extensions')
		{
			// Reprocess the include paths
			Kohana::include_paths(TRUE);
		}

		// Set config to changed
		return $this->changed = TRUE;
	}

	/**
	 * Clear the configuration
	 *
	 * @param   string       group 
	 * @return  bool
	 * @access  public
	 */
	public function clear($group)
	{
		// Remove the group from config
		unset($this->config[$group]);

		// Set config to changed
		return $this->changed = TRUE;
	}

	/**
	 * Checks whether the setting exists in
	 * config
	 *
	 * @param   string $key 
	 * @return  bool
	 * @access  public
	 */
	public function setting_exists($key)
	{
		return $this->get($key) === NULL;
	}

	/**
	 * Loads a configuration group based on the setting
	 *
	 * @param   string       group 
	 * @param   bool         required 
	 * @return  array
	 * @access  public
	 * @abstract
	 */
	abstract public function load($group, $required = FALSE);

} // End Kohana_Config_Driver