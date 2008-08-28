<?php

class Controller_Home extends Controller_Website {

	public $auto_render = TRUE;

	public function __call($method, $args)
	{
		$this->template->title = Kohana::lang('home.title');
		$this->template->content = View::factory('pages/home/home_'.Kohana::config('locale.language.0'));
	}

} // End Controller_Home