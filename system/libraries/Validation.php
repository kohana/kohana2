<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Validation library.
 *
 * $Id$
 *
 * @package    Validation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Validation_Core extends ArrayObject {

	// Unique "any field" key
	protected $any_field;

	// Message output format
	protected $message_format = '<p class="error">{message}</p>';

	// Filters
	protected $pre_filters = array();
	protected $post_filters = array();

	// Rules and callbacks
	protected $rules = array();
	protected $callbacks = array();

	// Errors
	protected $errors = array();
	protected $messages = array();

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  object
	 */
	public static function factory($array = NULL)
	{
		return new Validation( ! is_array($array) ? $_POST : $array);
	}

	/**
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct(array $array)
	{
		// Set a dynamic, unique "any field" key
		$this->any_field = uniqid(NULL, TRUE);

		parent::__construct($array, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
	}

	/**
	 * Returns the ArrayObject array values.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Set the format of message strings.
	 *
	 * @chainable
	 * @param   string   new message format
	 * @return  object
	 */
	public function message_format($str)
	{
		if (strpos($str, '{message}') === FALSE)
			throw new Kohana_Exception('validation.error_format');

		// Set the new message format
		$this->message_format = $str;

		return $this;
	}

	/**
	 * Sets or returns the message for an input.
	 *
	 * @chainable
	 * @param   string   input key
	 * @param   string   message to set
	 * @return  string|object
	 */
	public function message($input, $message = NULL)
	{
		if ($message === NULL)
		{
			// Return nothing if no message exists
			if (empty($this->messages[$input]))
				return '';

			// Return the HTML message string
			return str_replace('{message}', $this->messages[$input], $this->message_format);
		}
		else
		{
			$this->messages[$input] = $message;
		}

		return $this;
	}

	/**
	 * Add a pre-filter to one or more inputs.
	 *
	 * @chainable
	 * @param   callback  filter
	 * @param   string    fields to apply filter to, use TRUE for all fields
	 * @return  object
	 */
	public function pre_filter($filter, $field = TRUE)
	{
		if ( ! is_callable($filter))
			throw new Kohana_Exception('validation.filter_not_callable');

		if ($field === TRUE)
		{
			// Handle "any field" filters
			$fields = $this->any_field;
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->pre_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add a post-filter to one or more inputs.
	 *
	 * @chainable
	 * @param   callback  filter
	 * @param   string    fields to apply filter to, use TRUE for all fields
	 * @return  object
	 */
	public function post_filter($filter, $field = TRUE)
	{
		if ( ! is_callable($filter, TRUE))
			throw new Kohana_Exception('validation.filter_not_callable');

		if ($field === TRUE)
		{
			// Handle "any field" filters
			$fields = $this->any_field;
		}
		else
		{
			// Add the filter to specific inputs
			$fields = func_get_args();
			$fields = array_slice($fields, 1);
		}

		foreach ($fields as $field)
		{
			// Add the filter to specified field
			$this->post_filters[$field][] = $filter;
		}

		return $this;
	}

	/**
	 * Add rules to a field. Rules are callbacks or validation methods. Rules can
	 * only return TRUE or FALSE.
	 *
	 * @chainable
	 * @param   string    field name
	 * @param   callback  rules (unlimited number)
	 * @return  object
	 */
	public function add_rules($field, $rules)
	{
		// Handle "any field" filters
		($field === TRUE) and $field = $this->any_field;

		// Get the rules
		$rules = func_get_args();
		$rules = array_slice($rules, 1);

		foreach ($rules as $rule)
		{
			// Rule arguments
			$args = NULL;

			if (is_string($rule))
			{
				if (preg_match('/^([^\[]++)\[(.+)\]$/', $rule, $matches))
				{
					// Split the rule into the function and args
					$rule = $matches[1];
					$args = preg_split('/(?<!\\\\),\s*/', $matches[2]);
				}

				if (method_exists($this, $rule))
				{
					// Make the rule a valid callback
					$rule = array($this, $rule);
				}
			}

			if ( ! is_callable($rule, TRUE))
				throw new Kohana_Exception('validation.rule_not_callable');

			// Add the rule to specified field
			$this->rules[$field][] = array($rule, $args);
		}

		return $this;
	}

	/**
	 * Add callbacks to a field. Callbacks must accept the Validation object
	 * and the input name. Callback returns are not processed.
	 *
	 * @chainable
	 * @param   string     field name
	 * @param   callbacks  callbacks (unlimited number)
	 * @return  object
	 */
	public function add_callbacks($field, $callbacks)
	{
		// Handle "any field" filters
		($field === TRUE) and $field = $this->any_field;

		if (func_get_args() > 2)
		{
			// Multiple callback
			$callbacks = func_get_args();
			$callbacks = array_slice($callbacks, 1);
		}
		else
		{
			// Only one callback
			$callbacks = array($callbacks);
		}

		foreach ($callbacks as $callback)
		{
			if ( ! is_callable($callback, TRUE))
				throw new Kohana_Exception('validation.callback_not_callable');

			// Add the filter to specified field
			$this->callbacks[$field][] = $callback;
		}

		return $this;
	}

	/**
	 * Validate by processing pre-filters, rules, callbacks, and post-filters.
	 * All fields that have filters, rules, or callbacks will be initialized if
	 * they are undefined. Validation will only be run if there is data already
	 * in the array.
	 *
	 * @return bool
	 */
	public function validate()
	{
		// All the fields that are being validated
		$all_fields = array_unique(array_merge
		(
			array_keys($this->pre_filters),
			array_keys($this->rules),
			array_keys($this->callbacks),
			array_keys($this->post_filters)
		));

		// Only run validation when POST data exists
		$run_validation = (count($this) > 0);

		foreach ($all_fields as $i => $field)
		{
			if ($field === $this->any_field)
			{
				// Remove "any field" from the list of fields
				unset($all_fields[$i]);
				continue;
			}

			// Make sure all fields are defined
			isset($this[$field]) or $this[$field] = NULL;
		}

		if ($run_validation === FALSE)
			return FALSE;

		// Reset all fields to ALL defined fields
		$all_fields = array_keys($this->getArrayCopy());

		foreach ($this->pre_filters as $field => $calls)
		{
			foreach ($calls as $func)
			{
				if ($field === $this->any_field)
				{
					foreach ($all_fields as $f)
					{
						// Process each filter
						$this[$f] = is_array($this[$f]) ? array_map($func, $this[$f]) : call_user_func($func, $this[$f]);
					}
				}
				else
				{
					// Process each filter
					$this[$field] = is_array($this[$field]) ? array_map($func, $this[$field]) : call_user_func($func, $this[$field]);
				}
			}
		}

		foreach ($this->rules as $field => $calls)
		{
			foreach ($calls as $call)
			{
				// Split the rule into function and args
				list($func, $args) = $call;

				if ($field === $this->any_field)
				{
					foreach ($all_fields as $f)
					{
						// Prevent other rules from running when this field already has errors
						if ( ! empty($this->errors[$f])) break;

						// Don't process rules on empty fields
						if (($func[1] !== 'required' AND $func[1] !== 'matches') AND empty($this[$f]))
							continue;

						// Run each rule
						if ( ! call_user_func($func, $this[$f], $args))
						{
							$this->errors[$f] = is_array($func) ? $func[1] : $func;
						}
					}
				}
				else
				{
					// Prevent other rules from running when this field already has errors
					if ( ! empty($this->errors[$field])) break;

					// Don't process rules on empty fields
					if (($func[1] !== 'required' AND $func[1] !== 'matches') AND empty($this[$field]))
						continue;

					// Run each rule
					if ( ! call_user_func($func, $this[$field], $args))
					{
						$this->errors[$field] = is_array($func) ? $func[1] : $func;
						// Stop after an error is found
						break;
					}
				}
			}
		}

		foreach ($this->callbacks as $field => $calls)
		{
			foreach ($calls as $func)
			{
				if ($field === $this->any_field)
				{
					foreach ($all_fields as $f)
					{
						// Execute the callback
						call_user_func($func, $this, $f);

						// Stop after an error is found
						if ( ! empty($errors[$f])) break 2;
					}
				}
				else
				{
					// Execute the callback
					call_user_func($func, $this, $field);

					// Stop after an error is found
					if ( ! empty($errors[$f])) break;
				}
			}
		}

		foreach ($this->post_filters as $field => $calls)
		{
			foreach ($calls as $func)
			{
				if ($field === $this->any_field)
				{
					foreach ($all_fields as $f)
					{
						// Process each filter
						$this[$f] = is_array($this[$f]) ? array_map($func, $this[$f]) : call_user_func($func, $this[$f]);
					}
				}
				else
				{
					// Process each filter
					$this[$field] = is_array($this[$field]) ? array_map($func, $this[$field]) : call_user_func($func, $this[$field]);
				}
			}
		}

		// Return TRUE if there are no errors
		return (count($this->errors) === 0);
	}

	/**
	 * Add an error to an input.
	 *
	 * @chainable
	 * @param   string  input name
	 * @param   string  unique error name
	 * @return  object
	 */
	public function add_error($field, $name)
	{
		if (isset($this[$field]))
		{
			$this->errors[$field] = $name;
		}

		return $this;
	}

	/**
	 * Return the errors array.
	 *
	 * @return array
	 */
	public function errors()
	{
		return $this->errors;
	}

	/**
	 * Rule: required. Generates an error if the field has an empty value.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function required($str)
	{
		return ! ($str === '' OR $str === NULL OR $str === FALSE OR (is_array($str) AND empty($str)));
	}

	/**
	 * Rule: matches. Generates an error if the field does not match one or more
	 * other fields.
	 *
	 * @param   mixed   input value
	 * @param   array   input names to match against
	 * @return  bool
	 */
	public function matches($str, array $inputs)
	{
		foreach ($inputs as $key)
		{
			if ($str !== (isset($this[$key]) ? $this[$key] : NULL))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * @param   mixed   input value
	 * @param   array   minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		$size = strlen($str);
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}

} // End Validation