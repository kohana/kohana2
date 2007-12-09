<?php defined('SYSPATH') or die('No direct script access.');

class User_Edit_Model extends User_Model {

	// Overload the class
	protected $class = 'user';

	// Forge instance
	protected $form;

	public function __construct($action, $title, $id = FALSE)
	{
		// Load the user
		parent::__construct($id);

		// Create the form
		$this->form = new Forge($action, $title);

		foreach(self::$fields[$this->table] as $field => $meta)
		{
			// User id and login data is not editable
			if (substr($field, -2) == 'id' or $field == 'logins')
				continue;

			if ($field == 'password')
			{
				// Add password and confirm password fields
				$this->form->password($field)->label(TRUE)->rules('length[5,32]');
				$this->form->password('confirm')->label(TRUE)->matches($this->form->password);

				if ($this->object->id == 0)
				{
					// Password fields are required for new users
					$this->form->password->rules('+required');
				}
			}
			else
			{
				// All fields are required by default
				$rules = 'required';

				if (isset($meta['length']))
				{
					$rules .= '|length['.(empty($meta['exact']) ? '1,' : '').$meta['length'].']';
				}

				if ($field == 'email')
				{
					$rules .= '|valid_email';
				}

				// Add an input for this field with the value of the field
				$this->form->input($field)->label(TRUE)->rules($rules)->value($this->object->$field);
			}
		}

		// Find all roles
		$roles = new Role_Model();
		$roles = $roles->find(ALL);

		$options = array();
		foreach($roles as $role)
		{
			// Add each role to the options
			$options[$role->name] = isset($this->roles[$role->id]);
		}

		// Create a checklist of roles
		$this->form->checklist('roles')->options($options)->label(TRUE);

		// Add the save button
		$this->form->submit('Save');
	}

	public function save()
	{
		if ($this->form->validate() AND $data = $this->form->as_array())
		{
			if (empty($data['password']))
			{
				// Remove the empty password so it's not reset
				unset($data['password'], $data['confirm']);
			}

			// Need to set this before saving
			$new_user = ($this->object->id == 0);

			// Remove the roles from data
			$roles = arr::remove('roles', $data);

			foreach($data as $field => $val)
			{
				// Set object data from the form
				$this->$field = $val;
			}

			if ($status = parent::save())
			{
				if ($new_user)
				{
					foreach($roles as $role)
					{
						// Add the user roles
						$this->add_role($role);
					}
				}
				else
				{
					foreach(array_diff($this->roles, $roles) as $role)
					{
						// Remove roles that were deactivated
						$this->remove_role($role);
					}

					foreach(array_diff($roles, $this->roles) as $role)
					{
						// Add new roles
						$this->add_role($role);
					}
				}
			}

			// Return the save status
			return $status;
		}

		return FALSE;
	}

	public function html()
	{
		// Proxy to form html
		return $this->form->html();
	}

	public function __toString()
	{
		// Proxy to form html
		return $this->form->html();
	}

}