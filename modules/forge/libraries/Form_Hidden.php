<?php defined('SYSPATH') or die('No direct script access.');

class Form_Hidden_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'hidden',
		'value' => '',
	);

	protected $protect = array('type', 'label');

	public function html()
	{
		$data = $this->data;

		return form::hidden($data);
	}

} // End Form Hidden