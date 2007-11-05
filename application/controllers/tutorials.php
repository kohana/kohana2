<?php defined('SYSPATH') or die('No direct script access.');

class Tutorials_Controller extends Controller {

	protected $auto_render = TRUE;

	public function _default()
	{
		$this->template->content = new View('pages/tutorials/index');
	}

} // End Tutorial_Controller