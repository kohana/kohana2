<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Encrypt
 *
 * Kohana Source Code:
 *  author    - Rick Ellis, Kohana Team
 *  copyright - Copyright (c) 2006, EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
 */
class Encrypt_Core {

	protected $encryption_key = '';
	protected $hash_type      = 'sha1';
	protected $mcrypt_exists  = FALSE;
	protected $mcrypt_cipher  = '';
	protected $mcrypt_mode    = '';

	/*
	 * Method: __construct
	 *  Simply determines whether the mcrypt library exists.
	 */
	public function __construct()
	{
		$this->mcrypt_exists = function_exists('mcrypt_encrypt');

		Log::add('debug', 'Encrypt Library initialized');
	}

	/*
	 * Method: get_key
	 *  Returns it as MD5 in order to have an exact-length 128 bit key.
	 *  mcrypt is sensitive to keys that are not the correct length
	 *
	 * Parameters:
	 *  key - the key
	 *
	 * Returns:
	 *  String containing the 128 bit key
	 */
	public function get_key($key = '')
	{
		if ($key == '')
		{
			if ($this->encryption_key != '')
				return $this->encryption_key;

			if (($key = Config::item('encryption.key')) === NULL)
				throw new Kohana_Exception('encrypt.no_encryption_key');
		}

		return md5($key);
	}

	/*
	 * Method: set_key
	 *  Set the encryption key
	 *
	 * Parameters:
	 *  key - the key
	 */
	public function set_key($key = '')
	{
		$this->encryption_key = $key;
	}

	/*
	 * Method: encode
	 *  Encodes the message string using bitwise XOR encoding.
	 *  The key is combined with a random hash, and then it
	 *  too gets converted using XOR. The whole thing is then run
	 *  through mcrypt (if supported) using the randomized key.
	 *  The end result is a double-encrypted message string
	 *  that is randomized with each call to this function,
	 *  even if the supplied message and key are the same.
	 *
	 * Parameters:
	 *  string - string to encode
	 *  key    - the key
	 *
	 * Returns:
	 *  The encoded string.
	 */
	public function encode($string, $key = '')
	{
		$key = $this->get_key($key);
		$enc = $this->xor_encode($string, $key);
		
		if ($this->mcrypt_exists === TRUE)
		{
			$enc = $this->mcrypt_encode($enc, $key);
		}

		return base64_encode($enc);
	}

	/*
	 * Method: decode
	 *  Reverses the encode process
	 *
	 * Parameters:
	 *  string - string to decode
	 *  key    - the key
	 *
	 * Returns:
	 *  The decoded data.
	 */
	public function decode($string, $key = '')
	{
		$key = $this->get_key($key);
		$dec = base64_decode($string);

		 if ($dec === FALSE)
		 	return FALSE;

		if ($this->mcrypt_exists === TRUE)
		{
			$dec = $this->mcrypt_decode($dec, $key);
		}

		return $this->xor_decode($dec, $key);
	}

	/*
	 * Method: xor_encode
	 *  Takes a plain-text string and key as input and generates an encoded bit-string using XOR
	 *
	 * Parameters:
	 *  string - string to encode
	 *  key    - the key
	 *
	 * Returns:
	 *  The encoded string.
	 */
	protected function xor_encode($string, $key)
	{
		$rand = '';

		while (strlen($rand) < 32)
		{
			$rand .= mt_rand(0, mt_getrandmax());
		}

		$rand = $this->hash($rand);

		$enc = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return $this->xor_merge($enc, $key);
	}

	/*
	 * Method: xor_decode
	 *  Takes an encoded string and key as input and generates the plain-text original message
	 *
	 * Parameters:
	 *  string - string to decode
	 *  key    - the key
	 *
	 * Returns:
	 *  The decoded data.
	 */
	protected function xor_decode($string, $key)
	{
		$string = $this->xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	/*
	 * Method: xor_merge
	 *  Takes a string and key as input and computes the difference using XOR
	 *
	 * Parameters:
	 *  string - the string
	 *  key    - the key
	 *
	 * Returns:
	 *  The combined string and key.
	 */
	protected function xor_merge($string, $key)
	{
		$hash = $this->hash($key);
		$str = '';

		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}

	/*
	 * Method: mcrypt_encode
	 *  Encrypt using mcrypt
	 *
	 * Parameters:
	 *  data - the data
	 *  key  - the key
	 *
	 * Returns:
	 *  The encoded string.
	 */
	public function mcrypt_encode($data, $key)
	{
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

		return mcrypt_encrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect);
	}

	/*
	 * Method: mcrypt_decode
	 *  Decrypt using mcrypt
	 *
	 * Parameters:
	 *  data - the data
	 *  key  - the key
	 *
	 * Returns:
	 *  The decoded data.
	 */
	public function mcrypt_decode($data, $key)
	{
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

		return rtrim(mcrypt_decrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect), "\0");
	}

	/*
	 * Method: set_cipher
	 *  Set the mcrypt Cipher
	 *
	 * Parameters:
	 *  cipher - the cipher
	 */
	public function set_cipher($cipher)
	{
		$this->mcrypt_cipher = $cipher;
	}

	/*
	 * Method: set_mode
	 *  Set the mcrypt Mode
	 *
	 * Parameters:
	 *  mode - the mode
	 */
	public function set_mode($mode)
	{
		$this->mcrypt_mode = $mode;
	}

	/*
	 * Method: get_cipher
	 *  Get mcrypt cipher Value
	 *
	 * Returns:
	 *  The mcrypt cipher
	 */
	public function get_cipher()
	{
		if ($this->mcrypt_cipher == '')
		{
			$this->mcrypt_cipher = MCRYPT_RIJNDAEL_256;
		}

		return $this->mcrypt_cipher;
	}

	/*
	 * Method: get_mode
	 *  Get mcrypt Mode Value
	 *
	 * Returns:
	 *  The mcrypt mode.
	 */
	public function get_mode()
	{
		if ($this->mcrypt_mode == '')
		{
			$this->mcrypt_mode = MCRYPT_MODE_ECB;
		}

		return $this->mcrypt_mode;
	}

	/*
	 * Method: set_hash
	 *  Set the Hash type
	 *
	 * Parameters:
	 *  type - the type
	 */
	public function set_hash($type = 'sha1')
	{
		$this->hash_type = ($type == 'md5') ? 'md5' : 'sha1';
	}

	/*
	 * Method: hash
	 *  Hash encode a string
	 *
	 * Parameters:
	 *  str - the string to encode
	 *
	 * Returns:
	 *  The encoded string.
	 */
	public function hash($str)
	{
		return ($this->hash_type == 'sha1') ? sha1($str) : md5($str);
	}

} // End Encrypt class