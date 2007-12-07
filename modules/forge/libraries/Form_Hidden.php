<?php defined('SYSPATH') or die('No direct script access.');

class Form_Hidden_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'hidden',
		'value' => '',
	);

	protected $protect = array('type');

	protected function html_element()
	{
		$data = $this->data;

		unset($data['label']);

		return form::hidden($data);
	}

} // End Form Password