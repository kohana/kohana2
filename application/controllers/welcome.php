<?php

class Welcome_Controller extends Controller {

	function index()
	{
		foreach(get_class_methods(__CLASS__) as $method)
		{
			if ( ! preg_match('/_example$/', $method)) continue;
				echo html::anchor('welcome/'.$method, $method)."<br/>\n";
		}
	}

	function form_example()
	{
		$this->load->library('validation');

		print form::open('', array('enctype' => 'multipart/form-data'));

		print form::label('imageup', 'Image Uploads').':<br/>';
		print form::upload('imageup').'<br/>';
		// print form::upload('imageup[]').'<br/>';
		// print form::upload('imageup[]').'<br/>';
		print form::submit('upload', 'Upload!');

		print form::close();

		if ( ! empty($_POST))
		{
			$this->validation->set_rules('imageup', 'required|upload[gif,png,jpg,500K]', 'Image Upload');
			print '<p>validation result: '.var_export($this->validation->run(), TRUE).'</p>';
		}

		print $this->validation->debug();
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

		$query = $this->database->select('title')->from('pages')->get();

		$query->result();

		foreach($query as $item)
		{
			print_r($item);
		}

		die;

		print "Numrows: ".$query->num_rows()."<br/>";
		print "<pre>".print_r($this->database, TRUE)."</pre><br/>";

		$query = $this->database->select('title')->from('pages')->get();
		print "Numrows: ".$query->num_rows()."<br/>";
		print "<pre>".print_r($this->database, TRUE)."</pre><br/>";

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

		foreach(array('agent', 'browser', 'version') as $key)
		{
			print $key.': '.$this->user_agent->$key.'<br/>'."\n";
		}

		print "<br/><br/>\n";
		print "done in {execution_time} seconds";
	}

	function calendar_example()
	{
		$this->load->library('calendar');
		echo $this->calendar->generate();
	}

	function image_example()
	{
		$config['source_image'] = '/var/www/dev/kohana/uploads/haha.JPG';
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = 75;
		$config['height'] = 50;
		$this->load->library('image_lib', $config);
		if ( ! $this->image_lib->resize())
		{
		    echo $this->image_lib->display_errors();
		}
	}


}