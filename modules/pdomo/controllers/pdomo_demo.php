<?php defined('SYSPATH') or die('No direct script access.');

class Pdomo_Demo_Controller extends Controller {

	public function index()
	{
		pdomo::registry('default', new PDO('mysql:host=localhost;dbname=kohana', 'root', 'r00tdb'));

		$model = pdomo::factory('user')->find('username', 'woody.gilk');

		$model->email = 'none';

		echo Kohana::debug($model->save());

		echo Kohana::debug($model);
		echo Kohana::debug('{execution_time}');
	}

} // End Pdomo Demo