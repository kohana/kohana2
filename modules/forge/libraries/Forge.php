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

	protected $inputs = array();
	protected $hidden = array();

	public function __construct($action = '', $title = '', $method = 'post')
	{
		// Set action
		$this->template['action'] = $action;
		$this->template['title']  = $title;
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

		if ( ! ($input instanceof Form_Input))
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
		if (empty($_POST))
			return;

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

		// Set the form open and close
		$form->open  = form::open($form->action, array('method' => 'post'), $hidden);
		$form->close = form::close();

		// Set the inputs
		$form->inputs = $this->inputs;

		return $form->render();
	}

} // End Forge