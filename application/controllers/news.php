<?php defined('SYSPATH') or die('No direct script access.');

class News_Controller extends Controller {

	protected $auto_render = TRUE;

	public function index()
	{
		Event::run('system.404');
	}

	public function gophp5()
	{
		$this->template->set(array
		(
			'title'   => 'GoPHP5',
			'content' => new View('pages/gophp5')
		));
	}

}