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
 * Session Database Driver
 *
 * @category    Session
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/session.html
 */
class Session_Database implements Session_Driver {

	/*
	CREATE TABLE `kohana_session` (
		`session_id` VARCHAR( 40 ) NOT NULL ,
		`last_activity` INT( 11 ) NOT NULL ,
		`total_hits` INT( 10 ) NOT NULL ,
		`data` TEXT NOT NULL ,
		PRIMARY KEY ( `session_id` )
	);
	*/

	// Database connection
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set config options
		$this->expiration = Config::item('session.expiration');
		$this->encryption = Config::item('session.encryption');
		$this->group_name = Config::item('session.storage');

		// Load Input
		$this->input = new Input();

		// Load Encryption
		if ($this->encryption == TRUE)
		{
			$this->encrypt = new Encryption();
		}

		// Set 'no expiration' to two years
		if ($this->expiration == 0)
		{
			$this->expiration = 60 * 60 * 24 * 365 * 2;
		}

		Log::add('debug', 'Session Database Driver Initialized');
	}

	/**
	 * Open the session
	 * Session opens a dedicated database connection.
	 * This is done for 3 reasons:
	 * 1. A sessions database group MUST be configured.
	 * 2. To prevent data loss occurring with different db connections.
	 * 3. To keep the session db connection available in the shutdown handler.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function open($path, $name)
	{
		try
		{
			// Try connecting to the database using a database group, defined
			// by the 'session.name' config item. This is optional, but preferred.
			$this->db = new Database($this->group_name);
		}
		catch (Kohana_Database_Exception $e)
		{
			// If there's no default group, we use the default database
			$this->db = new Database();
		}

		if ( ! $this->db->table_exists($this->group_name))
			throw new Kohana_Exception('session.no_table', $this->group_name);

		return ($this->db) ? TRUE : FALSE;
	}

	/**
	 * Close the session
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function close()
	{
		// Garbage collect
		$this->gc();
	}

	/**
	 * Read a session
	 *
	 * @access	public
	 * @param	string	session id
	 * @return	string
	 */
	public function read($id)
	{
		$query = $this->db->from($this->group_name)->where('session_id', $id)->get();

		if ($query->result()->num_rows() > 0)
			return $query->current()->data;

		// Return value must be string, NOT a boolean
		return '';
	}

	/**
	 * Write session data
	 *
	 * @access	public
	 * @param	string	session id
	 * @param	string	session data
	 * @return	boolean
	 */
	public function write($id, $session_string)
	{
		//ob_start();
		$data = array
		(
			'session_id'    => $id,
			'last_activity' => time(),
			'data'          => $session_string
		);

		// Fetch current session data
		$query = $this->db->select('session_id')->from($this->group_name)->where('session_id', $id)->get();
//echo $this->db->last_query().'<pre>'.print_r($query->result(), true).'</pre>';die;
		// Yes? Do update
		if ($query->result()->num_rows() > 0)
		{
			// Remove session ID from the update
			unset($data['session_id']);

			$query = $this->db->update($this->group_name, $data, array('session_id' => $id));
		}
		else // No? Add the session
		{
			$query = $this->db->insert($this->group_name, $data);
		}

		return (bool) $query->num_rows();
	}

	/**
	 * Destroy the session
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function destroy($id)
	{
		return (bool) $this->db->delete($this->group_name, array('session_id' => $id))->num_rows();
	}

	/**
	 * Regenerate the session, keeping existing data
	 *
	 * @access	public
	 * @return	void
	 */
	public function regenerate($new_id)
	{
	}

	/**
	 * Collect garbage
	 * 
	 * Upon each call there is a 3% chance that this function will delete all
	 * sessions older than session.gc.maxlifetime. If it does so, the number
	 * of deleted rows will be returned. Otherwise TRUE will be returned.
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function gc()
	{
		if (rand(1, 100) < 4)
		{
			$expiry = time() - $this->expiration;
			$result = (int) $this->db->delete($this->group_name, array('last_activity <' => $expiry))->num_rows();

			Log::add('debug', 'Session garbage was collected, '.$result.' row(s) deleted');

			return $result;
		}

		return TRUE;
	}

} // End Session Database Driver