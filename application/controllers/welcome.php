<?php

class Welcome_Controller extends Controller {

	function index()
	{
		print new View('welcome', array('message' => 'testing calls back to the controller'));
		
		// $db = new Database();
		// print_r ($db);
	}

	function say_hello()
	{
		return '<strong>OMG HI!!!!1</strong>';
	}

}