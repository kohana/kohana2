<?php

echo geshi_highlight('<?php 

class User_Model extends Model
{
	protected $id;
	protected $username;
	protected $password;
	protected $email;
	protected $address;
	protected $city;
	protected $state;
	protected $zip;
	protected $last_login;

	private $rules = array(\'id\' =>       array(\'name\' => \'User ID\',
	                                           \'rules\' => \'\',
	                                           \'valid\' => FALSE),
	                       \'username\' => array(\'name\' => \'Username\',
	                                           \'rules\' => \'required[3,25]|alpha_numeric\',
	                                           \'valid\' => FALSE),
	                       \'password\' => array(\'name\' => \'Password\',
	                                           \'rules\' => \'required[3,25]\',
	                                           \'valid\' => FALSE),
	                       \'email\' =>    array(\'name\' => \'Email\',
	                                           \'rules\' => \'valid_email|required\',
	                                           \'valid\' => FALSE),
	                       \'zip\' =>      array(\'name\' => \'ZIP Code\',
	                                           \'rules\' => \'numeric|required\',
	                                           \'valid\' => FALSE));

	public $validation;
	private $validated = FALSE;

	// Magic functions
	public function __construct()
	{
		$this->validation = new Validation();
	}

	public function __set($key, $val)
	{
		// Make sure this key and value is valid
		if (isset($this->$key))
		{
			$this->validation->set_rules(array($key => $value), $this->rules[$key][\'rules\'], $this->rules[$key][\'name\']);
			if (!$this->validation->run())
				return FALSE;

			// You win at life!
			$this->$key = $val;
			$this->rules[$key][\'valid\'] = TRUE;

			// See if the whole thing is validated
			foreach ($this->rules as $key => $rule)
			{
				// If anything isnt validated, just return success
				if ($rule[\'valid\'] == FALSE)
					return TRUE;
			}
			// Otherwise set validated and return
			$this->validated = TRUE;
			return TRUE;
		}

		return FALSE;
	}
	
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;

		return FALSE;
	}
	
	public function set_fields($input)
	{
		$data = array();
		$rules = array();
		$fields = array();
		$new_input = array();

		foreach ($this->rules as $key => $value)
		{
			//silently ignore invalid fields
			$data[$key] = @$input[$key];
			$rules[$key] = $this->rules[$key][\'rules\'];
			$fields[$key] = $this->rules[$key][\'name\'];
			
			if (isset($data[$key]) and isset($input[$key]))
				$new_input[$key] = $data[$key];
		}

		$this->validation->set_rules($data, $rules, $fields);

		if ($this->validation->run())
		{
			// Only set valid the keys that were inputed
			foreach ($new_input as $key => $value)
			{
				$this->$key = $value;
				$this->rules[$key][\'valid\'] = TRUE;
			}

			// Check to see if everything is validated
			foreach ($this->rules as $key => $rule)
			{
				// If anything isnt validated, just return success
				if ($rule[\'valid\'] == FALSE)
					return TRUE;
			}

			// Otherwise set validated and return
			$this->validated = TRUE;
			return TRUE;
		}

		return FALSE;
	}

	public function fetch($search_data)
	{
		$query = $this->db->from(\'users\')->where($search_data)->get();

		// Return the results
		if (count($query) > 1)
		{
			// Make the results copies of this object...w00t!
			$query->result(TRUE, __CLASSNAME__);
			return $query;
		}
		else if (count($query) == 1)// Assign the results to $this
		{
			$query = $query->current();
			$this->id = $query->id;
			$this->username = $query->username;
			$this->password = $query->password;
			$this->email = $query->email;
			$this->address = $query->address;
			$this->city = $query->city;
			$this->state = $query->state;
			$this->zip = $query->zip;
			$this->last_login = $query->last_login;

			return TRUE;
		}

		return FALSE;
	}

	public function save()
	{
		if (!$this->validated)
			return FALSE;

		// It\'s an insert
		if (is_null($this->id))
		{
			$insert_data = array(\'username\' => $this->username,
			                     \'password\' => $this->password,
			                     \'email\' => $this->email,
			                     \'address\' => $this->address,
			                     \'city\' => $this->city,
			                     \'state\' => $this->state,
			                     \'zip\' => $this->zip
			                     );

			$query = $this->db->insert(\'users\', $insert_data);

			return $query->insert_id();
		}
		else // It\'s an update
		{
			$update_data = array(\'username\' => $this->username,
			                     \'password\' => $this->password,
			                     \'email\' => $this->email,
			                     \'address\' => $this->address,
			                     \'city\' => $this->city,
			                     \'state\' => $this->state,
			                     \'zip\' => $this->zip
			                     );

			$query = $this->db->update(\'users\', $insert_data, array(\'id\' => $this->id));

			return count($query);
		}
	}
	
	public function delete()
	{
		if (is_null($this->id))
			return FALSE;

		$query = $this->db->delete(\'users\', array(\'id\' => $this->id));

		return count($query);
	}
}', 'php', NULL, TRUE);