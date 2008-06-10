<?php defined('SYSPATH') or die('No direct script access.');

class News_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function index()
	{
		Event::run('system.404');
	}

	public function gophp5()
	{
		$this->template->title = 'GoPHP5';
		$this->template->content = View::factory('pages/news/gophp5_'.Config::item('locale.language'));
	}

} // End News Controller