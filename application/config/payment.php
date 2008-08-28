<?php
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
	'test_mode'     => FALSE,
	'curl_config'   => array(CURLOPT_HEADER         => FALSE,
	                         CURLOPT_RETURNTRANSFER => TRUE,
	                         CURLOPT_SSL_VERIFYPEER => FALSE)
);

/*
 * PayPal Options:
 *  USER - the username to use
 *  PWD - the password to use
 *  SIGNATURE - the api signature to use
 *  RETURNURL - the URL to send the user to after they login with paypal
 *  CANCELURL - the URL to send the user to if they cancel the paypal transaction
 *  CURRENCYCODE - the Currency Code to to the transactions in (What do you want to get paid in?)
 */
$config['Paypal'] = array
(
	'USER' => '',
	'PWD' => '',
	'SIGNATURE' => '',
	'RETURNURL' => 'http://'.$_SERVER['SERVER_NAME'].'/donate/paypal.html',
	'CANCELURL' => 'http://'.$_SERVER['SERVER_NAME'].'/donate/index.html',
);