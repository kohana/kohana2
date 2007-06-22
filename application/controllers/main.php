<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Controller {

	function Main()
	{
		parent::Controller();

		// Enable Profiler for debugging
		$this->output->enable_profiler(TRUE);
	}

	function index()
	{
		// ob_start();
		$this->load->library('session');

		// Test session creation
		// $this->session->create();

		// Test setting data
		$this->session->set('foo', 'bar');
		$this->session->set('baz', 'foo');
		
		// Test deleting data
		$this->session->del('baz');

		// Test setting protected key
		$this->session->set('ip_address', null);

		// Test setting flash data
		// $this->session->set_flash('foo', 'baz');
		$this->session->keep_flash('foo');

		// Test setting data via CI Session method
		// $this->session->set_userdata('foo', 'bar');
		
		print_r ($_SESSION);
	}

}

?>