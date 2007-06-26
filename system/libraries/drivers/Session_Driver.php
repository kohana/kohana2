<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * Session API Driver
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Sessions
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/sessions.html
 */
class Session_Driver {

	/**
	 * Constructor
	 *
	 * Developers should always run this, parent::Session_Driver()
	 */
	function Session_Driver($config)
	{
		foreach(((array) $config) as $key => $val)
		{
			$this->$key = $val;
		}

		// Load necessary classes
		$this->input =& load_class('Input');
		if ($this->encryption == TRUE)
		{
			$this->encrypt =& load_class('Encrypt');
		}

		// Set "no expiration" to two years
		if ($this->expiration == 0)
		{
			$this->expiration = 60*60*24*365*2;
		}
	}

	/**
	 * Open a session
	 *
	 * @access	public
	 * @param	string	file path
	 * @param	string	session name
	 * @return	bool
	 */
	function open($path, $name)
	{
		show_error(get_class($this).'::open has not been defined');
	}

	// --------------------------------------------------------------------

	/**
	 * Close a session
	 *
	 * @access	public
	 * @return	void
	 */
	function close()
	{
		show_error(get_class($this).'::close has not been defined');
	}

	// --------------------------------------------------------------------

	/**
	 * Read a session
	 *
	 * @access	public
	 * @param	string	id
	 * @return	string
	 */
	function read($id)
	{
		show_error(get_class($this).'::read has not been defined');
	}

	// --------------------------------------------------------------------

	/**
	 * Write a session
	 *
	 * @access	public
	 * @param	string	id
	 * @param	string	data
	 * @return	bool
	 */
	function write($id, $data)
	{
		show_error(get_class($this).'::write has not been defined');
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy a session
	 *
	 * @access	public
	 * @param	string	id
	 * @return	bool
	 */
	function destroy($id)
	{
		show_error(get_class($this).'::destroy has not been defined');
	}

	// --------------------------------------------------------------------

	/**
	 * Regenerate a session
	 *
	 * @access	public
	 * @return	bool
	 */
	function regenerate()
	{
		if ( ! isset($this->name))
		{
			show_error(get_class($this).'::regenerate has not been defined');
		}

		// We use 13 characters of a hash of the user's IP address for
		// an id prefix to prevent collisions. This should be very safe.
		$sessid = sha1($this->input->ip_address());
		$_start = rand(0, strlen($sessid)-13);
		$sessid = substr($sessid, $_start, 13);
		return uniqid($sessid);
	}

	// --------------------------------------------------------------------

	/**
	 * Garbage collection, called by close()
	 *
	 * @access	public
	 * @return	bool
	 */
	function gc()
	{
		return ((rand(0, 100) % 50) === 0);
	}

}
// END Session Driver Class
?>