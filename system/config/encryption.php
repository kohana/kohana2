<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Encryption
 *  Settings for the Encryption Library
 *
 * Options:
 *  key  - Default encryption key. To provide a high level of security,
 *         make sure your key is at least 16 characters and contains
 *         letters, numbers, and symbols.
 */
$config = array
(
	'key'    => 'K0H@NA+PHP15Aw3s0ME',
	'mode'   => MCRYPT_MODE_ECB,
	'cipher' => MCRYPT_RIJNDAEL_256
);
