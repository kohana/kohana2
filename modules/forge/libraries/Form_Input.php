<?php defined('SYSPATH') or die('No direct script access.');

class Form_Input_Core {

	// Input instance
	protected static $input;

	// Element data
	protected $data = array
	(
		'type'  => 'text',
		'class' => 'textbox'
	);

	// Protected data keys
	protected $protect = array();

	// Validtion rules
	protected $rules = array();

	// Validation check
	protected $is_valid;

	// Errors
	protected $errors = array();

	public function __construct($name)
	{
		if (self::$input === NULL)
		{
			self::$input = new Input;
		}

		$this->data['name'] = $name;
	}

	public function __call($method, $args)
	{
		if ($method == 'rules')
		{
			$this->add_rules(explode('|', $args[0]));
		}
		elseif ($method == 'name')
		{
			// Yup, do nothing. The name should stay static once it is set.
		}
		else
		{
			$this->data[$method] = $args[0];
		}

		return $this;
	}

	public function __get($key)
	{
		if (isset($this->data[$key]))
		{
			return $this->data[$key];
		}
	}

	public function label($val = NULL)
	{
		if ($val === NULL)
		{
			return form::label($this->name, $this->label);
		}
		else
		{
			$this->label = ($val === TRUE) ? ucfirst($this->name) : $val;
			return $this;
		}
	}

	public function html()
	{
		// Make sure validation runs
		$this->is_valid;

		// Import locally to prevent tampering
		$data = $this->data;

		// Remove the label
		unset($data['label'], $data['options'], $data['default']);

		return form::input($data).$this->error_message();
	}

	protected function add_rules( array $rules)
	{
		foreach($rules as $rule)
		{
			if ( ! in_array($rule, $this->rules))
			{
				$this->rules[] = $rule;
			}
		}
	}

	protected function error_message()
	{
		// Make sure validation runs
		is_null($this->is_valid) and $this->validate();

		$message = '';
		foreach($this->errors as $error)
		{
			// Make the error into HTML
			$message .= '<p class="error">'.$error.'</p>';
		}
		return $message;
	}

	protected function load_value()
	{
		if ($value = self::$input->post($this->name))
		{
			$this->data['value'] = $value;
		}
	}

	public function validate()
	{
		// Validation has already run
		if (is_bool($this->is_valid))
			return $this->is_valid;

		// No data to validate
		if (empty($_POST))
			return $this->is_valid = FALSE;

		// Load the submitted value
		$this->load_value();

		// No rules to validate
		if (count($this->rules) == 0)
			return $this->is_valid = TRUE;

		if ( ! empty($this->rules))
		{
			foreach($this->rules as $rule)
			{
				if (($offset = strpos($rule, '[')) !== FALSE)
				{
					// Get the args
					$args = preg_split('/, ?/', trim(substr($rule, $offset), '[]'));

					// Remove the args from the rule
					$rule = substr($rule, 0, $offset);
				}

				if ( ! method_exists($this, 'rule_'.$rule))
					throw new Kohana_Exception('forge.invalid_rule', $rule);

				// The rule function is always prefixed with rule_
				$rule = 'rule_'.$rule;

				if (isset($args))
				{
					// Manually call up to 2 args for speed
					switch(count($args))
					{
						case 1:
							$this->$rule($args[0]);
						break;
						case 2:
							$this->$rule($args[0], $args[1]);
						break;
						default:
							call_user_func_array(array($this, $rule), $args);
						break;
					}
				}
				else
				{
					// Just call the rule
					$this->$rule();
				}

				// Prevent args from being re-used
				unset($args);
			}
		}

		// If there are errors, validation failed
		return $this->is_valid = empty($this->errors);
	}

	protected function rule_required()
	{
		if ($this->value == NULL)
		{
			$this->errors[] = 'This field is required.';
		}
	}

	protected function rule_length($min, $max = NULL)
	{
		if (empty($this->data['value']))
			return;

		// Get the length
		$length = strlen($this->data['value']);

		if ($max == NULL)
		{
			if ($length != $min)
			{
				$this->errors[] = 'This field must be exactly '.$min.' characters long.';
			}
		}
		else
		{
			if ($length < $min OR $length > $max)
			{
				$this->errors[] = 'This field must be between '.$min.' and '.$max.' characters long.';
			}
		}
	}

} // End Form Input