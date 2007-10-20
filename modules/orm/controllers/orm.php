<?php defined('SYSPATH') or die('No direct script access.');

class Orm_Controller extends Controller {

	function index()
	{
		$this->load->library('profiler');

		$user = new User_Model(array('email_address' => 'john@smith.com'));

		print "user: ".Kohana::debug_output($user->data());

		print Kohana::lang('core.stats_footer');
	}

}