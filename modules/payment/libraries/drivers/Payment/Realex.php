<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Realex Payment Driver
 *
 * $Id: Realex.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Payment
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Payment_Realex_Driver implements Payment_Driver
{
	private $config;
	
	// Fields required to do a transaction
	private $required_fields = array
	(
		'card_num' => FALSE,
		'card_exp' => FALSE,
		'card_cvn' => FALSE,
		'card_name' => FALSE,
		'card_type' => FALSE,
		'order_id' => FALSE,
		'amount' => FALSE,
		
	);

	// Default required values
	private $fields = array
	(
		'card_num' => '',
		'card_exp' => '',
		'card_cvn' => '',
		'card_name' => '',
		'card_type' => '',
		'order_id' => '',
		'amount' => '',
		
		'card_issue' => '',
		'account' => '',
		'comments' => array(),
	);

	private $test_mode = TRUE;

	/**
	 * Sets the config for the class.
	 *
	 * @param  array  config passed from the library
	 */
	public function __construct($config)
	{
		$this->config = $config;
		
		$this->curl_config = $config['curl_config'];
		$this->test_mode = $config['test_mode'];

		Kohana::log('debug', 'Realex Payment Driver Initialized');
	}

	public function set_fields($fields)
	{
		foreach ((array) $fields as $key => $value)
		{
			// Do variable translation
			switch ($key)
			{
				case 'exp_date':
				case 'expiration_date':
					$key = 'card_exp';
					break;
				default:
					break;
			}

			$this->fields[$key] = $value;
			if (array_key_exists($key, $this->required_fields) and !empty($value)) $this->required_fields[$key] = TRUE;
		}
	}

	public function process()
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
		
		$timestamp = date('YmdHis');
		
		$account = $this->test_mode ? 'internettest' : 'internet';
		 
		$xml = '<request timestamp="'.$timestamp.'" type="auth">
					<merchantid>'.$this->config['merchant_id'].'</merchantid>
					<account>'.$account.'</account>
					<orderid>'.$this->fields['order_id'].'</orderid>
					<amount currency="'.$this->config['currency'].'">'.$this->fields['amount'].'</amount>
					<card>
						<number>'.$this->fields['card_num'].'</number>
						<expdate>'.$this->fields['card_exp'].'</expdate>
						<chname>'.$this->fields['card_name'].'</chname>
						<type>'.$this->fields['card_type'].'</type>
						<issueno>'.$this->fields['card_issue'].'</issueno>
						<cvn>
							<number>'.$this->fields['card_cvn'].'</number>
							<presind>1</presind>
						</cvn>
					</card>
					<autosettle flag="'.$this->config['auto_settle'].'" />
					<comments>';
		
		foreach ($this->fields['comments'] as $id => $comment)
		{
			$xml .= '	<comment id="'.$id.'">'.$comment.'</comment>';
		}

		$xml .= '	</comments>
					<md5hash>'.md5(md5($timestamp.'.'.$this->config['merchant_id'].'.'.$this->fields['order_id'].'.'.$this->fields['amount'].'.'.$this->config['currency'].'.'.$this->fields['card_num']).'.'.$this->config['secret']).'</md5hash>
				</request>';
				
		$post_url = 'https://epage.payandshop.com/epage-remote.cgi';

		$ch = curl_init($post_url);

		// Set custom curl options
		curl_setopt_array($ch, $this->curl_config);

		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

		if ($result = curl_exec ($ch))
		{
			if (strlen($result) < 2) # no response
				throw new Kohana_Exception('payment.gateway_connection_error');

			// Convert the XML response to an array
			$response = simplexml_load_string($result);
			
			if ((string) $response->result == '00')
				return true;
			else
				return array((string) $response->result, (string) $response->message);
		}
		else
			throw new Kohana_Exception('payment.gateway_connection_error');
	}
} // End Payment_Realex_Driver Class