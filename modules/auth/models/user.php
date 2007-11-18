<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends ORM {

	// User roles
	protected $roles = array();

	// Relationships
	protected $has_and_belongs_to_many = array('roles');

	/*
	 * Constructor
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct($id);

		// Load auth configuration
		$this->config = Config::item('auth');

		foreach($this->find_related_roles() as $role)
		{
			// Load roles
			$this->roles[$role->id] = $role->name;
		}
	}

	/*
	 * Magic __set function.
	 */
	public function __set($key, $value = NULL)
	{
		static $auth;

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

		parent::__set($key, $value);
	}

	/*
	 * Magic __get function.
	 */
	public function __get($key)
	{
		return ($key === 'roles') ? $this->roles : parent::__get($key);
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

		if ( ! ctype_digit((string) $role))
		{
			// Find the role id
			$role = new Role_Model($role);
			$role = $role->id;
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

	/**
	 * Generate a WHERE array.
	 */
	protected function where($id)
	{
		// Primary key
		if (($where = parent::where($id)) !== NULL)
			return $where;

		// Email address
		if (valid::email($id))
			return array('email' => $id);

		// Username
		return array('username' => $id);
	}

} // End User_Model