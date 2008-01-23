<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Session_Database_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Session_Database_Driver implements Session_Driver {

	/*
	CREATE TABLE kohana_session
	(
		session_id VARCHAR(40) NOT NULL,
		last_activity INT(11) NOT NULL,
		data TEXT NOT NULL,
		PRIMARY KEY (session_id)
	);
	*/

	protected $db;
	protected $input;
	protected $encrypt;

	protected $db_group;
	protected $expiration;
	protected $new_session = TRUE;
	protected $old_id;

	public function __construct()
	{
		$this->db_group = Config::item('session.storage');
		$this->expiration = Config::item('session.expiration');

		$this->input = new Input;

		// Load Encrypt library
		if (Config::item('session.encryption'))
		{
			$this->encrypt = new Encrypt;
		}

		// Set 'no expiration' to two years
		if ($this->expiration == 0)
		{
			$this->expiration = 63072000;
		}

		Log::add('debug', 'Session Database Driver Initialized');
	}

	/**
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
		if (Config::item('database.'.$this->db_group) === NULL)
		{
			// There's no defined group, use the default database
			$this->db = new Database;
		}
		else
		{
			// Connect to the database using a database group, defined
			// by the 'session.storage' config item.
			$this->db = new Database($this->db_group);
		}

		return is_object($this->db);
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		$query = $this->db->from($this->db_group)->where('session_id', $id)->get()->result(TRUE);

		if ($query->count() > 0)
		{
			// No new session, this is used when writing the data
			$this->new_session = FALSE;
			return (Config::item('session.encryption')) ? $this->encrypt->decode($query->current()->data) : $query->current()->data;
		}

		// Return value must be string, NOT a boolean
		return '';
	}

	public function write($id, $data)
	{
		$session = array
		(
			'session_id' => $id,
			'last_activity' => time(),
			'data' => (Config::item('session.encryption')) ? $this->encrypt->encode($data) : $data
		);

		// Existing session, with regenerated session id
		if ( ! empty($this->old_id))
		{
			$query = $this->db->update($this->db_group, $session, array('session_id' => $this->old_id));
		}
		// New session
		elseif ($this->new_session)
		{
			$query = $this->db->insert($this->db_group, $session);
		}
		// Existing session, without regenerated session id
		else
		{
			// No need to update session_id
			unset($session['session_id']);

			$query = $this->db->update($this->db_group, $session, array('session_id' => $id));
		}

		return (bool) $query->count();
	}

	public function destroy($id)
	{
		return (bool) $this->db->delete($this->db_group, array('session_id' => $id))->count();
	}

	public function regenerate()
	{
		// It's wasteful to delete the old session and insert a whole new one so
		// we cache the old id to simply update the db with the new one
		$this->old_id = session_id();

		session_regenerate_id();

		// Return new session id
		return session_id();
	}

	/**
	 * Method: gc
	 *  Session garbage collection
	 *
	 * Returns:
	 *  TRUE
	 */
	public function gc()
	{
		$query = $this->db->delete($this->db_group, array('last_activity <' => time() - $this->expiration));

		Log::add('debug', 'Session garbage collected: '.$query->count().' row(s) deleted.');

		return TRUE;
	}

} // End Session Database Driver
