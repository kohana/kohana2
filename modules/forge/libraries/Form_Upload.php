<?php defined('SYSPATH') or die('No direct script access.');

class Form_Upload_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'upload',
		'value' => '',
	);

	protected $protect = array('type', 'label', 'value');

	public function __construct($name)
	{
		parent::__construct($name);

		if ( ! empty($_FILES) AND ! empty($_FILES[$name]))
			$_POST[$name] = $_FILES[$name];
	}

	public function rule_allow()
	{
		echo Kohana::debug(func_get_args());

		return TRUE;
	}

	public function rule_size($size)
	{
		echo Kohana::debug($size);

		return TRUE;
	}

	public function load_value()
	{
		if (empty($_FILES))
			return;

		echo Kohana::debug($_FILES);
	}

	public function html()
	{
		$data = $this->data;

		return form::upload($data);
	}

} // End Form Upload