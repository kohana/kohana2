<?php defined('SYSPATH') or die('No direct script access.');

class Home_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function __call($method, $args)
	{
		$this->template->title = Kohana::lang('home.title');
		$this->template->content = View::factory('pages/home/home_'.Kohana::config('locale.language.0'));
	}

} // End Kohana Website Controller