<?php

class Welcome_Controller extends Controller {

	function index()
	{
		$db = new Database();
		print_r ($db);
	}

}