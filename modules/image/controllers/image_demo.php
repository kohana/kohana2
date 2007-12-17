<?php defined('SYSPATH') or die('No direct script access.');

class Image_demo_Controller extends Controller {

	public function index()
	{
		$profiler = new Profiler;

		$dir = str_replace('\\', '/', realpath(dirname(__FILE__).'/../upload')).'/';

		$image = new Image($dir.'moo.jpg');

		$image->resize(400, NULL)->crop(400, 350, 'top')->sharpen(20);

		$image->save($dir.'super-cow-crop.jpg');

		echo Kohana::debug($image);
	}

} // End