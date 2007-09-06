<?php

class Welcome_Controller extends Controller {

	function index()
	{
		echo "Welcome!";
	}

	function validation_example()
	{
		// To demonstrate Validation being able to validate any array, I will
		// be using a pre-built array. When you load validation with no arguments
		// it will default to validating the POST array.
		$data = array
		(
			'user' => 'hi',
			'pass' => 'bigsecret',
			'reme' => '1'
		);

		$this->load->library('validation', $data);

		$this->validation->set(array
		(
			'user' => array('Username',    'trim|required[4,32]'),
			'pass' => array('Password',    'required|sha1'),
			'reme' => array('Remember Me', 'integer')
		));

		print $this->validation->debug();

		print "{execution_time} ALL DONE!";
	}

	function database_example()
	{
		$db = new Database();

		print_r($db->query('SELECT * FROM pages'));

		print "<br/><br/>\n";
		print "done in {execution_time} seconds";
	}

	function pagination_example()
	{
		// You HAVE TO use $this->pagination when initializing the Pagination library.
		// This is because the pagination views call $this->pagination->url().
		// Problem: what in case your page has multiple pagination areas? Hmm...
		$this->pagination = new Pagination(array(
			'base_url'       => 'welcome/pagination_example/page/', // page segment doesn't need to be the last one
			'uri_segment'    => 4, // 'uri_label' => 'page' would have the same result
			'total_items'    => 254, // use db count query here of course
			'items_per_page' => 10, // it may be handy to set defaults for stuff like this in config/pagination.php
			'style'          => 'classic' // pick one from: classic (default), digg, extended, punbb, or add your own!
		));

		// Just echoing it is enough to display the links (__toString() rocks!)
		echo 'Classic style: '.$this->pagination;

		// You can also use the create_links() method and pick a style on the fly if you want
		echo '<hr />Digg style:     '.$this->pagination->create_links('digg');
		echo '<hr />Extended style: '.$this->pagination->create_links('extended');
		echo '<hr />PunBB style:    '.$this->pagination->create_links('punbb');
	}

}