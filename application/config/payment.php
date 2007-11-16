<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Settings related to the Payment library.
 *
 * Options:
 *  driver - default driver to use
 *  test_mode - Turn TEST MODE on or off
 */
$config['default'] = array
(
	'driver'        => 'Paypal',
	'test_mode'     => TRUE
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
	'ReturnUrl' => 'http://www.kohanaphp.com/donate/paypal.html',
	'CANCELURL' => 'http://www.kohanaphp.com/donate/index.html',
	'CURRENCYCODE' => 'USD'
);