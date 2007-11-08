<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Credit Card
 *  Settings related to the CreditCard library.
 *
 * Options:
 *  driver - default driver to use
 *  test_mode - Turn TEST MODE on or off
 */
$config['default'] = array
(
	'driver'        => 'Authorize',
	'test_mode'     => TRUE
);

/*
 * Authorize.net Options:
 *  auth_net_login_id - the transaction login ID; provided by gateway provider
 *  auth_net_tran_key - the transaction key; provided by gateway provider
 */
$config['Authorize'] = array
(
	'auth_net_login_id' => '',
	'auth_net_tran_key' => ''
);

/*
 * YourPay.net Options:
 *  certificate - the location on your server of the certificate file.
 */
$config['Yourpay'] = array
(
	'merchant_id' => '',
	'certificate' => './path/to/certificate.pem'
);

/*
 * PayPal Options:
 *  API_UserName - the username to use
 *  API_Password - the password to use
 *  API_Signature - the api signature to use
 *  ReturnUrl - the URL to sent the user to after they login with paypal
 *  CANCELURL - the URL to sent the user to if they cancel the paypal transaction
 *  CURRENCYCODE - the Currency Code to to the transactions in (What do you want to get paid in?)
 */
$config['Paypal'] = array
(
	'API_UserName' => '',
	'API_Password' => '',
	'API_Signature' => '',
	'ReturnUrl' => '',
	'CANCELURL' => '',
	'CURRENCYCODE' => 'USD'
);