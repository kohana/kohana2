<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();
	}

	function index()
	{
		$this->load->library('session');
		$this->session->set('hi');
		$this->load->view('welcome_message');
	}
}

?>