<?php defined('SYSPATH') or die('No direct script access.');

class Form_Checkbox_Core extends Form_Input {

	protected $data = array
	(
		'type' => 'checkbox',
		'class' => 'checkbox',
		'value' => '1',
		'checked' => FALSE,
	);

	protected $protect = array('type');

	public function __get($key)
	{
		if ($key == 'value')
		{
			// Return the value if the checkbox is checked
			return $this->data['checked'] ? $this->data['value'] : NULL;
		}

		return parent::__get($key);
	}

	protected function html_element()
	{
		// Get the 
		$data = $this->data;

		// Remove label
		unset($data['label']);

		return form::checkbox($data);
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		// Makes the box checked if the value from POST is the same as the current value
		$this->data['checked'] = (self::$input->post($this->name) == $this->data['value']);
	}

} // End Form Checkbox