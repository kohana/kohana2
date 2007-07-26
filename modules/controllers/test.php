<?php

class Test extends Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index($arg = 'none')
	{
		print "<p>Index of Test loaded: $arg</p>\n";
	}

}

?>