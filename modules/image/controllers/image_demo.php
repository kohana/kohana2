<?php defined('SYSPATH') or die('No direct script access.');

class Image_demo_Controller extends Controller {

	public function index()
	{
		$dir = str_replace('\\', '/', realpath(dirname(__FILE__).'/../upload')).'/';

		$image = new Image($dir.'moo.jpg');
		$image->resize(400, NULL);
		$image->crop(600, 300, 'bottom');
		$image->save($dir.'super-cow-crop.jpg');

		echo Kohana::debug($image);
	}

} // End