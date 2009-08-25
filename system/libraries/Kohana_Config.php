<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides a driver-based interface for setting and getting
 * configuration options for the Kohana environment
 *
 * $Id: Kohana_Config.php 4490 2009-07-27 18:13:12Z samsoir $
 *
 * @package    KohanaConfig
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Config_Core implements ArrayAccess {

	/**
	 * The default Kohana_Config driver
	 * to use for system setup
	 *
	 * @var     string
	 * @static
	 */
	public static $default_driver = 'array';
	
	/**
	 * Kohana_Config instance
	 *
	 * @var     array
	 * @static
	 */
	protected static $instance;
	
	/**
	 * Returns a new instance of the Kohana_Config library
	 * based on the singleton pattern
	 *
	 * @param   string       driver
	 * @return  Kohana_Config
	 * @access  public
	 * @static
	 */
	public static function & instance()
	{
		// If the driver has not been initialised, intialise it
		if ( empty(Kohana_Config::$instance)) 
		{
			//call a 1 time non singleton of Kohana_Config to get a list of drivers
			$config = new Kohana_Config(array('config_drivers'=>array(),'internal_cache'=>FALSE));
			$core_config = $config->get('core');
			Kohana_Config::$instance = new Kohana_Config($core_config);
		}
		
		// Return the Kohana_Config driver requested
		return Kohana_Config::$instance;
	}
	
	/**
	 * The drivers for this object
	 *
	 * @var     Kohana_Config_Driver
	 */
	protected $drivers;
	
	/**
	 * Kohana_Config constructor to load the supplied driver.
	 * Enforces the singleton pattern.
	 *
	 * @param   string       driver
	 * @access  protected
	 */
	protected function __construct(array $core_config)
	{
		$drivers = $core_config['config_drivers'];
		
		//remove array if it's found in config
		if (in_array('array', $drivers))
			unset($drivers[array_search('array', $drivers)]);
			
		//add array at the very end
		$this->drivers = $drivers = array_merge($drivers, array('array'));
		
		foreach ($this->drivers as & $driver)
		{
			// Create the driver name
			$driver = 'Config_'.ucfirst($driver).'_Driver';
			
			// Ensure the driver loads correctly
			if (!Kohana::auto_load($driver))
				throw new Kohana_Exception('core.driver_not_found', array($driver, get_class($this)));
				
			// Load the new driver
			$driver = new $driver($core_config);
			
			// Ensure the new driver is valid
			if (!$driver instanceof Config_Driver)
				throw new Kohana_Exception('core.driver_implements', array($driver, get_class($this), 'Config_Driver'));
		}
		// Log the event
		Kohana_Log::add('debug', 'Kohana_Config initialized with drivers:'.implode(', ',$drivers));
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
		foreach ($this->drivers as $driver)
		{
			try
			{
				return $driver->get($key, $slash, $required);
			}
			catch (Kohana_Config_Exception $e)
			{
				//if it's the last driver in the list and it threw an exception, re throw it
				if ($driver === $this->drivers[(count($this->drivers)-1)])
					throw $e;
			}
		}
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
		foreach ($this->drivers as $driver)
		{
			try
			{
				$driver->set($key, $value);
			}
			catch (Kohana_Config_Exception $e)
			{
			
			}
		}
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

class Kohana_Config_Exception extends Kohana_Exception {}
