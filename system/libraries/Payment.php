<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Payment_Core
 *  Provides payment support for credit cards and other providers like PayPal
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Payment_Core {

	// Configuration
	protected $config = array
	(
		// The driver string
		'driver'      => NULL,
		// Test mode is set to true by default
		'test_mode'   => TRUE,
	);

	protected $driver = NULL;

	/**
	 * Constructor: __construct
	 *  Sets the payment processing fields.
	 *  The driver will translate these into the specific format for the provider.
	 *  Standard fields are (Providers may have additional or different fields):
	 *
	 *  card_num
	 *  exp_date
	 *  cvv
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
	 */
	public function __construct($config = array())
	{
		if (empty($config))
		{
			// Load the default group
			$config = Config::item('payment.default');
		}
		elseif (is_string($config))
		{
			$this->config['driver'] = $config;
		}

		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);

		// Woah! We can't continue like this!
		if ($this->config['driver'] == NULL)
			throw new Kohana_Exception();

		// Get the driver specific settings
		$this->config = array_merge($this->config, Config::item('payment.'.$this->config['driver']));

		try
		{
			// Set driver name
			$driver = 'Payment_'.ucfirst($this->config['driver']).'_Driver';

			// Manually load so that exceptions can be caught
			require_once Kohana::find_file('libraries/drivers', substr($driver, 0, -7), TRUE);
		}
		catch (Kohana_Exception $e)
		{
			throw new Kohana_Exception('payment.driver_not_supported', $this->config['driver']);
		}

		// Initialize the driver
		$this->driver = new $driver($this->config);
	}

	/**
	 * Method: __set
	 *  Sets the credit card processing fields
	 *
	 * Parameters:
	 *  name - the field name
	 *  val  - the value
	 */
	public function __set($name, $val)
	{
		$this->driver->set_fields(array($name => $val));
	}

	/**
	 * Method: set_fields
	 *  Bulk setting of payment processing fields
	 *
	 * Parameters:
	 *  fields - an array of values to set
	 *
	 * Returns:
	 *  <Payment> object
	 */
	public function set_fields($fields)
	{
		$this->driver->set_fields((array) $fields);

		return $this;
	}

	/**
	 * Method: process
	 *  Runs the transaction
	 *
	 * Returns:
	 *  TRUE on successful payment, an error string on failure
	 */
	public function process()
	{
		return $this->driver->process();
	}
}