<?php

class Users_Controller extends Controller {

	public function __construct()
	{
		die('controller in a subdir!');
	}

	public function index()
	{
		print "hello world";
	}

}