<?php defined('SYSPATH') or die('No direct script access.');

class Form_Checklist_Core extends Form_input{

	protected $data = array
	(
		'name'  => '',
		'type'  => 'checkbox',
		'class' => 'checklist',
	);

	protected $protect = array('name', 'type');

	// Name of the list
	protected $list_name = '';

	// Associative array of options: value => checked
	protected $list_options = array();

	// List input data
	protected $list_data = array();

	public function __construct($name, $data)
	{
		$this->list_name = $this->data['name'] = $name;

		$this->list_options = arr::remove('options', $data);

		foreach($data as $key => $val)
		{
			$this->$key = $val;
		}
	}

	public function __get($key)
	{
		if ($key == 'value')
		{
			return isset($_POST[$this->list_name]) ? $_POST[$this->list_name] : array();
		}

		return parent::__get($key);
	}

	public function html()
	{
		// Load the submitted value
		$this->load_value();

		// Import base data
		$base_data = $this->data;

		// Newline
		$nl = "\n";

		$checklist = '<ul class="'.arr::remove('class', $base_data).'">'.$nl;
		foreach($this->list_options as $val => $checked)
		{
			// New set of input data
			$data = $base_data;

			// Set the name, value, and checked status
			$data['name']    = $this->list_name.'[]';
			$data['value']   = $val;
			$data['checked'] = $checked;

			$checklist .= '<li><label>'.form::checkbox($data).' '.$val.'</label></li>'.$nl;
		}
		$checklist .= '</ul>';

		return $checklist;
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		foreach($this->list_options as $val => $checked)
		{
			if (empty($_POST[$this->list_name]))
			{
				$this->list_options[$val] = FALSE;
			}
			else
			{
				$this->list_options[$val] = in_array($val, $_POST[$this->list_name]);
			}
		}
	}

} // End Form Checklist