<?php defined('SYSPATH') or die('No direct script access.');

class Image_demo_Controller extends Controller {

	public function index()
	{
		$dir = str_replace('\\', '/', realpath(dirname(__FILE__).'/../upload')).'/';

		$image = new Image($dir.'good-omen-cat.jpg');
		$image->resize(85, 85, Image::NONE);
		$image->save($dir.'good_cat.gif');

		echo Kohana::debug($image);
	}

} // End