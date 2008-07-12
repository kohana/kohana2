<?php defined('SYSPATH') or die('No direct script access.');

class Auth_User_Model extends ORM {

	// Relationships
	protected $has_many = array('user_tokens');
	protected $has_and_belongs_to_many = array('roles');

	// User roles
	protected $has_roles;

	public function __set($key, $value)
	{
		if ($key === 'password')
		{
			// Use Auth to hash the password
			$value = Auth::instance()->hash_password($value);
		}

		parent::__set($key, $value);
	}

	public function has($object, $id = NULL)
	{
		if ($object === 'role')
		{
			if ( ! $this->loaded)
				return FALSE;

			if ($this->has_roles === NULL)
			{
				$this->db->select('id', 'name');

				// Load the roles
				$this->has_roles = $this->roles->select_list('id', 'name');
			}

			if (is_string($id) AND ! ctype_digit($id))
			{
				return in_array($id, $this->has_roles);
			}
			else
			{
				return isset($this->has_roles[$id]);
			}
		}

		return parent::has($object, $id);
	}

	/**
	 * Tests if a username exists in the database.
	 *
	 * @param   mixed    id to check
	 * @return  boolean
	 */
	public function username_exists($id)
	{
		return (bool) $this->db
			->where($this->unique_key($id), $id)
			->count_records($this->table_name);
	}

	/**
	 * Allows a model to be loaded by username or email address.
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return valid::email($id) ? 'email' : 'username';
		}

		return parent::unique_key($id);
	}

	/**
	 * Resets roles when results are loaded.
	 */
	protected function load_result($array = FALSE)
	{
		$result = parent::load_result($array);

		if ($array === FALSE)
		{
			// Reset roles
			$this->has_roles = NULL;
		}

		return $result;
	}

} // End Auth User Model