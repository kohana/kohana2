<?php defined('SYSPATH') or die('No direct script access.');

class Home_Controller extends Controller {

	protected $auto_render = TRUE;

	public function _remap()
	{
		$this->template->set(array
		(
			'title'   => 'Home',
			'content' => new View('pages/home')
		));
	}

} // End Kohana Website Controller