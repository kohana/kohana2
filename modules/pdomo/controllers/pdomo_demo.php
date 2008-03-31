<?php defined('SYSPATH') or die('No direct script access.');

class Pdomo_Demo_Controller extends Controller {

	public function index()
	{
		pdomo::registry('default', new PDO('mysql:host=localhost;dbname=kohana', 'root', 'r00tdb'));

		$m = pdomo::factory('user_token')->find(1);

		echo Kohana::debug($m);
		echo Kohana::debug('{execution_time}');
	}

} // End Pdomo Demo