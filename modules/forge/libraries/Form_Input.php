<?php defined('SYSPATH') or die('No direct script access.');

class Form_Input_Core {

	// Input instance
	protected static $input;

	// Element data
	protected $data = array
	(
		'type'  => 'text',
		'class' => 'textbox',
		'value' => '',
	);

	// Protected data keys
	protected $protect = array();

	// Validation rules, matches, and callbacks
	protected $rules = array();
	protected $matches = array();
	protected $callbacks = array();

	// Validation check
	protected $is_valid;

	// Errors
	protected $errors = array();

	public function __construct($name)
	{
		if (self::$input === NULL)
		{
			// Load the Input library
			self::$input = new Input;
		}

		$this->data['name'] = $name;
	}

	public function __call($method, $args)
	{
		if ($method == 'rules')
		{
			// Set rules and action
			$rules  = $args[0];
			$action = substr($rules, 0, 1);

			if (in_array($action, array('-', '+', '=')))
			{
				// Remove the action from the rules
				$rules = substr($rules, 1);
			}
			else
			{
				// Default action is append
				$action = '';
			}

			$this->add_rules(explode('|', $rules), $action);
		}
		elseif ($method == 'name')
		{
			// Do nothing. The name should stay static once it is set.
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

	public function matches($input)
	{
		if ( ! in_array($input, $this->matches))
		{
			$this->matches[] = $input;
		}

		return $this;
	}

	public function callback($callback)
	{
		if ( ! in_array($callback, $this->callbacks))
		{
			$this->callbacks[] = $callback;
		}

		return $this;
	}

	public function label($val = NULL)
	{
		if ($val === NULL)
		{
			if ($name = $this->name)
			{
				return form::label($name, $this->label);
			}
		}
		else
		{
			$this->data['label'] = ($val === TRUE) ? ucwords(inflector::humanize($this->name)) : $val;
			return $this;
		}
	}

	public function html()
	{
		// Make sure validation runs
		$this->validate();

		return $this->html_element().$this->error_message();
	}

	protected function html_element()
	{
		$data = $this->data;

		unset($data['label']);

		return form::input($data);
	}

	protected function add_rules( array $rules, $action)
	{
		if ($action === '=')
		{
			// Just replace the rules
			$this->rules = $rules;
			return;
		}

		foreach($rules as $rule)
		{
			if ($action === '-')
			{
				if ($key = array_search($rule, $this->rules))
				{
					// Remove the rule
					unset($this->rules[$key]);
				}
			}
			else
			{
				if ( ! in_array($rule, $this->rules))
				{
					if ($action == '+')
					{
						array_unshift($this->rules, $rule);
					}
					else
					{
						$this->rules[] = $rule;
					}
				}
			}
		}
	}

	public function add_error($key, $val)
	{
		if ( ! isset($this->errors[$key]))
		{
			$this->errors[$key] = $val;
		}

		return $this;
	}

	protected function error_message()
	{
		// Make sure validation runs
		is_null($this->is_valid) and $this->validate();

		$message = '';
		foreach($this->errors as $func => $args)
		{
			if (is_string($args))
			{
				$error = $args;
			}
			else
			{
				// Force args to be an array
				$args = is_array($args) ? $args : array();

				// Add the label or name to the beginning of the args
				array_unshift($args, $this->label ? strtolower($this->label) : $this->name);

				// Fetch an i18n error message
				$error = Kohana::lang('validation.'.$func, $args);
			}

			// Make the error into HTML
			$message .= '<p class="error">'.$error.'</p>';
		}

		return $message;
	}

	protected function load_value()
	{
		if (is_bool($this->is_valid))
			return;

		if ($value = self::$input->post($this->name))
		{
			// Load POSTed value
			$this->data['value'] = $value;
		}

		if (is_string($this->data['value']))
		{
			// Trim string values
			$this->data['value'] = trim($this->data['value']);
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
		if (count($this->rules) == 0 AND count($this->matches) == 0 AND count($this->callbacks) == 0)
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

				if (substr($rule, 0, 6) === 'valid_' AND method_exists('valid', substr($rule, 6)))
				{
					$func = substr($rule, 6);

					if ($this->value AND ! valid::$func($this->value))
					{
						$this->errors[$rule] = TRUE;
					}
				}
				elseif (method_exists($this, 'rule_'.$rule))
				{
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
				else
				{
					throw new Kohana_Exception('validation.invalid_rule', $rule);
				}

				// Stop when an error occurs
				if ( ! empty($this->errors))
					break;
			}
		}

		if ( ! empty($this->matches))
		{
			foreach($this->matches as $input)
			{
				if ($this->value != $input->value)
				{
					// Field does not match
					$this->errors['matches'] = array($input->name);
					break;
				}
			}
		}

		if ( ! empty($this->callbacks))
		{
			foreach($this->callbacks as $callback)
			{
				call_user_func($callback, $this);

				// Stop when an error occurs
				if ( ! empty($this->errors))
					break;
			}
		}

		// If there are errors, validation failed
		return $this->is_valid = empty($this->errors);
	}

	protected function rule_required()
	{
		if ($this->value == FALSE)
		{
			$this->errors['required'] = TRUE;
		}
	}

	protected function rule_length($min, $max = NULL)
	{
		// Get the length, return if zero
		if (($length = strlen($this->value)) === 0)
			return;

		if ($max == NULL)
		{
			if ($length != $min)
			{
				$this->errors['exact_length'] = array($min);
			}
		}
		else
		{
			if ($length < $min)
			{
				$this->errors['min_length'] = array($min);
			}
			elseif($length > $max)
			{
				$this->errors['max_length'] = array($max);
			}
		}
	}

} // End Form Input