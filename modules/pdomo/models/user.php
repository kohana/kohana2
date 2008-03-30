<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends PDO_Model {

	protected $table = 'users';

	protected $types = array
	(
		'id' => 'integer',
		'email' => 'string',
		'password' => 'string',
		'logins' => 'integer',
	);

	protected function __validate()
	{
		// Load validation
		$data = Validation::factory($this->data);

		if (isset($this->changed['email']))
		{
			// Email is required and needs to be an email address
			$data->add_rules('email', 'required', 'length[4,64]', 'email');
		}

		if (isset($this->changed['password']))
		{
			// Password is required and needs to be hashed
			$data->add_rules('password', 'required', 'length[5,64]')->post_filter(array(Auth::instance(), 'hash_password'), 'password');
		}

		if ($data->validate())
		{
			// Load the validated/filtered data over the current data
			$this->data = $data->getArrayCopy();

			// Validation successful
			return TRUE;
		}
		else
		{
			// Retuen the error messages
			return $data->errors();
		}
	}

} // End User Model