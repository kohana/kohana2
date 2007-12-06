<?php defined('SYSPATH') or die('No direct script access.');

class Forge_Controller extends Controller {

	public function index()
	{
		$profiler = new Profiler;

		$forge = new Forge('forge/index', 'Test Form');

		$forge->add(new Form_Input('username', array(
			'label' => 'Username',
			'rules' => 'required|length[4,32]')));
		$forge->add(new Form_Password('password', array(
			'label' => 'Password',
			'rules' => 'required|length[4,64]')));
		$forge->add(new Form_Checklist('roles', array(
			'options' => array(
				'Admin' => FALSE,
				'Login' => TRUE,
				'Other' => TRUE),
			'label' => 'Roles')));
		$forge->add(new Form_Submit('Login'));

		echo $forge->html();
	}

} // End