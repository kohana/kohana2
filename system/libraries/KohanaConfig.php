<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides a driver-based interface for setting and getting
 * configuration options for the Kohana environment
 *
 * $Id$
 *
 * @package    KohanaConfig
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class KohanaConfig_Core implements ArrayAccess {

	/**
	 * The default Kohana_Config driver
	 * to use for system setup
	 *
	 * @var     string
	 * @static
	 */
	public static $default_driver;

	/**
	 * Kohana_Config drivers initialised
	 *
	 * @var     array
	 * @static
	 */
	protected static $drivers = array();

	/**
	 * Returns a new instance of the Kohana_Config library
	 * based on the singleton pattern
	 *
	 * @param   string       driver
	 * @return  Kohana_Config
	 * @access  public
	 * @static
	 */
	public static function & instance($driver = FALSE)
	{
		// If there is no driver defined, use the default one
		if ($driver === FALSE)
			$driver = KohanaConfig::$default_driver;

		// If the driver has not been initialised, intialise it
		if ( ! isset(KohanaConfig::$drivers[$driver]))
			KohanaConfig::$drivers[$driver] = new KohanaConfig($driver);

		// Return the Kohana_Config driver requested
		return KohanaConfig::$drivers[$driver];
	}


	/**
	 * The driver for this object
	 *
	 * @var     Kohana_Config_Driver
	 */
	protected $driver;

	/**
	 * Kohana_Config constructor to load the supplied driver.
	 * Enforces the singleton pattern.
	 *
	 * @param   string       driver
	 * @access  protected
	 */
	protected function __construct($driver)
	{
		// Create the driver name
		$driver = 'KohanaConfig_'.ucfirst($driver).'_Driver';

		// Ensure the driver loads correctly
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', array($driver, get_class($this)));

		// Load the new driver
		$this->driver = new $driver;

		// Ensure the new driver is valid
		if ( ! $this->driver instanceof KohanaConfig_Driver)
			throw new Kohana_Exception('core.driver_implements', array($driver, get_class($this), 'KohanaConfig_Driver'));

		// Log the event
		Kohana_Log::add('debug', 'Kohana_Config initialized with '.$driver);
	}

	/**
	 * Gets a value from the configuration driver
	 *
	 * @param   string       key 
	 * @param   bool         slash 
	 * @param   bool         required 
	 * @return  mixed
	 * @access  public
	 */
	public function get($key, $slash = FALSE, $required = FALSE)
	{
		return $this->driver->get($key, $slash, $required);
	}

	/**
	 * Sets a value to the configuration driver
	 *
	 * @param   string       key 
	 * @param   mixed        value 
	 * @return  bool
	 * @access  public
	 */
	public function set($key, $value)
	{
		return $this->driver->set($key, $value);
	}

	/**
	 * Clears a group from configuration
	 *
	 * @param   string       group 
	 * @return  bool
	 * @access  public
	 */
	public function clear($group)
	{
		return $this->driver->clear($group);
	}

	/**
	 * Loads a configuration group
	 *
	 * @param   string       group 
	 * @param   bool         required 
	 * @return  array
	 * @access  public
	 */
	public function load($group, $required = FALSE)
	{
		return $this->driver->load($group, $required);
	}

	/**
	 * The following allows access using
	 * array syntax.
	 * 
	 * @example  $config['core.site_domain']
	 */

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key 
	 * @return  mixed
	 * @access  public
	 */
	public function offsetGet($key)
	{
		return $this->driver->get($key);
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key 
	 * @param   mixed        value 
	 * @return  bool
	 * @access  public
	 */
	public function offsetSet($key, $value)
	{
		return $this->driver->set($key, $value);
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key 
	 * @return  bool
	 * @access  public
	 */
	public function offsetExists($key)
	{
		return $this->driver->setting_exists($key);
	}

	/**
	 * Allows access to configuration settings
	 * using the ArrayAccess interface
	 *
	 * @param   string       key 
	 * @return  bool
	 * @access  public
	 */
	public function offsetUnset($key)
	{
		return $this->driver->set($key, NULL);
	}
} // End KohanaConfig