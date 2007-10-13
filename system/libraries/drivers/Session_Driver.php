<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Session API Driver
 *
 * @category    Session
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/session.html
 */
interface Session_Driver {

	/**
	 * Open a session
	 *
	 * @access  public
	 * @param   string  file path
	 * @param   string  session name
	 * @return  bool
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
	 * @return  bool
	 */
	public function write($id, $data);

	/**
	 * Destroy a session
	 *
	 * @access  public
	 * @param   string
	 * @return  bool
	 */
	public function destroy($id);

	/**
	 * Garbage collection, called by close()
	 *
	 * @access  public
	 * @return  bool
	 */
	public function gc();

} // End Session Driver Class