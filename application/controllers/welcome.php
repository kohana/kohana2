<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();
	}

	function index()
	{
		$this->load->model('blog');
		$this->load->view('welcome_message');
	}
}
?>