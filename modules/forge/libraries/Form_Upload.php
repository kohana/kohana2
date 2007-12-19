<?php defined('SYSPATH') or die('No direct script access.');

class Form_Upload_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'upload'
	);

	protected $protect = array('type', 'label');

	public function html()
	{
		$data = $this->data;

		return form::upload($data);
	}

} // End Form Upload