<?php defined('SYSPATH') or die('No direct script access.');

class Form_Textarea_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'textarea',
		'value' => '',
	);

	protected $protect = array('type');

	protected function html_element()
	{
		$data = $this->data;

		unset($data['label']);

		return form::textarea($data);
	}

} // End Form Password