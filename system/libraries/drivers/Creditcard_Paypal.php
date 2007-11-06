<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Creditcard_Paypal_Driver
 *  Provides payment processing with Payal
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 *
 * $Id: Creditcard_Paypal.php 975 2007-11-04 13:18:16Z Geert $
 */
class Creditcard_Paypal_Driver
{
	private $paypal_values = array('API_UserName' => 'kendoubek_api1.aol.com',
	                               'API_Password' => 'BFFUG3EB9S5HC7D2',
	                               'API_Signature' => '',
	                               'API_Endpoint' => 'https://api-3t.paypal.com/nvp',
	                               'version' => '3.0',
	                               'Amt' => 0,
	                               'PAYMENTACTION' => 'Sale',
	                               'ReturnUrl' => '',
	                               'CANCELURL' => '',
	                               'CURRENCYCODE' => 'USD',
	                               'payerid' => '');
	
	function __construct()
	{
		
	}
	
	function process()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $paypal_values['API_Endpoint']);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
		//Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		if(USE_PROXY)
			curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT); 

		//NVPRequest for submitting to server
		$nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($paypal_values['version'])."&PWD=".urlencode($paypal_values['API_Password'])."&USER=".urlencode($paypal_values['API_UserName'])."&SIGNATURE=".urlencode($paypal_values['API_Signature'])."&Amt=".$paymentAmount."&PAYMENTACTION=".$paymentType."&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL ."&CURRENCYCODE=".$currencyCodeType;

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