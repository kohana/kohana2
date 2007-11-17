<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends Model {

	// Database instance
	protected $db;

	// Configuration
	protected $config;

	// User data
	protected $user;

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
	 * Load user information
	 */
	protected function load($id)
	{
		$this->db
			->select('*')
			->from($this->config['user_table'])
			->limit(1);

		if ($id != FALSE)
		{
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
					$this->user->$key = NULL;
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
					->select("$role_table.id, $role_table.name, $role_table.description")
					->from($join_table)
					->join($role_table, "$join_table.role_id = $role_table.id")
					->where("$join_table.user_id", $id)
					->get();

				if (count($result) > 0)
				{
					foreach($result as $role)
					{
						// Append the role information to 
						$this->roles[$role->id] = $role->name;
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/*
	 * Magic __set function.
	 */
	public function __set($key, $value = NULL)
	{
		if (isset($this->user->$key))
		{
			$this->user->$key = $value;
		}
	}

	/*
	 * Magic __get function.
	 */
	public function __get($key)
	{
		if ($key === 'data_array')
		{
			return (array) $this->user;
		}
		elseif (isset($this->user->$key))
		{
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
			// Use array_flip to change the roles: name => id
			return array_key_exists($role, array_flip($this->roles));
		}
	}

} // End User