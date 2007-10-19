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
 * $Id: Database_Mysql.php 829 2007-10-15 18:15:37Z zombor $
 */

/**
 * Credit Card Authorize.net Driver
 *
 * @category    Credit Card
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/database.html
 */
class Credit_Card_Authorize_Driver
{
	/**
	 * Fields required to do a transaction
	 *
	 * @var array
	 */
	private $required_fields = array(   'x_login' => FALSE,
										'x_version' => TRUE,
										'x_delim_char' => TRUE,
										'x_url' => TRUE,
										'x_type' => TRUE,
										'x_method' => TRUE,
										'x_tran_key' => FALSE,
										'x_relay_response' => TRUE,
										'x_card_num' => FALSE,
										'x_expiration_date' => FALSE,
										'x_amount' => FALSE,
										);
	/**
	 * Default required values
	 *
	 * @var array
	 */
	private $authnet_values = array
	(
		'x_version'			=> '3.1',
		'x_delim_char'		=> '|',
		'x_delim_data'		=> 'TRUE',
		'x_url'				=> 'FALSE',
		'x_type'			=> 'AUTH_CAPTURE',
		'x_method'			=> 'CC',
		'x_relay_response'	=> 'FALSE',
	);
	
	public function __construct($config)
	{
		$this->authnet_values['x_login'] = $config['auth_net_login_id'];
		$this->authnet_values['x_tran_key'] = $config['auth_net_tran_key'];

		$this->curl_config = $config['curl_config'];
		
		Log::add('debug', 'Authorize Credit Card Driver Initialized');
	}
	
	public function set_fields($fields)
	{
		foreach ($fields as $key => $value)
		{
			// Do variable translation
			switch($key)
			{
				
				default:
					break;
			}
			
			$this->authnet_values['x_'.$key] = $value;
			$this->required_fields['x_'.$key] = TRUE;
		}
	}
	
	function process()
	{
		// Check for required fields
		if (in_array(FALSE, $this->required_fields))
			return FALSE;
		
		$fields = "";
		foreach( $this->authnet_values as $key => $value )
		{
			$fields .= $key.'='.urlencode($value).'&';
		}
			
		
		$post_url = ($this->config['test_mode']) ? 'https://certification.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll'; 
		
		$ch = curl_init($post_url); 
		
		// Set custom curl options
		foreach ($this->curl_config as $key => $value)
		{
			curl_setopt($ch, $key, $value);
		}
		
		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " ));
		
		$resp = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		
		$h = substr_count($resp, "|");
		
		for($j=1; $j <= $h; $j++)
		{
			$p = strpos($resp, "|");
			
			if ($p !== FALSE)
			{
				$pstr = substr($text, 0, $p);
				
				$pstr_trimmed = substr($pstr, 0, -1); // removes "|" at the end
				
				if($pstr_trimmed=="")
				{
					$pstr_trimmed='NO VALUE RETURNED';
				}
				
				switch($j)
				{
					case 1:
						if($pstr_trimmed=='1') // Approved
							return TRUE;
						else
							return FALSE;
					default:
						return FALSE;
							
				}
			}
		}
	}
}

/**
 * 	A normal transaction array looks like this (for reference):
 * 
 * 	$authnet_values				= array
	(
		"x_login"				=> $auth_net_login_id,
		"x_version"				=> "3.1",
		"x_delim_char"			=> "|",
		"x_delim_data"			=> "TRUE",
		"x_url"					=> "FALSE",
		"x_type"				=> "AUTH_CAPTURE",
		"x_method"				=> "CC",
		"x_tran_key"			=> $auth_net_tran_key,
		"x_relay_response"		=> "FALSE",
		"x_card_num"			=> $this->input->post('cc_num'),
		"x_exp_date"			=> $this->input->post('cc_month') . $this->input->post('cc_year'),
		"x_description"			=> $order_contents,
		"x_amount"				=> round(($total_price + $tax + $shipping), 2),
		"x_first_name"			=> $this->input->post('first_name'),
		"x_last_name"			=> $this->input->post('last_name'),
		"x_company"				=> $this->input->post('company'),
		"x_address"				=> $this->input->post('address'),
		"x_city"				=> $this->input->post('city'),
		"x_state"				=> $this->input->post('state'),
		"x_zip"					=> $this->input->post('zip'),
		"x_email"				=> $this->input->post('email'),
		"x_phone"				=> $this->input->post('phone'),
		"x_fax"					=> "",
		"x_cust_id"				=> "",
						
		"x_ship_to_first_name"	=> $this->input->post('shipping_first_name'),
		"x_ship_to_last_name"	=> $this->input->post('shipping_last_name'),
		"x_ship_to_company"		=> $this->input->post('shipping_company'),
		"x_ship_to_address"		=> $this->input->post('shipping_address'),
		"x_ship_to_city"		=> $this->input->post('shipping_city'),
		"x_ship_to_state"		=> $this->input->post('shipping_state'),
		"x_ship_to_zip"			=> $this->input->post('shipping_zip'),
			
		"x_tax"					=> $tax,
		"x_freight"				=> $shipping,
		"x_comments"			=> "",
	);
 */