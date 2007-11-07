<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Creditcard_Paypal_Driver
 *  Provides payment processing with Paypal
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 *
 */
class Payment_Paypal_Driver
{
	private $required_fields = array('API_UserName' => FALSE,
	                                'API_Password'  => FALSE,
	                                'API_Signature' => FALSE,
	                                'API_Endpoint'  => TRUE,
	                                'version'       => TRUE,
	                                'Amt'           => FALSE,
	                                'PAYMENTACTION' => TRUE,
	                                'ReturnUrl'     => FALSE,
	                                'CANCELURL'     => FALSE,
	                                'CURRENCYCODE'  => TRUE,
	                                'payerid'       => FALSE);

	private $paypal_values = array('API_UserName'  => '',
	                               'API_Password'  => '',
	                               'API_Signature' => '',
	                               'API_Endpoint'  => 'https://api-3t.paypal.com/nvp',
	                               'version'       => '3.0',
	                               'Amt'           => 0,
	                               'PAYMENTACTION' => 'Sale',
	                               'ReturnUrl'     => '',
	                               'CANCELURL'     => '',
	                               'CURRENCYCODE'  => 'USD',
	                               'payerid'       => '');

	private $paypal_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';

	function __construct($config)
	{
		$this->paypal_values['API_UserName'] = $config['API_UserName'];
		$this->paypal_values['API_Password'] = $config['API_Password'];
		$this->paypal_values['API_Signature'] = $config['API_Signature'];
		$this->paypal_values['ReturnUrl'] = $config['ReturnUrl'];
		$this->paypal_values['CANCELURL'] = $config['CANCELURL'];
		$this->paypal_values['CURRENCYCODE'] = $config['CURRENCYCODE'];
		$this->required_fields['API_UserName'] = !empty($config['API_UserName']);
		$this->required_fields['API_Password'] = !empty($config['API_Password']);
		$this->required_fields['API_Signature'] = !empty($config['API_Signature']);
		$this->required_fields['ReturnUrl'] = !empty($config['ReturnUrl']);
		$this->required_fields['CANCELURL'] = !empty($config['CANCELURL']);
		$this->required_fields['CURRENCYCODE'] = !empty($config['CURRENCYCODE']);

		$this->curl_config = $config['curl_config'];

		$this->session = new Session();

		Log::add('debug', 'Authorize Payment Driver Initialized');
	}

	public function set_fields($fields)
	{
		foreach ((array) $fields as $key => $value)
		{
			// Do variable translation (none needed)
			/*switch($key)
			{
				default:
					break;
			}*/

			$this->paypal_values[$key] = $value;
			if (array_key_exists($key, $this->required_fields) and !empty($value) $this->required_fields[$key] = TRUE;
		}
	}

	function pre_process($method)
	{
		if (!$this->session->get('token'))
		{
			$this->process('SetExpressCheckout');
		}
		else //User has already authorized with paypal
		{
			$token = $this->session->get('token');
			header("Location: ".$this->paypalurl.$token);
		}
	}

	function process($method = 'DoExpressCheckoutPayment')
	{
		// Check for required fields
		if (in_array(FALSE, $this->required_fields))
			throw new Kohana_Exception('payment.required');

		$ch = curl_init($this->paypal_values['API_Endpoint']);

		// Set custom curl options
		curl_setopt_array($ch, $this->curl_config);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq="METHOD=".urlencode($method).
	        	"&VERSION=".urlencode($this->paypal_values['version']).
	        	"&PWD=".urlencode($this->paypal_values['API_Password']).
	        	"&USER=".urlencode($this->paypal_values['API_UserName']).
	        	"&SIGNATURE=".urlencode($this->paypal_values['API_Signature']).
	        	"&Amt=".$paymentAmount.
	        	"&PAYMENTACTION=".$paymentType.
	        	"&ReturnUrl=".$this->paypal_values['returnURL'].
	        	"&CANCELURL=".$this->paypal_values['cancelURL'] .
	        	"&CURRENCYCODE=".$this->paypal_values['currencyCodeType'];

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
		//getting response from server
		$response = curl_exec($ch);

		//convrting NVPResponse to an Associative Array
		$nvpResArray=deformatNVP($response);
		$nvpReqArray=deformatNVP($nvpreq);
		$_SESSION['nvpReqArray']=$nvpReqArray;

		if (curl_errno($ch))
		{
			// moving to display page to display curl errors
			echo "<pre>" . print_r($response, true) . "</pre>" . 'test'; die;
			$_SESSION['curl_error_no']=curl_errno($ch) ;
			$_SESSION['curl_error_msg']=curl_error($ch);
			$location = "APIError.php";
			header("Location: $location");
		}
		else
		{
			curl_close($ch);
		}

		return $nvpResArray;
	}
}