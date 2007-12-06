<?php defined('SYSPATH') or die('No direct script access.');

class Form_Submit_Core extends Form_Input {

	protected $data = array
	(
		'type'  => 'submit',
		'class' => 'submit'
	);

	protected $protect = array('type');

	public function __construct($value)
	{
		$this->data['value'] = $value;
	}

	public function html()
	{
		$data = $this->data;
		unset($data['label']);

		return form::button($data);
	}

	protected function validate()
	{
		// Submit buttons do not need to be validated
		return $this->is_valid = TRUE;
	}

} // End Form Submit