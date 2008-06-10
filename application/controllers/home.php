<?php defined('SYSPATH') or die('No direct script access.');

class Home_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function _remap()
	{
		$this->template->title = Kohana::lang('home.title');
		$this->template->content = View::factory('pages/home/home_'.Config::get('locale.language'));
	}

} // End Kohana Website Controller