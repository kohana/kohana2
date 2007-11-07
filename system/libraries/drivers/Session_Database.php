<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Session_Database_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Session_Database_Driver implements Session_Driver {

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

	/*
	 * Method: open
	 *  Session opens a dedicated database connection.
	 *  This is done for 3 reasons:
	 *  1. A sessions database group MUST be configured.
	 *  2. To prevent data loss occurring with different db connections.
	 *  3. To keep the session db connection available in the shutdown handler.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function open($path, $name)
	{
		try
		{
			// Try connecting to the database using a database group, defined
			// by the 'session.storage' config item. This is optional, but preferred.
			$this->db = new Database($this->group_name);
		}
		catch (Kohana_Database_Exception $e)
		{
			// If there's no default group, we use the default database
			$this->db = new Database();
		}

		if ( ! $this->db->table_exists($this->group_name))
			throw new Kohana_Exception('session.no_table', $this->group_name);

		return is_object($this->db);
	}

	public function close()
	{
		// Garbage collect
		$this->gc();
	}

	public function read($id)
	{
		$query = $this->db->from($this->group_name)->where('session_id', $id)->get();

		if ($query->result()->num_rows() > 0)
			return $query->current()->data;

		// Return value must be string, NOT a boolean
		return '';
	}

	public function write($id, $session_string)
	{
		$data = array
		(
			'session_id'    => $id,
			'last_activity' => time(),
			'data'          => $session_string
		);

		// Fetch current session data
		$query = $this->db->select('session_id')->from($this->group_name)->where('session_id', $id)->get();

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

	public function destroy($id)
	{
		return (bool) $this->db->delete($this->group_name, array('session_id' => $id))->num_rows();
	}

	public function regenerate($new_id)
	{
	}

	/*
	 * Method: gc
	 *  Upon each call there is a 3% chance that this function will delete all
	 *  sessions older than session.expiration.
	 *
	 * Returns:
	 *  Number of deleted rows if gc run, TRUE if not
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