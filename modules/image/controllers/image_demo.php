<?php defined('SYSPATH') or die('No direct script access.');

class Image_demo_Controller extends Controller {

	public function index()
	{
		$profiler = new Profiler;

		$dir = str_replace('\\', '/', realpath(dirname(__FILE__).'/../upload')).'/';

		for ($i = 0; $i < 1000; $i++)
		{
			$img = new Image($dir.'moo.jpg');
			$img->crop(200, 200, 'center', 'center');
			$img->resize(200, 150);
		}
	}

} // End