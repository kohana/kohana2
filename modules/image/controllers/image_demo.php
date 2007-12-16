<?php defined('SYSPATH') or die('No direct script access.');

class Image_demo_Controller extends Controller {

	public function index()
	{
		$dir = str_replace('\\', '/', realpath(dirname(__FILE__).'/../upload')).'/';

		$image = new Image($dir.'moo.jpg');
		$image->resize(200, NULL);
		$image->crop(150, 150, 'center', 'center');
		$image->rotate(-400);

		echo Kohana::debug($image);
	}

} // End