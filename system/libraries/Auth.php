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

	// Database instance
	protected $db;

	// Session instance
	protected $session;

	// Configuration
	protected $config;

	// User roles
	protected $roles;

	public function __construct($config = NULL)
	{
		// Load libraries
		$this->db = new Database();
		$this->session = new Session();

		if ($config === NULL)
		{
			// Fetch configuration
			$this->config = Config::item('auth');

			// Clean up the salt pattern
			$this->config['salt_pattern'] = array_map('trim', explode(',', Config::item('auth.salt_pattern')));
		}

		print Kohana::debug($this->salt_shaker('bigjohn')); exit;

		// Fetch roles
		$result = $this->db->select('id, name, description')->from($this->config['role_table'])->get();

		// Parse roles, converting to an associative array
		foreach ($result as $role)
		{
			$this->roles[$role->name] = $role;
		}

		Log::add('debug', 'Auth Library loaded');
	}

	/*
	 * Create a new user.
	 *
	 * Parameters:
	 *  username - unqiue username
	 *  password - plaintext password
	 *  level    - user level
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function create($username, $password, $roles = array('user'))
	{
		try
		{
			// Perform the insert query
			$result = $this->db->insert($this->config['user_table'], array
			(
				'username' => $username,
				'password' => $this->hash_password($password)
			));
		}
		catch (Kohana_Database_Exception $e)
		{
			// User already exists
			return FALSE;
		}

		if (count($result) === 1)
		{
			// Add the user to the defined roles
			return $this->add_role($result->insert_id(), $roles);
		}

		return FALSE;
	}

	/*
	 * Add a user to a role.
	 *
	 * Parameters:
	 *  user_id - user database id
	 *  roles   - array of role names
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function add_role($user_id, $roles)
	{
		if ( ! is_array($roles))
		{
			return $this->add_role($user_id, array($roles));
		}

		foreach($roles as $role)
		{
			if (isset($this->roles[$role]))
			{
				try
				{
					// Add the user to the role
					$result = $this->db->insert($this->config['user_table'].'_'.$this->config['role_table'], array
					(
						'user_id' => $user_id,
						'role_id' => $this->roles[$role]->id
					));
				}
				catch (Kohana_Database_Exception $e)
				{
					// Role already exists
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	public function has_role($user_id, $role)
	{
		$result = $this->db
			->select('role_id')
			->from($this->config['user_table'].'_'.$this->config['role_table'])
			->where('user_id', $user_id)
			->where('')
			->get();
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
			->select('id, password, level, logins')
			->from($this->config['user_table'])
			->where(array
			(
				'username' => $username,
				'level >=' => $level
			))
			->limit(1)
			->get();

		if (count($result) !== 1)
			return FALSE;

		// Get the first result
		$result = $result->current();

		// Find the salt from the stored password
		$salt = $this->find_salt($result->password);

		if ($result->password === $this->hash_password($password, $salt))
		{
			// Update the number of logins
			$this->db
				->set('logins', ($result->logins + 1))
				->where('id', (int) $result->id)
				->update($this->config['user_table']);

			// Store session data
			$this->session->set(array
			(
				'user_id'  => (int) $result->id,
				'username' => $username,
				'level'    => (int) $result->level
			));

			return TRUE;
		}

		return FALSE;
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

	/*
	 * Perform a hash, using the configured method.
	 *
	 * Parameters:
	 *  str - string to be hashed
	 *
	 * Returns:
	 *  Hashed string.
	 */
	protected function hash($str)
	{
		return hash($this->config['hash_method'], $str);
	}

	/*
	 * Finds the salt from a password, based on the configured salt pattern.
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

		foreach($this->config['salt_pattern'] as $i => $offset)
		{
			// Find salt characters... take a good long look..
			$salt .= substr($password, $offset + $i, 1);
		}

		return $salt;
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
			$salt = substr($this->hash(uniqid(NULL, TRUE)), 0, count($this->config['salt_pattern']));
		}

		// Password hash that the salt will be inserted into
		$hash = $this->hash($salt.$password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach($this->config['salt_pattern'] as $offset)
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

} // End Auth