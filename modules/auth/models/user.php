<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends Model {

	// Database instance
	protected $db;

	// Configuration
	protected $config;

	// User data
	protected $user;

	// Changed data keys
	protected $changed = array();

	// User roles
	protected $roles = array();

	/*
	 * Constructor
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct();

		// Load auth configuration
		$this->config = Config::item('auth');

		// Load the user info
		$this->load($id);
	}

	/*
	 * Magic __set function.
	 */
	public function __set($key, $value = NULL)
	{
		static $auth;

		if (isset($this->user->$key))
		{
			if ($key != 'id')
			{
				if ($key === 'password')
				{
					if ($auth === NULL)
					{
						// Load Auth, attempting to use the controller copy
						$auth = isset(Kohana::instance()->auth) ? Kohana::instance()->auth : new Auth();
					}

					// Use Auth to hash the password
					$value = $auth->hash_password($value);
				}

				if ($value !== $this->user->$key)
				{
					// Save the changed key
					$this->changed[$key] = TRUE;
				}

				// Set the new value
				$this->user->$key = $value;
			}
		}
	}

	/*
	 * Magic __get function.
	 */
	public function __get($key)
	{
		if ($key === 'data_array')
		{
			// Return the data as an array
			return (array) $this->user;
		}
		elseif ($key === 'roles')
		{
			return $this->roles;
		}
		elseif (isset($this->user->$key))
		{
			// Return the value of key
			return $this->user->$key;
		}
	}

	/*
	 * Check if a user has a specified role.
	 */
	public function has_role($role)
	{
		if (is_numeric($role))
		{
			return isset($this->roles[$role]);
		}
		else
		{
			// Use in_array to search for the value
			return in_array($role, $this->roles);
		}
	}

	public function add_role($role)
	{
		if ($this->has_role($role))
			return TRUE;

		if ( ! ctype_digit($role))
		{
			// Phew, that's a big chain!
			$role = $this->db
				->select('id')
				->from($this->config['role_table'])
				->where('name', $role)
				->limit(1)
				->get()
				->current()
				->id;
		}

		try
		{
			$result = $this->db->set(array
			(
				'user_id' => $this->user->id,
				'role_id' => $role
			))
			->insert($this->config['user_table'].'_'.$this->config['role_table']);
		}
		catch (Kohana_Database_Exception $e)
		{
			// Database error
			return FALSE;
		}

		return (bool) count($result);
	}

	/*
	 * Save the user information to the database.
	 */
	public function save()
	{
		// Nothing needs to be saved
		if (empty($this->changed))
			return TRUE;

		$data = array();
		foreach($this->changed as $key => $val)
		{
			// Get changed data
			$data[$key] = $this->user->$key;
		}

		try
		{
			if ($this->user->id == 0)
			{
				// Insert the new user data
				$result = $this->db
					->set($data)
					->insert($this->config['user_table']);

				if (count($result) == 1)
				{
					// Set the user id from the insert id
					$this->user->id = $result->insert_id();
				}
			}
			else
			{
				// Update the current user data
				$result = $this->db
					->set($data)
					->where('id', $this->user->id)
					->update($this->config['user_table']);
			}
		}
		catch (Kohana_Database_Exception $e)
		{
			// Database error!
			return FALSE;
		}

		if (count($result) > 0)
		{
			// Reset changed data
			$this->changed = array();

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Load user information
	 */
	protected function load($id)
	{
		if (empty($id))
		{
			// Set empty fields
			$this->user = new StdClass();
			foreach($this->db->list_fields($this->config['user_table']) as $field)
			{
				$this->user->$field = '';
			}
			return TRUE;
		}

		$this->db
			->select('*')
			->from($this->config['user_table'])
			->limit(1);

		if ( ! ctype_digit($id))
		{
			// Id can be either an email address or a username
			$this->db->where(valid::email($id) ? 'email' : 'username', $id);
		}
		else
		{
			// Valid numeric id
			$this->db->where('id', $id);
		}

		if (count($result = $this->db->get()) === 1)
		{
			// Fetch the first result
			$this->user = $result->current();

			// Free the result
			unset($result);

			if ($id == FALSE)
			{
				foreach (get_object_vars($this->user) as $key => $val)
				{
					// Empty the data
					$this->user->$key = '';
				}
			}
			else
			{
				// Table names
				$user_table = $this->config['user_table'];
				$role_table = $this->config['role_table'];
				$join_table = $user_table.'_'.$role_table;

				// Fetch the user's roles, using a join
				$result = $this->db
					->select("$role_table.id, $role_table.name")
					->from($join_table)
					->join($role_table, "$join_table.role_id = $role_table.id")
					->where("$join_table.user_id", $this->user->id)
					->get();

				if (count($result) > 0)
				{
					foreach($result as $role)
					{
						// Add role to user's roles
						$this->roles[$role->id] = $role->name;
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

} // End User_Model