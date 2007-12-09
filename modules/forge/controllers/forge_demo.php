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

		// Create each input, following this format:
		//
		//   type($name)->attr(..)->attr(..);
		//
		$form->hidden('hideme')->value('hiddenz!');
		$form->input('email')->label(TRUE)->rules('required|valid_email');
		$form->input('username')->label(TRUE)->rules('required|length[5,32]');
		$form->password('password')->label(TRUE)->rules('required|length[5,32]');
		$form->password('confirm')->label(TRUE)->matches($form->password);
		$form->checkbox('remember')->label('Remember Me');
		$form->checklist('foods')->label('Favorite Foods')->options($foods)->rules('required');
		$form->dropdown('state')->label('Home State')->options(locale_US::states())->rules('required');
		$form->dateselect('birthday')->label(TRUE)->minutes(15)->years(1950, date('Y'));
		$form->submit('Save');

		echo Kohana::debug(headers_list());exit;

		if ($form->validate())
		{
			echo Kohana::debug($form->as_array());
		}

		echo $form->html();
	}

	public function edit_user($id = FALSE)
	{
		$profiler = new Profiler;

		$form = new User_Edit_Model(NULL, 'Edit User', $id);

		if ($form->save())
		{
			echo Kohana::debug('user edited!', $form->as_array());
		}
		else
		{
			echo $form;
		}
	}

	public function bench()
	{
		Benchmark::start('using_array');
		$output = array();
		for ($i = 0; $i < 1000; $i++)
		{
			$output[] = ($i % 2 == 0) ? 'a' : 'b';
		}
		$output = implode('', $output);
		$array = Benchmark::get('using_array');

		unset($i, $output);

		Benchmark::start('using_string');
		$output = '';
		for ($i = 0; $i < 1000; $i++)
		{
			$output .= ($i % 2 == 0) ? 'a' : 'b';
		}

		$string = Benchmark::get('using_string');

		echo Kohana::debug('imploded array: ', $array, 'string append: ', $string);
	}

} // End