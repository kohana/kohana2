<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends ORM {

	// Relationships
	protected $has_many = array('user_tokens');
	protected $has_and_belongs_to_many = array('roles');

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
			// Load a role model
			$role = ORM::factory('role');

			// Load JOIN info
			$join_table = $role->join_table($this->table_name);
			$join_col1  = $role->foreign_key(NULL, $join_table);
			$join_col2  = $role->foreign_key(TRUE);

			return (bool) $this->db
				->join($role->table_name, $join_col1, $join_col2)
				->where($role->unique_key($id), $id)
				->where($this->foreign_key(NULL, $join_table), $this->object[$this->primary_key])
				->count_records($join_table);
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

} // End User_Model