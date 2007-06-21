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
	 * Garbage collection, called by close()
	 *
	 * @access	public
	 * @param	integer	session life
	 * @return	bool
	 */
	function gc($max_life)
	{
		show_error(get_class($this).'::gc has not been defined');
	}

}

?>