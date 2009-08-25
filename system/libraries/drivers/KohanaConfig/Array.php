<?php defined('SYSPATH') or die('No direct script access.');
/**
 * KohanaConfig Array driver to get and set
 * configuration options using PHP arrays.
 * 
 * This driver can cache and encrypt settings
 * if required.
 *
 * $Id$
 *
 * @package    KohanaConfig
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class KohanaConfig_Array_Driver extends KohanaConfig_Driver {

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
	protected $cache_name = 'KohanaConfig_Array_Cache';

	/**
	 * The Encryption library
	 *
	 * @var     Encrypt
	 */
	protected $encrypt;

	/**
	 * Array driver constructor. Sets up the PHP array
	 * driver, including caching and encryption if
	 * required
	 *
	 * @access  public
	 */
	public function __construct()
	{
		
		if (($cache_setting = $this->get('core.internal_cache')) !== FALSE)
		{
			// Setup the cache configuration
			$cache_config = array
			(
				'driver'      => 'file',
				'params'      => $this->get('core.internal_cache_path'),
				'lifetime'    => $cache_setting,
			);

			// Load a cache instance
			$this->cache = Cache::instance();

			// If encryption is required
			if ($this->get('core.internal_cache_encrypt'))
			{
				$encryption_config = array
				(
					'key'    => $this->get('core.internal_cache_key'),
					'mode'   => MCRYPT_MODE_ECB,
					'cipher' => MCRYPT_RIJNDAEL_256
				);

				// Initialise encryption
				$this->encrypt = Encrypt::instance($encryption_config);
			}

			// Restore the cached configuration
			$this->config = $this->load_cache();

			// Add the save cache method to system.shutshut event
			Event::add('system.shutdown', array($this, 'save_cache'));

		}


	}

	/**
	 * Loads a configuration group based on the setting
	 *
	 * @param   string       group 
	 * @param   bool         required 
	 * @return  array
	 * @access  public
	 */
	public function load($group, $required = FALSE)
	{
		if ($group === 'core')
		{
			// Load the application configuration file
			require APPPATH.'config/config'.EXT;

			if ( ! isset($config['site_domain']))
			{
				// Invalid config file
				die('Your Kohana application configuration file is not valid.');
			}

			return $config;
		}

		// Load matching configs
		$configuration = array();

		if ($files = Kohana::find_file('config', $group, $required))
		{
			foreach ($files as $file)
			{
				require $file;

				if (isset($config) AND is_array($config))
				{
					// Merge in configuration
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		// Return merged configuration
		return $configuration;
	}

	/**
	 * Loads the cached version of this configuration driver
	 *
	 * @return  array
	 * @access  public
	 */
	public function load_cache()
	{
		// Load the cache for this configuration

		$cached_config = $this->cache->get($this->cache_name);

		// If the configuration was loaded from the cache
		if ($cached_config !== NULL)
		{
			// If encryption is enabled
			if ($this->encrypt instanceof Encrypt)
				$cached_config = unserialize($this->encrypt->decode($cached_config));
		}
		else
			$cached_config = array();

		// Return the cached config
		return $cached_config;
	}

	/**
	 * Saves a cached version of this configuration driver
	 *
	 * @return  bool
	 * @access  public
	 */
	public function save_cache()
	{
		// If this configuration has changed
		if ($this->get('core.internal_cache') !== FALSE AND $this->changed)
		{
			// Encrypt the config if required
			if ($this->encrypt instanceof Encrypt)
				$data = serialize($this->encrypt->encode($this->config));
			else
				$data = $this->config;

			// Save the cache
			return $this->cache->set($this->cache_name, $data);
		}

		return TRUE;
	}
} // End Kohana_Config_Array_Driver