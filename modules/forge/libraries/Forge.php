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
	}

	public function __call($method, $args)
	{
		if ($method == 'hidden')
		{
			$this->hidden[$args[0]] = $args[1];
			return;
		}
		// Class name
		$input = 'Form_'.ucfirst($method);

		// Create the input
		$input = new $input(empty($args) ? NULL : current($args));

		if ( ! ($input instanceof Form_Input))
			throw new Kohana_Exception('forge.invalid_input');

		if ($name = $input->name)
		{
			// Assign by name
			$this->inputs[$name] = $input;
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
		foreach($this->inputs as $input)
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

		// Set the form open and close
		$form->open  = form::open($form->action, array('method' => 'post'), $this->hidden);
		$form->close = form::close();

		// Set the inputs
		$form->inputs = $this->inputs;

		return $form->render();
	}

} // End Forge