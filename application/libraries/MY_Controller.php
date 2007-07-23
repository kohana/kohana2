<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller extends Core_Controller {

	public function __construct()
	{
		parent::__construct();
		
		print "Controller extension loaded";
	}

}

?>