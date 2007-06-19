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
 * SQLite Utility Class
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/database/
 */
class Core_DB_sqlite_utility extends Core_DB_utility {


	/**
	 * Create database
	 *
	 * @access	public
	 * @param	string	the database name
	 * @return	bool
	 */
	function _create_database()
	{
		// In SQLite, a database is created when you connect to the database.
		// We'll return TRUE so that an error isn't generated
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @access	private
	 * @param	string	the database name
	 * @return	bool
	 */
	function _drop_database($name)
	{
		if ( ! @file_exists($this->db->database) OR ! @unlink($this->db->database))
		{
			if ($this->db->db_debug)
			{
				return $this->db->display_error('db_unable_to_drop');
			}
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * I don't believe you can do a database listing with SQLite
	 * since each database is its own file.  I suppose we could
	 * try reading a directory looking for SQLite files, but
	 * that doesn't seem like a terribly good idea
	 *
	 * @access	private
	 * @return	bool
	 */
	function _list_databases()
	{
		if ($this->db_debug)
		{
			return $this->display_error('db_unsuported_feature');
		}
		return array();
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 *  Unsupported feature in SQLite
	 *
	 * @access	private
	 * @return	bool
	 */
	function _drop_table($table)
	{
		if ($this->db->db_debug)
		{
			return $this->db->display_error('db_unsuported_feature');
		}
		return array();
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize table query
	 *
	 * Is optimization even supported in SQLite?
	 *
	 * @access	private
	 * @param	string	the table name
	 * @return	object
	 */
	function _optimize_table($table)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Repair table query
	 *
	 * Are table repairs even supported in SQLite?
	 *
	 * @access	private
	 * @param	string	the table name
	 * @return	object
	 */
	function _repair_table($table)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * SQLite Export
	 *
	 * @access	private
	 * @param	array	Preferences
	 * @return	mixed
	 */
	function _backup($params = array())
	{
		// Currently unsupported
		return $this->db->display_error('db_unsuported_feature');
	}

}

?>