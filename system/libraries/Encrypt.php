<?php  defined('SYSPATH') or die('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Encryption Class
 *
 * Provides two-way keyed encoding using XOR Hashing and mcrypt
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/encryption.html
 */
class Encrypt_Core {

	private $encryption_key = '';
	private $hash_type      = 'sha1';
	private $mcrypt_exists  = FALSE;
	private $mcrypt_cipher  = '';
	private $mcrypt_mode    = '';

	/**
	 * Constructor
	 *
	 * Simply determines whether the mcrypt library exists.
	 */
	public function __construct()
	{
		$this->mcrypt_exists = function_exists('mcrypt_encrypt');

		Log::add('debug', 'Encrypt Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the encryption key
	 *
	 * Returns it as MD5 in order to have an exact-length 128 bit key.
	 * mcrypt is sensitive to keys that are not the correct length
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public function get_key($key = '')
	{
		if ($key == '')
		{
			if ($this->encryption_key != '')
			{
				return $this->encryption_key;
			}

			if (($key = Config::item('encryption.key')) === FALSE)
			{
				/**
				 * @todo This should return an i18n error
				 */
				trigger_error('To use the Encrypt class, you need to set a encryption key in your config file.', E_USER_ERROR);
			}
		}

		return md5($key);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the encryption key
	 *
	 * @access  public
	 * @param   string
	 * @return  void
	 */
	public function set_key($key = '')
	{
		$this->encryption_key = $key;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode
	 *
	 * Encodes the message string using bitwise XOR encoding.
	 * The key is combined with a random hash, and then it
	 * too gets converted using XOR. The whole thing is then run
	 * through mcrypt (if supported) using the randomized key.
	 * The end result is a double-encrypted message string
	 * that is randomized with each call to this function,
	 * even if the supplied message and key are the same.
	 *
	 * @access  public
	 * @param   string  the string to encode
	 * @param   string  the key
	 * @return  string
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

	// --------------------------------------------------------------------

	/**
	 * Decode
	 *
	 * Reverses the above process
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  string
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

	// --------------------------------------------------------------------

	/**
	 * XOR Encode
	 *
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access  private
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	private function xor_encode($string, $key)
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

	// --------------------------------------------------------------------

	/**
	 * XOR Decode
	 *
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	private function xor_decode($string, $key)
	{
		$string = $this->xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	// --------------------------------------------------------------------

	/**
	 * XOR key + string Combiner
	 *
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access  private
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	private function xor_merge($string, $key)
	{
		$hash = $this->hash($key);
		$str = '';

		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt using mcrypt
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	public function mcrypt_encode($data, $key)
	{
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

		return mcrypt_encrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect);
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt using mcrypt
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	public function mcrypt_decode($data, $key)
	{
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

		return rtrim(mcrypt_decrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect), "\0");
	}

	// --------------------------------------------------------------------

	/**
	 * Set the mcrypt Cipher
	 *
	 * @access  public
	 * @param   constant
	 * @return  string
	 */
	public function set_cipher($cipher)
	{
		$this->mcrypt_cipher = $cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the mcrypt Mode
	 *
	 * @access  public
	 * @param   constant
	 * @return  string
	 */
	public function set_mode($mode)
	{
		$this->mcrypt_mode = $mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Get mcrypt cipher Value
	 *
	 * @access  public
	 * @return  string
	 */
	public function get_cipher()
	{
		if ($this->mcrypt_cipher == '')
		{
			$this->mcrypt_cipher = MCRYPT_RIJNDAEL_256;
		}

		return $this->mcrypt_cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Get mcrypt Mode Value
	 *
	 * @access  public
	 * @return  string
	 */
	public function get_mode()
	{
		if ($this->mcrypt_mode == '')
		{
			$this->mcrypt_mode = MCRYPT_MODE_ECB;
		}

		return $this->mcrypt_mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Hash type
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public function set_hash($type = 'sha1')
	{
		$this->hash_type = ($type == 'md5') ? 'md5' : 'sha1';
	}

	// --------------------------------------------------------------------

	/**
	 * Hash encode a string
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function hash($str)
	{
		return ($this->hash_type == 'sha1') ? sha1($str) : md5($str);
	}

} // End Encrypt class