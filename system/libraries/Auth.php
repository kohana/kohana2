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

	protected $salt_pattern;

	public function __construct()
	{
		// Load libraries
		$this->db = new Database();
		$this->session = new Session();

		// Get the salt pattern
		$this->salt_pattern = array_map('trim', explode(',', Config::item('auth.salt_pattern')));

		$plain = 'breakfast';

		$pass = $this->hash_password($plain);
		$salt = $this->find_salt($pass);
		$test = $this->hash_password($plain, $salt);

		print 'Hashed: '.Kohana::debug($pass);
		print 'Rebuilt: '.Kohana::debug($test);
		print 'Matches: '.Kohana::debug($pass === $test);
		exit;

		Log::add('debug', 'Auth Library loaded');
	}

	/*
	 * Creates a hashed password from a plaintext password, inserting salt
	 * based on the configured salt pattern.
	 *
	 * Parameters:
	 *  password - plaintext password
	 *
	 * Returns:
	 *  Hashed password string
	 */
	protected function hash_password($password, $salt = FALSE)
	{
		if ($salt == FALSE)
		{
			// Create a salt string, same length as the number of offsets in the pattern
			$salt = substr(sha1(uniqid(NULL, TRUE)), 0, count($this->salt_pattern));
		}

		// Password hash that the salt will be inserted into
		$hash = sha1($salt.$password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach($this->salt_pattern as $offset)
		{
			// Split a new part of the hash off
			$part = substr($hash, 0, $offset - $last_offset);

			// Cut the current part out of the hash
			$hash = substr($hash, $offset - $last_offset);

			// Add the part to the password, appending the salt character
			$password .= $part.array_shift($salt);

			// Set the last offset to the current offset
			$last_offset = $offset;
		}

		// Return the password, with the remaining hash appended
		return $password.$hash;
	}

	/*
	 * Finds the salt from a password, based on the figured salt pattern.
	 *
	 * Parameters:
	 *  password - hashed password
	 *
	 * Returns:
	 *  Salt string
	 */
	protected function find_salt($password)
	{
		$salt = '';

		foreach($this->salt_pattern as $i => $offset)
		{
			// Find salt characters... take a good long look
			$salt .= substr($password, $offset + $i, 1);
		}

		return $salt;
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