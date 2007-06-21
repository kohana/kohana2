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
		
		// $_SESSION['hello'] = 'hi world!';
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