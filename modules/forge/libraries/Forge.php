<?php defined('SYSPATH') or die('No direct script access.');

class Forge_Core {

	protected $template = array
	(
		'action' => '',
		'title'  => '',
		'open'   => '',
		'close'  => '',
		'class'  => 'form'
	);

	public $inputs = array();
	public $hidden = array();

	public function __construct($action = '', $title = '', $method = NULL, $class = NULL)
	{
		empty($method) and $method = 'post';
		empty($class)  and $class  = 'form';

		// Set action
		$this->template['action'] = $action;
		$this->template['title']  = $title;
		$this->template['class']  = $class;
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

		if ($name = $input->name)
		{
			if ($method == 'hidden')
			{
				$this->hidden[$name] = $input;
			}
			else
			{
				// Assign by name
				$this->inputs[$name] = $input;
			}
		}
		else
		{
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

	/**
	 * Creates the form HTML
	 *
	 * @param   string   form view template name
	 * @return  string
	 */
	public function html($template = 'forge_template')
	{
		// Load template with current template vars
		$form = new View($template, $this->template);

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
				break;
			}
		}
		// Set the form open and close
		$form->open  = form::$form_type($form->action, array('method' => 'post'), $hidden);
		$form->close = form::close();

		// Set the inputs
		$form->inputs = $this->inputs;

		return $form->render();
	}

	/**
	 * Returns the form HTML
	 */
	public function __toString()
	{
		return $this->html();
	}

} // End Forge