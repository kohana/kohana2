<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Team.
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/*
	CREATE TABLE `kohana_session` (
	`session_id` VARCHAR( 26 ) NOT NULL ,
	`last_activity` INT( 11 ) NOT NULL ,
	`total_hits` INT( 10 ) NOT NULL ,
	`data` TEXT NOT NULL ,
	PRIMARY KEY ( `session_id` )
	) ;
*/

/**
 * Session Database Driver
 *
 * @package     Kohana
 * @subpackage  Drivers
 * @category    Sessions
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/sessions.html
 */
class Session_Database implements Session_Driver {

	var $sdb;  // session db connection
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->expiration		= Config::item('session.expiration');
		$this->encryption		= Config::item('session.encryption');
		$this->name  			= Config::item('session.name');
		$this->gc_probability  	= Config::item('session.gc_probability');
		
		// Load necessary classes
		$this->input = new Input();
		if ($this->encryption == TRUE)
		{
			$this->encrypt = new Encryption();
		}

		// Set "no expiration" to two years
		if ($this->expiration == 0)
		{
			$this->expiration = 60*60*24*365*2;
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
		// $this->name contains the configured 'session_name'. A db group
		// AND an actual database table must exist of the SAME name.
		$this->sdb = new Database($this->name);
		
		if (! $this->sdb->table_exists($this->name))
		{
			throw new Kohana_Exception('session.no_table', $this->name);
		}
		
		return ($this->sdb) ? TRUE : FALSE;
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
		//return $this->sdb->close();
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
		$query = $this->sdb->from($this->name)->where('session_id', $id)->get();
		$query->result();
		
		if ($query->num_rows() > 0)
		{
			$row = $query->current();
			return $row->data;
		}

		return ''; // must return empty string on failure, not a boolean!
	}

	/**
	 * Write session data
	 *
	 * @access	public
	 * @param	string	session id
	 * @param	string	session data
	 * @return	boolean
	 */
	public function write($id, $data)
	{
		$last_activity = time();
		$total_hits = 1;

		// Does session exist?
		$query = $this->sdb->select('session_id, last_activity, total_hits, data')->from($this->name)->where('session_id', $id)->get();

		$query->result();
		
		// Yes? Do update
		if ($query->num_rows() > 0)
		{
			$row = $query->current();
			$total_hits += $row->total_hits;
			//echo $id;die;
			$db_data = array('last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
			
			$query = $this->sdb->update($this->name, $db_data, array('session_id' => $id));

			// Did we succeed?
			if ($query->num_rows())
				return TRUE;
		}
		else // No? Add the session
		{
			$db_data = array('session_id'=> $id, 'last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
			$query = $this->sdb->insert($this->name, $db_data);

			// Did we succeed?
			if ($query->num_rows() > 0)
				return TRUE;
		}

		return FALSE;
	}

	/**
	 * Destroy the session
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function destroy($id)
	{
		$id = session_id();

		$query = $this->sdb->delete($this->name, array('session_id' => $id));
		// Did we succeed?
		return (bool) ($query->num_rows() > 0);
	}

	/**
	 * Regenerate the session, keeping existing data
	 *
	 * @access	public
	 * @return	void
	 */
	public function regenerate()
	{
		// Get session data, using the old session id
		$id = session_id();

		$query = $this->sdb->select('total_hits, data')->from($this->name)->where('session_id', $id)->get();
		echo $this->sdb->last_query();
		$query->result();
		$row = $query->current();
		// Session exists? Then store the data
		if ($query->num_rows() > 0)
		{
			$total_hits = $row->total_hits;
			$data = $row->data;
		}
		else
		{
			$total_hits = 0;
			$data = '';
		}
		
		// Reset session control items
		$last_activity = time();
		
		// Regenerate the session
		// We use 13 characters of a hash of the user's IP address for
		// an id prefix to prevent collisions. This should be very safe.
		$sessid = sha1($this->input->ip_address());
		$_start = rand(0, strlen($sessid)-13);
		$sessid = substr($sessid, $_start, 13);
		$sessid = uniqid($sessid);

		// Set the new session id
		session_id($sessid);

		$db_data = array('session_id'=> $sessid, 'last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
		$sql = $this->sdb->insert($this->name, $db_data);

	}
	
	/**
	 * Collect garbage
	 *
	 * For the randomly challenged, the parent::gc() function will return
	 * true with a probability of 0.03 This means there is a three % chance
	 * of deleting sessions older than session.gc.maxlifetime for each gc().
	 *
	 * @access	public
	 * @return	int	Number of rows deleted
	 */
	public function gc()
	{
		if ((rand() % 100) < $this->gc_probability)
		{
			$lifetime = ini_get('session.gc_maxlifetime');
			$expiry = ($lifetime > 0) ? (time() - $lifetime) : (time() - 1440);

			$query = $this->sdb->delete($this->name, array('last_activity' => $expiry));
			return $query->num_rows();
		}
		
		return 0;
	}

} // End Session Database Driver