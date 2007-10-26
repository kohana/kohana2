<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Credit Card
 *  Settings related to the CreditCard library.
 *
 * Options:
 *  driver - default driver to use
 */
$config['default'] = array
(
	'driver'        => 'Authorize',
	'test_mode'     => TRUE
);

$config['Authorize'] = array
(
	'auth_net_login_id' => 'blah',
	'auth_net_tran_key' => 'blah'
);