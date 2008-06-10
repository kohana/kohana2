<?php defined('SYSPATH') or die('No direct script access.');

class Home_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function _remap()
	{
		$this->template->set(array
		(
			'title'   => Kohana::lang('home.title'),
			'content' => new View('pages/home')
		));
	}

} // End Kohana Website Controller