<?php

class Welcome_Controller extends Controller {

	function index()
	{
		//print_r(date::hours(1, TRUE)); die
		
		foreach(get_class_methods(__CLASS__) as $method)
		{
			if ( ! preg_match('/_example$/', $method)) continue;
				echo html::anchor('welcome/'.$method, $method)."<br/>\n";
		}
	}

	function form_example()
	{
		print form::open();

		print form::label('test', 'this is a test').form::checkbox('test', '1');

		print form::close();
	}

	function validation_example()
	{
		// To demonstrate Validation being able to validate any array, I will
		// be using a pre-built array. When you load validation with no arguments
		// it will default to validating the POST array.
		$data = array
		(
			'user' => 'hello',
			'pass' => 'bigsecret',
			'reme' => '1'
		);

		// Same as CI, but supports passing an array to the constructor
		$this->load->library('validation', $data);

		// Looks familiar...
		$this->validation->set_rules(array
		(
			// Format:
			// key          friendly name,  validation rules
			'user' => array('username',    'trim|required[1,2]'),
			'pass' => array('password',    'required|sha1'),
			'reme' => array('remember me', 'required')
		));

		// Same syntax as before
		$this->validation->run();

		// Same syntax, but dynamcially generated wth __get()
		print $this->validation->user_error;

		// Yay!
		print "{execution_time} ALL DONE!";
	}

	function database_example()
	{
		$this->load->library('database');
		$this->load->database();

		$query = $this->database->from('pages')->get();

		echo '<pre>' . print_r($query, true) . '</pre>';
		$query->result();

		echo 'Current: ' . $query->current()->title . '<br />';
		echo 'Next: ' . $query->next()->title . '<br />';
		echo 'Next: ' . $query->next()->title . '<br />';

		foreach ($query as $row)
		{
			echo print_r($row);
		}

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
		echo "done in {execution_time} seconds";
	}
	function user_agent_example()
	{
		$this->load->library('user_agent');
		echo $this->user_agent;
	}
	function calendar_example()
	{
		$this->load->library('calendar');
		echo $this->calendar->generate();
	}
	

}