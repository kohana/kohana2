<?php defined('SYSPATH') or die('No direct script access.');

class License_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function index()
	{
		$this->template->title = Kohana::lang('license.title');
		$this->template->content = View::factory('pages/license/license_'.Kohana::config('locale.language.0'));
	}

} // End License Controller