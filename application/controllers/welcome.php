<?php

class Welcome_Controller extends Controller {

	function index()
	{
		if (0 === FALSE)
			echo 'yea';
		foreach(get_class_methods(__CLASS__) as $method)
		{
			if ( ! preg_match('/_example$/', $method)) continue;
				echo html::anchor('welcome/'.$method, $method)."<br/>\n";
		}
	}

	function session_example()
	{
		$this->load->database();
		$s = new Session();

		print "SESSID: <pre>".session_id()."</pre>\n";

		print "<pre>".print_r($_SESSION, TRUE)."</pre>\n";

		print "<br/>{execution_time} seconds";
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
		$this->load->database();

		$query = $this->db->select('title')->from('pages')->get();

		$query->result();

		foreach($query as $item)
		{
			echo '<pre>'.print_r($item, true).'</pre>';
		}
		print "Numrows: ".$query->num_rows()."<br/>";

		echo '<pre>'.print_r($this->db->list_tables(), TRUE).'</pre>';
		echo $this->db->table_exists('pages');

		$query = $this->db->select('title')->from('pages')->get();
		print "<br />Numrows: ".$query->num_rows()."<br/>";

		$sql = 'SELECT * FROM pages WHERE id = ?';
		$query = $this->db->query($sql, array(49));
		echo 'OBJECT:<pre>'.print_r($query->result(TRUE), TRUE).'</pre>';
		echo $this->db->last_query();
		
		$sql = 'SELECT * FROM pages WHERE id = ?';
		$query = $this->db->query($sql, array(49));
		echo '<br />ARRAY:<pre>'.print_r($query->result(FALSE), TRUE).'</pre>';
		echo $this->db->last_query();
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