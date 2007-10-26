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
class Creditcard_Core {
	
	// Configuration
	protected $config = array
	(
		// The driver string
		'driver'      => NULL,
		// Curl config, see http://us.php.net/manual/en/function.curl-setopt.php for details
		'curl_config' => array(   CURLOPT_HEADER => FALSE,
                                  CURLOPT_RETURNTRANSFER => TRUE,
                                  CURLOPT_SSL_VERIFYPEER => FALSE
                              ),
		// Test mode is set to true by default
		'test_mode'   => TRUE,
	);
	
	protected $driver = NULL;

	/*
	 * Method: __construct
	 *  Sets the credit card processing fields.
	 *  The driver will translate these into the specific format for the provider.
	 *  Standard fields are (Providers may have additional fields):
	 *
	 *  card_num
	 *  exp_date
	 *  description
	 *  amount
	 *  tax
	 *  shipping
	 *  first_name
	 *  last_name
	 *  company
	 *  address
	 *  city
	 *  state
	 *  zip
	 *  email
	 *  phone
	 *  fax
	 *  ship_to_first_name
	 *  ship_to_last_name
	 *  ship_to_company
	 *  ship_to_address
	 *  ship_to_city
	 *  ship_to_state
	 *  ship_to_zip
	 *
	 * Parameters:
	 *  config - the driver string
	 *
	*/
	public function __construct($config = array())
	{
		if (empty($config))
		{
			// Load the default group
			$config = Config::item('creditcard.default');
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
		$this->config = array_merge($this->config, Config::item('creditcard.'.$this->config['driver']));

		// Set driver name
		$driver = 'Creditcard_'.ucfirst($this->config['driver']).'_Driver';

		// Manually call auto-loading, for proper exception handling
		Kohana::auto_load($driver);

		// Initialize the driver
		$this->driver = new $driver($this->config);
	}

	/*
	 * Method: __set
	 *  Sets the credit card processing fields
	 *
	 * Parameters:
	 *  name - the field name
	 *  val  - the value
	 *
	*/
	public function __set($name, $val)
	{
		$this->driver->set_fields(array($name => $val));
	}

	/*
	 * Method: set_fields
	 *  Bulk setting of credit card processing fields
	 *
	 * Parameters:
	 *  fields - an array of values to set
	 *
	 * Returns:
	 *  <Creditcard> object
	*/
	public function set_fields($fields)
	{
		$this->driver->set_fields(array_merge($this->fields, (array) $fields));
		
		return $this;
	}

	/*
	 * Method: process
	 *  Runs the transaction and returns the status string on failure or TRUE on success
	 *
	 * Returns:
	 *  Mixed
	 */
	public function process()
	{
		$this->set_fields($this->fields);
		return $this->driver->process();
	}
}