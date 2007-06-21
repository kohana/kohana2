<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Controller {

	function Main()
	{
		parent::Controller();
	}

	function index()
	{
		$this->output->enable_profiler(TRUE);
		$this->load->helper('url');
		$this->load->library('session');
		
		// $this->session->create();
		// $this->session->set('foo', 'bar');
		// $this->session->set_flash('foo', 'bar');
		print_r ($_SESSION);
		
		// $this->session->death();
		
		// array_merge(array(), $null);
	}
	
	function foo($arg = 'none')
	{
		print $arg;
	}
}

?>