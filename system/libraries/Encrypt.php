<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the MCrypt extension.
 * @see http://php.net/mcrypt
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Encrypt_Core {

	// mcrypt rand type
	protected static $rand;

	/**
	 * Loads encryption configuration and validates the data.
	 *
	 * @throws Kohana_Exception
	 */
	public function __construct($config = array())
	{
		if ( ! defined('MCRYPT_ENCRYPT'))
			throw new Kohana_Exception('encrypt.requires_mcrypt');

		// Append the default configuration options
		$config += Config::item('encryption');

		if (empty($config['key']))
			throw new Kohana_Exception('encrypt.no_encryption_key');

		// Find the max length of the key
		$size = mcrypt_get_key_size($config['cipher'], $config['mode']);

		if (strlen($config['key']) > $size)
		{
			// Shorten the key so that it can be used by the mcrypt module
			$config['key'] = substr($config['key'], 0, $size);
		}

		// Cache the config in the object
		$this->config = $config;

		Log::add('debug', 'Encrypt Library initialized');
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 * @param   string  data to be encrypted
	 * @return  string  encrypted data
	 */
	public function encode($data)
	{
		// Set the rand type if it has not already been set
		if (self::$rand === NULL)
		{
			if (defined('MCRYPT_DEV_URANDOM'))
			{
				// Use /dev/urandom
				self::$rand = MCRYPT_DEV_URANDOM;
			}
			elseif (defined('MCRYPT_DEV_RANDOM'))
			{
				// Use /dev/random
				self::$rand = MCRYPT_DEV_RANDOM;
			}
			else
			{
				// Use the system random number generator
				self::$rand = MCRYPT_RAND;
			}
		}

		if (self::$rand === MCRYPT_RAND)
		{
			// The system random number generator must always be seeded each
			// time it is used, or it will not produce true random results
			mt_srand();
		}

		// Create a random initialization vector of the proper size for the current cipher
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($this->config['cipher'], $this->config['mode']), self::$rand);

		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.'>>>iv>>>'.$data);
	}

	/**
	 * Decrypts an encoded string back to it's original value.
	 *
	 * @param   string  encoded string to be decrypted
	 * @return  string  decrypted data
	 */
	public function decode($data)
	{
		// Split the string back into initialization vector and data
		list($iv, $data) = explode('>>>iv>>>', base64_decode($data), 2);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv), "\0");
	}

} // End Encrypt