<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
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
interface Session_Driver {

	/**
	 * Open a session
	 *
	 * @access  public
	 * @param   string  file path
	 * @param   string  session name
	 * @return  boolean
	 */
	public function open($path, $name);

	/**
	 * Close a session
	 *
	 * @access  public
	 * @return  void
	 */
	public function close();

	/**
	 * Read a session
	 *
	 * @access  public
	 * @param   string  id
	 * @return  string
	 */
	public function read($id);

	/**
	 * Write a session
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  boolean
	 */
	public function write($id, $data);

	/**
	 * Destroy a session
	 *
	 * @access  public
	 * @param   string
	 * @return  boolean
	 */
	public function destroy($id);

	/**
	 * Regenerate a session
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function regenerate();

	/**
	 * Garbage collection, called by close()
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function gc();

} // End Session Driver Class