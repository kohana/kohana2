<?php defined('SYSPATH') or die('No direct script access.');

class Forge_Core {

	// Template variables
	protected $template = array
	(
		'title' => '',
		'class' => '',
		'open'  => '',
		'close' => '',
	);

	// Form attributes
	protected $attr = array();

	// Form inputs and hidden inputs
	public $inputs = array();
	public $hidden = array();

	// Error message format, only used with custom templates
	public $error_format = '<p class="error">{message}</p>';
	public $newline_char = "\n";

	public function __construct($action = '', $title = '', $method = NULL, $attr = array())
	{
		// Set form attributes
		$this->attr['action'] = $action;
		$this->attr['method'] = empty($method) ? 'post' : $method;

		// Set template variables
		$this->template['title'] = $title;

		// Empty attributes sets the class to "form"
		empty($attr) and $attr = array('class' => 'form');

		// String attributes is the class name
		is_string($attr) and $attr = array('class' => $attr);

		// Extend the template with the attributes
		$this->attr += $attr;
	}

	public function __get($key)
	{
		if (isset($this->inputs[$key]))
		{
			return $this->inputs[$key];
		}
		elseif (isset($this->hidden[$key]))
		{
			return $this->hidden[$key];
		}
	}

	public function __call($method, $args)
	{
		// Class name
		$input = 'Form_'.ucfirst($method);

		// Create the input
		switch(count($args))
		{
			case 1:
				$input = new $input($args[0]);
			break;
			case 2:
				$input = new $input($args[0], $args[1]);
			break;
		}

		if ( ! ($input instanceof Form_Input) AND ! ($input instanceof Forge))
			throw new Kohana_Exception('forge.invalid_input', get_class($input));

		$input->method = $this->attr['method'];

		if ($name = $input->name)
		{
			// Assign by name
			if ($method == 'hidden')
			{
				$this->hidden[$name] = $input;
			}
			else
			{
				$this->inputs[$name] = $input;
			}
		}
		else
		{
			// No name, these are unretrievable
			$this->inputs[] = $input;
		}

		return $input;
	}

	public function validate()
	{
		$status = TRUE;
		foreach($this->inputs as $input)
		{
			if ($input->validate() == FALSE)
			{
				$status = FALSE;
			}
		}

		return $status;
	}

	public function as_array()
	{
		$data = array();
		foreach(array_merge($this->hidden, $this->inputs) as $input)
		{
			if ($name = $input->name)
			{
				// Return only named inputs
				$data[$name] = $input->value;
			}
		}
		return $data;
	}

	public function error_format($string = '')
	{
		if (strpos((string) $string, '{message}') === FALSE)
			throw new Kohana_Exception('validation.error_format');

		$this->error_format = $string;
	}

	/**
	 * Creates the form HTML
	 *
	 * @param   string   form view template name
	 * @param   boolean  use a custom view
	 * @return  string
	 */
	public function html($template = 'forge_template', $custom = FALSE)
	{
		// Load template with current template vars
		$form = new View($template, $this->template);

		if ($custom)
		{
			// Using a custom view

			$data = array();
			foreach ($this->inputs as $input)
			{
				$data[$input->name] = $input;

				// Compile the error messages for this input
				$messages = '';
				$errors = $input->error_messages();
				if (is_array($errors) AND ! empty($errors))
				{
					foreach($errors as $error)
					{
						// Replace the message with the error in the html error string
						$messages .= str_replace('{message}', $error, $this->error_format).$this->newline_char;
					}
				}

				$data[$input->name.'_errors'] = $messages;
			}

			$form->set($data);
		}
		else
		{
			// Using a template view

			$hidden = array();
			if ( ! empty($this->hidden))
			{
				foreach($this->hidden as $input)
				{
					$hidden[$input->name] = $input->value;
				}
			}

			$form_type = 'open';
			// See if we need a multipart form
			foreach ($this->inputs as $input)
			{
				if ($input instanceof Form_Upload)
				{
					$form_type = 'open_multipart';
				}
			}

			// Set the form open and close
			$form->open  = form::$form_type(arr::remove('action', $this->attr), $this->attr, $hidden);
			$form->close = form::close();

			// Set the inputs
			$form->inputs = $this->inputs;
		}

		return $form;
	}

	/**
	 * Returns the form HTML
	 */
	public function __toString()
	{
		return $this->html();
	}

} // End Forge