<?php defined('SYSPATH') or die('No direct script access.');

class Form_Password_Core extends Form_Input {

	protected $data = array
	(
		'type'  => 'password',
		'class' => 'password'
	);

	protected $protect = array('type');

} // End Form Password