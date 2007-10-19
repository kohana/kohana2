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

	private $authnet_values = array
	(
		'x_login'			=> '',
		'x_version'			=> '3.1',
		'x_delim_char'		=> '|',
		'x_delim_data'		=> 'TRUE',
		'x_url'				=> 'FALSE',
		'x_type'			=> 'AUTH_CAPTURE',
		'x_method'			=> 'CC',
		'x_tran_key'		=> '',
		'x_relay_response'	=> 'FALSE',
		'x_card_num'		=> '',
		'x_exp_date'		=> '',
		'x_description'		=> '',
		'x_amount'			=> '',
		'x_first_name'		=> '',
		'x_last_name'		=> '',
		'x_company'			=> '',
		'x_address'			=> '',
		'x_city'			=> '',
		'x_state'			=> '',
		'x_zip' 			=> '',
		'x_email'			=> '',
		'x_phone'			=> '',
		'x_fax'				=> '',
		'x_cust_id'			=> '',
		'x_ship_to_first_name'  => '',
		'x_ship_to_last_name'   => '',
		'x_ship_to_company'	=> '',
		'x_ship_to_address'	=> '',
		'x_ship_to_city'	=> '',
		'x_ship_to_state'	=> '',
		'x_ship_to_zip'		=> '',
		'x_tax'				=> '',
		'x_freight'			=> '',
		'x_comments' 		=> '',
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
			$this->authnet_values['x_'.$key] = $value;
		}
	}
	
	function process()
	{
		$fields = "";
		foreach( $authnet_values as $key => $value )
		{
			$fields .= $key.'='.urlencode($value).'&';
		}
			
		
		$post_url = ($this->config['test_mode']) ? 'https://certification.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll'; 
		
		$ch = curl_init($post_url); 
		foreach ($this->curl_config as $key => $value)
		{
			curl_setopt($ch, $key, $value);
		}
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