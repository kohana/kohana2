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

	public function __construct($action = '', $title = '', $method = 'post')
	{
		// Set action
		$this->template['action'] = $action;
		$this->template['title']  = $title;
	}

	public function __get($key)
	{
		if ($key == 'validated')
		{
			foreach($this->inputs as $input)
			{
				if ( ! $input->is_valid)
					return FALSE;
			}
			return TRUE;
		}
		elseif (isset($this->inputs[$key]))
		{
			return $this->inputs[$key]->value;
		}
	}

	public function add($input)
	{
		if ( ! ($input instanceof Form_Input))
			throw new Kohana_Exception('forge.invalid_input');

		if ($name = $input->name)
		{
			$this->inputs[$name] = $input;
		}
		else
		{
			$this->inputs[] = $input;
		}

		return $this;
	}

	public function data()
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
		$form->open  = form::open($form->action);
		$form->close = form::close();

		// Set the inputs
		$form->inputs = $this->inputs;

		return $form->render();
	}

} // End Forge