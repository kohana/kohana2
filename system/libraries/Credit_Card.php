<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id: Database.php 832 2007-10-16 12:01:30Z Geert $
 */

/**
 * Credit Card Class
 *
 * @category    Libraries
 * @author      Jeremy Bush
 * @copyright   Copyright (c) 2007, Kohana Team
 * @license     http://www.codeigniter.com/user_guide/license.html
 * @link        http://kohanaphp.com/user_guide/en/general/credit_card.html
 */
class Credit_Card_Core {
	
	// Configuration
	protected $config = array
	(
		'driver'		=> NULL,
		'curl_settings'	=> array(	CURLOPT_HEADER => FALSE,
									CURLOPT_RETURNTRANSFER => TRUE,
									CURLOPT_SSL_VERIFYPEER => FALSE),
		'test_mode'		=> TRUE,
	);
	
	protected $driver = NULL;
	
	/*
	 * The standard processor fields...every CC processor should use these terms, 
	 * custom ones will be added by the programmer, 
	 * and the driver will make sure they are set.
	 * 
	 * if some are similar, but not exact (maybe card_num should be cc_num), the driver will translate those as well
	 */
	protected $fields = array(	'card_num' 			=> '',
								'exp_date'			=> '',
								'description'		=> '',
								'amount'			=> '',
								'tax'				=> '',
								'shipping'			=> '',
								'first_name'		=> '',
								'last_name'			=> '',
								'company'			=> '',
								'address'			=> '',
								'city'				=> '',
								'state'				=> '',
								'zip'				=> '',
								'email'				=> '',
								'phone'				=> '',
								'fax'				=> '',
								'ship_to_first_name'=> '',
								'ship_to_last_name'	=> '',
								'ship_to_company'	=> '',
								'ship_to_address'	=> '',
								'ship_to_city'		=> '',
								'ship_to_state'		=> '',
								'ship_to_zip'		=> '',
								);
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   mixed
	 * @return  void
	 */
	public function __construct($config = array())
	{
		if (empty($config))
		{
			// Load the default group
			$config = Config::item('credit_card.default');
		}
		else if (is_string($config))
		{
			$this->config['driver'] = $config;
		}

		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);
		
		// Woah! We can't continue like this!
		if ($this->config['driver'] == NULL)
			throw new Kohana_Exception();

		// Get the driver specific settings
		$this->config = array_merge($this->config, Config::item('credit_card.'.$this->config['driver']));

		// Set driver name
		$driver = 'Credit_Card_'.ucfirst($this->config['driver']).'_Driver';

		// Manually call auto-loading, for proper exception handling
		Kohana::auto_load($driver);

		// Initialize the driver
		$this->driver = new $driver($this->config);
	}
	
	/**
	 * Sets the CC processor variables 
	 *
	 * @param string $name
	 * @param mixed $val
	 */
	public function __set($name, $val)
	{
		$this->fields[$name] = $val;
	}
	
	/**
	 * Runs the transaction
	 *
	 * @return boolean
	 */
	public function process()
	{
		return $this->driver->process($this->fields);
	}
}