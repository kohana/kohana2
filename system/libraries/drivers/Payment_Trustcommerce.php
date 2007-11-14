<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Payment_Trustcommerce_Driver
 *  Provides payment processing with TrustCommerce.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Payment_Trustcommerce_Driver
{
	// Fields required to do a transaction
	private $required_fields = array('custid' => FALSE,
	                                 'password' => TRUE,
	                                 'action' => TRUE,
	                                 'media' => TRUE,
	                                 'cc' => TRUE,
	                                 'exp' => TRUE,
	                                 'amount' => FALSE
	                                 );

	private $tclink_library = './path/to/library';
	private $test_mode = TRUE;

	private $fields = array();

	public function __construct($config)
	{
		$this->test_mode = $config['test_mode'];

		if (!extension_loaded('tclink'))
		{
			if (!dl($this->tclink_library))
			{
				throw new Kohana_Exception('payment.no_dlib', $this->tclink_library);
			}
		}
		Log::add('debug', 'TrustCommerce Payment Driver Initialized');
	}

	public function set_fields($fields)
	{
		foreach ((array) $fields as $key => $value)
		{
			// Do variable translation
			switch($key)
			{
				case 'card_num':
					$key = 'cc';
					break;
				case 'exp_date':
					$key = 'exp';
					break;
				default:
					break;
			}

			$this->fields[$key] = $value;
			if (array_key_exists($key, $this->required_fields) and !empty($value))
			{
				$this->required_fields[$key] = TRUE;
			}
		}
	}

	function process()
	{
		// Check for required fields
		if (in_array(FALSE, $this->required_fields))
		{
			$fields = array();
			foreach ($this->required_fields as $key => $field)
			{
				if (!$field) $fields[] = $key;
			}
			throw new Kohana_Exception('payment.required', implode(', ', $fields));
		}

		$result = tclink_send($params);

		while (list($key, $val) = each($result))
		{
			if ($key == 'status')
				return ($val == 'success');
		}

		return FALSE;
	}
}