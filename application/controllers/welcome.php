<?php

class Welcome extends Controller {

	function index()
	{
		$this->load->view('welcome')->set('message', 'testing')->render(TRUE);
		print "hi";
	}

	function _index()
	{
		$this->load->helper('url');
		print "<p>Welcome to ".url::site('welcome')."!</p>\n";
		
		$this->load->model('users');
		print "<p>Model test: ".$this->users->hi()."!</p>\n";

		print "<p>Rendered in {execution_time} seconds</p>\n";
	}

}

?>