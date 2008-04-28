<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends ORM {

	// Relationships
	protected $has_many = array('tokens');
	protected $has_and_belongs_to_many = array('roles');

	// User roles
	protected $roles = NULL;

	public function __get($key)
	{
		// Allow roles to be fetched as a simple array
		if ($key === 'roles')
		{
			// Force the roles to load if they are empty
			($this->roles === NULL) and $this->has_role('login');

			return $this->roles;
		}

		return parent::__get($key);
	}

	public function __set($key, $value)
	{
		if ($key === 'password')
		{
			// Use Auth to hash the password
			$value = Auth::instance()->hash_password($value);
		}

		parent::__set($key, $value);
	}

	/**
	 * Overloading the has_role method, for optimization.
	 */
	public function has_role($role)
	{
		// Don't mess with these calls, they are too complex
		if (is_object($role))
			return parent::has_role($role);

		if ($this->roles === NULL)
		{
			// Make the roles into an array. This serves a dual purpose
			// of preventing the roles from being re-queried unnecessarily
			// as well as optimizing has_role() calls.
			$this->roles = array();

			if ($this->id > 0)
			{
				foreach ($this->find_related_roles() as $r)
				{
					// Load all the user roles
					$this->roles[$r->id] = $r->name;
				}
			}
		}

		// Make sure the role name is a string
		$role = (string) $role;

		if (ctype_digit($role))
		{
			// Find by id
			return isset($this->roles[$role]);
		}
		else
		{
			// Find by name
			return in_array($role, $this->roles);
		}
	}

	/**
	 * Tests if a username exists in the database.
	 *
	 * @param   string   username to check
	 * @return  bool
	 */
	public function username_exists($name)
	{
		return (bool) self::$db->where('username', $name)->count_records('users');
	}

	/**
	 * Allows a model to be loaded by username or email address.
	 */
	protected function where_key($id = NULL)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return valid::email($id) ? 'email' : 'username';
		}

		return parent::where_key($id);
	}

} // End User_Model