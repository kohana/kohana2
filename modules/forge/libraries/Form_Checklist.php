<?php defined('SYSPATH') or die('No direct script access.');

class Form_Checklist_Core extends Form_Input {

	protected $data = array
	(
		'name'  => '',
		'type'  => 'checkbox',
		'class' => 'checklist',
	);

	protected $protect = array('name', 'type');

	public function __construct($name)
	{
		$this->data['name'] = $name;
	}

	public function __get($key)
	{
		if ($key == 'value')
		{
			// Return the currently checked values
			return array_keys($this->data['options'], TRUE);
		}

		return parent::__get($key);
	}

	public function html()
	{
		// Import base data
		$base_data = $this->data;

		// Make it an array
		$base_data['name'] .= '[]';

		// Newline
		$nl = "\n";

		$checklist = '<ul class="'.arr::remove('class', $base_data).'">'.$nl;
		foreach(arr::remove('options', $base_data) as $val => $checked)
		{
			// New set of input data
			$data = $base_data;

			// Set the name, value, and checked status
			$data['value']   = $val;
			$data['checked'] = $checked;

			$checklist .= '<li><label>'.form::checkbox($data).' '.$val.'</label></li>'.$nl;
		}
		$checklist .= '</ul>';

		return $checklist.$this->error_message();
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		foreach($this->data['options'] as $val => $checked)
		{
			if (empty($_POST[$this->data['name']]))
			{
				$this->data['options'][$val] = FALSE;
			}
			else
			{
				$this->data['options'][$val] = in_array($val, $_POST[$this->data['name']]);
			}
		}
	}

} // End Form Checklist