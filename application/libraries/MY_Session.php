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
 * Session Backwards Compatibility Class
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Sessions
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/sessions.html
 */
class Session extends Core_Session {

	/**
	 * Proxy get()
	 *
	 * @access	public
	 * @return	string
	 */
	function userdata($key)
	{
		return $this->get($key);
	}

	// ------------------------------------------------------------------------

	/**
	 * Proxy set()
	 *
	 * @access	public
	 * @return	string
	 */
	function set_userdata($key, $val = FALSE)
	{
		return $this->set($key, $val);
	}

	// ------------------------------------------------------------------------

	/**
	 * Proxy save()
	 *
	 * @access	public
	 * @return	string
	 */
	function sess_write()
	{
		return $this->save();
	}
	
	// ------------------------------------------------------------------------

	/**
	 * Proxy destroy()
	 *
	 * @access	public
	 * @return	string
	 */
	function sess_destroy()
	{
		$this->destory();
	}

}
// END Session Class
?>