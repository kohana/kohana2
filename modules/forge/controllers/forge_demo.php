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

		if ($form->validate())
		{
			echo Kohana::debug($form->as_array());
		}

		echo $form->html();
	}

	public function edit_user($id = FALSE)
	{
		$profiler = new Profiler;
		$cache = new Cache;

		// Cache id for the current empty editing form
		$cache_id = 'form--'.url::current();

		if (empty($_POST))
		{
			// Attempt to get the HTML from cache
			if ($form = $cache->get($cache_id))
			{
				echo $form;
				return;
			}
		}

		// Create a new user editing form
		$form = new User_Edit_Model(NULL, 'Edit User', $id);

		if ($form->save())
		{
			// Cache information is no longer valid
			$cache->del($cache_id);

			echo Kohana::debug('user edited!', $form->as_array());
		}

		if (empty($_POST))
		{
			// Cache the form HTML
			$cache->set($cache_id, $form = $form->html());
		}

		echo $form;
	}

} // End