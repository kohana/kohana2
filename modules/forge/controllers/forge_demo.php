<?php defined('SYSPATH') or die('No direct script access.');

class Forge_demo_Controller extends Controller {

	public function index()
	{
		$profiler = new Profiler;

		$foods = array(
			'taco' => FALSE,
			'burger' => FALSE,
			'spaghetti (checked)' => TRUE,
			'cookies (checked)' => TRUE);

		$form = new Forge(NULL, 'New User');
		$form->input('username')->label(TRUE)->rules('required');
		$form->password('password')->label(TRUE)->rules('required');
		$form->checklist('foods')->label('Favorite Foods')->options($foods)->rules('required');
		$form->dropdown('state')->label('Home State')->options(locale_US::states())->rules('required');
		$form->submit('Save');

		if ($form->validate())
		{
			echo Kohana::debug($form->as_array());
		}

		echo $form->html();
	}

} // End