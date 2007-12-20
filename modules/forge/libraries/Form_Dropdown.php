<?php defined('SYSPATH') or die('No direct script access.');

class Form_Dropdown_Core extends Form_Input {

	protected $data = array
	(
		'name'  => '',
		'class' => 'dropdown',
	);

	protected $protect = array('type');

	public function __get($key)
	{
		if ($key == 'value')
		{
			return $this->selected;
		}

		return parent::__get($key);
	}

	public function html_element()
	{
		// Import base data
		$base_data = $this->data;

		// Get the options and default selection
		$options = arr::remove('options', $base_data);
		$selected = arr::remove('selected', $base_data);

		return form::dropdown($base_data, $options, $selected);
	}

	protected function load_value()
	{
		if (is_bool($this->valid))
			return;

		$this->data['selected'] = $this->input_value($this->name);
	}

} // End Form Dropdown