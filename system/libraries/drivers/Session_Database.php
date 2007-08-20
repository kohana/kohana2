<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

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
	var $CORE;

	/**
	 * Constructor
	 */
	public function __construct($config)
	{
		foreach(((array) $config) as $key => $val)
		{
			$this->$key = $val;
		}

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

	// --------------------------------------------------------------------

	/**
	 * Open the session
	 * Session opens a dedicated database connection.
	 * This is done for 3 reasons:
	 * 1. A sessions database group MUST be configured.
	 * 2. To prevent data loss occurring with different db connections.
	 * 3. To keep the session db connection available in the shutdown handler.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function open()
	{
		$this->CORE = Kohana::$instance;
		// $this->name contains the configured 'session_name'. A db group
		// AND an actual database table must exist of the SAME name.
		$this->sdb = $this->CORE->load->database($this->name, TRUE);
		
		if (! $this->sdb->table_exists($this->name))
		{
			show_error(get_class($this).'::The configured session table name was not found');
		}
		
		if ($this->sdb)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Close the session
	 *
	 * @access	public
	 * @return	bool
	 */
	public function close()
	{
		// Garbage collect
		$this->gc();
		return $this->sdb->close();
	}

	// --------------------------------------------------------------------

	/**
	 * Read a session
	 *
	 * @access	public
	 * @param	string	session id
	 * @return	string
	 */
	public function read($id)
	{
		$sql = "SELECT data 
				FROM $this->name 
				WHERE session_id = ?";
		$query = $this->sdb->query($sql, array($id));
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->data;
		}
		else
		{
			return ''; // must return empty string on failure, not a boolean!
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write session data
	 *
	 * @access	public
	 * @param	string	session id
	 * @param	string	session data
	 * @return	bool
	 */
	public function write($id, $data)
	{
		$last_activity = time();
		$total_hits = 1;

		// Does session exist?
		$sql = "SELECT session_id, last_activity, total_hits, data 
				FROM $this->name 
				WHERE session_id = ?";
		$query = $this->sdb->query($sql, array($id));
		
		// Yes? Do update
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$total_hits += $row->total_hits;
			
			$db_data = array('last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
			$where = "session_id = '$id'";
			$sql = $this->sdb->update_string($this->name, $db_data, $where);

			$this->sdb->query($sql);
			// Did we succeed?
			if ($this->sdb->affected_rows())
			{
				return TRUE;
			}
		}
		else // No? Add the session
		{
			$db_data = array('session_id'=> $id, 'last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
			$sql = $this->sdb->insert_string($this->name, $db_data);

			$this->sdb->query($sql);
			// Did we succeed?
			if ($this->sdb->affected_rows())
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the session
	 *
	 * @access	public
	 * @return	bool
	 */
	public function destroy()
	{
		$id = session_id();

		$sql = "DELETE FROM $this->name 
				WHERE session_id = '$id'";
		$this->sdb->query($sql);
		// Did we succeed?
		if ($this->sdb->affected_rows())
		{
			return TRUE;
		}

		return FALSE;

	}

	// --------------------------------------------------------------------

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

		$sql = "SELECT total_hits, data 
				FROM $this->name 
				WHERE session_id = ?";
		$query = $this->sdb->query($sql, array($id));
		
		$row = $query->row();
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
		session_id(parent::regenerate());
		session_regenerate_id();
		
		// Add the new session to the db
		$id = session_id();

		$db_data = array('session_id'=> $id, 'last_activity' => $last_activity, 'total_hits' => $total_hits, 'data' => $data);
		$sql = $this->sdb->insert_string($this->name, $db_data);
 
		$this->sdb->query($sql);
	}
	
	// --------------------------------------------------------------------
	
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
		if (parent::gc())
		{
			$lifetime = ini_get('session.gc_maxlifetime');
			$expiry = ($lifetime > 0) ? (time() - $lifetime) : (time() - 1440);

			$sql = "DELETE FROM $this->name WHERE last_activity < $expiry";
			$this->sdb->query($sql);
		
			return $this->sdb->affected_rows();
			return 0;
		}
		
		return 0;
		
	}

} // End Session Database Driver