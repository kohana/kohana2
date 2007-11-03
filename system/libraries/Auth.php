<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Auth
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Auth_Core {

	protected $db;
	protected $users_table = 'users';

	public function __construct()
	{
		// Load libraries
		$this->db = new Database();
		$this->session = new Session();

		Log::add('debug', 'Auth Library loaded');
	}

	/*
	 * Method: login
	 *  Attempt a user login.
	 *
	 * Parameters:
	 *  username - username to check
	 *  password - password to check
	 *  level    - minimum level
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function login($username, $password, $level = 1)
	{
		// Fetch user information
		$result = $this->db
			->select('id, level, logins')
			->from($this->users_table)
			->where(array
			(
				'username' => $username,
				'password' => sha1($password),
				'level >=' => (int) $level
			))
			->limit(1)
			->get();

		if (count($result) !== 1)
			return FALSE;

		// Get the first result
		$result = $result->offsetGet(0);
        
		// Update the number of logins
		$this->db
			->set('logins', ($result->logins + 1))
			->where('id', (int) $result->id)
			->update($this->users_table);
        
		// Store session data
		$this->session->set(array
		(
			'user_id'  => (int) $result->id,
			'username' => $username,
			'level'    => (int) $result->level
		));
        
		return TRUE;
	}

	/*
	 * Method: logout
	 *  Force a logout of a user.
	 *
	 * Parameters:
	 *  destroy - completely destroy the session
	 */
	public function logout($destroy = FALSE)
	{
		if ($destroy == TRUE)
		{
			$this->session->destroy();
		}
		else
		{
			$this->session->del('user_id', 'username', 'level');
		}
	}

} // End Auth