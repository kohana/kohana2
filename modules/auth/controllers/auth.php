<?php defined('SYSPATH') or die('No direct script access.');

class Auth_Controller extends Controller {

	function _remap()
	{
		$profiler = new Profiler();
		$session  = new Session();

		$user = new User_Model(1);

		print Kohana::debug($user->has_role('1'));
		return;
		$auth = new Auth();

		print Kohana::debug($auth->login('woody.gilk', 'breakfast'));
	}
}