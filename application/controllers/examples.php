<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Contains examples of various Kohana library examples. You can access these
 * samples in your own installation of Kohana by going to ROOT_URL/examples.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Examples_Controller extends Controller {

	/**
	 * Displays a list of available examples
	 */
	function index()
	{
		// Get the methods that are only in this class and not the parent class.
		$examples = array_diff
		(
			get_class_methods(__CLASS__),
			get_class_methods(get_parent_class($this))
		);

		sort($examples);

		echo "<strong>Examples:</strong>\n";
		echo "<ul>\n";

		foreach($examples as $method)
		{
			if ($method == __FUNCTION__)
				continue;

			echo '<li>'.html::anchor('examples/'.$method, $method)."</li>\n";
		}

		echo "</ul>\n";
		echo '<p>'.Kohana::lang('core.stats_footer')."</p>\n";
	}

	public function archive($build = FALSE)
	{
		if ($build === 'build')
		{
			// Load archive
			$archive = new Archive('zip');

			// Download the application/views directory
			$archive->add(APPPATH.'views/', 'app_views/', TRUE);

			// Download the built archive
			$archive->download('test.zip');
		}
		else
		{
			echo html::anchor(Router::$current_uri.'/build', 'Download views');
		}
	}

	/**
	 * Demonstrates how to use views inside of views.
	 */
	function template()
	{
		$data = array
		(
			'title'   => 'View-in-View Example',
			'content' => 'This is my view-in-view page content.',
			'copyright' => '&copy; 2007 Kohana Team'
		);

		$view = $this->load->view('viewinview/container', $data);
		$view->header = $this->load->view('viewinview/header', $data);

		$view->render(TRUE);
	}

	/**
	 * Demonstrates how to parse RSS feeds by using DOMDocument.
	 */
	function rss()
	{
		// Parse an external atom feed
		$feed = feed::parse('http://codeigniter.com/feeds/atom/news/');

		// Show debug info
		echo Kohana::debug($feed);

		echo Kohana::lang('core.stats_footer');
	}

	/**
	 * Demonstrates the Session library and using session data.
	 */
	function session()
	{
		$s = new Session();

		echo 'SESSID: <pre>'.session_id()."</pre>\n";

		echo '<pre>'.print_r($_SESSION, TRUE)."</pre>\n";

		echo '<br/>{execution_time} seconds';
	}

	/**
	 * Demonstrates how to use the form helper with the Validation library.
	 */
	function form()
	{
		$this->load->library('validation');

		echo form::open('', array('enctype' => 'multipart/form-data'));

		echo form::label('imageup', 'Image Uploads').':<br/>';
		echo form::upload('imageup[]').'<br/>';
		echo form::upload('imageup[]').'<br/>';
		echo form::upload('imageup[]').'<br/>';
		echo form::submit('upload', 'Upload!');

		echo form::close();

		if ( ! empty($_POST))
		{
			$this->validation->set_rules('imageup', 'required|upload[gif,png,jpg,500K]', 'Image Upload');
			echo '<p>validation result: '.var_export($this->validation->run(), TRUE).'</p>';
		}

		echo Kohana::debug($this->validation);
		echo Kohana::lang('core.stats_footer');
	}

	/**
	 * Demontrates how to use the Validation library to validate an arbitrary array.
	 */
	function validation()
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
			'user' => array('username',    '=trim|required[1,12]|regex[/[0-9]+/]'),
			'pass' => array('password',    'required|=sha1'),
			'reme' => array('remember me', 'required')
		));

		// Same syntax as before
		$this->validation->run();

		// Same syntax, but dynamcially generated wth __get()
		echo $this->validation->error_string;

		// Yay!
		echo '{execution_time} ALL DONE!';
	}

	/**
	 * Demonstrates the features of the Database library.
	 *
	 * Table Structure:
	 *  CREATE TABLE `pages` (
	 *  `id` mediumint( 9 ) NOT NULL AUTO_INCREMENT ,
	 *  `page_name` varchar( 100 ) NOT NULL ,
	 *  `title` varchar( 255 ) NOT NULL ,
	 *  `content` longtext NOT NULL ,
	 *  `menu` tinyint( 1 ) NOT NULL default '0',
	 *  `filename` varchar( 255 ) NOT NULL ,
	 *  `order` mediumint( 9 ) NOT NULL ,
	 *  `date` int( 11 ) NOT NULL ,
	 *  `child_of` mediumint( 9 ) NOT NULL default '0',
	 *  PRIMARY KEY ( `id` ) ,
	 *  UNIQUE KEY `filename` ( `filename` )
	 *  ) ENGINE = MYISAM DEFAULT CHARSET = utf8 PACK_KEYS =0;
	 *
	*/
	function database()
	{
		$this->load->database();
		$table = 'pages';
		echo 'Does the '.$table.' table exist? ';
		if ($this->db->table_exists($table))
		{
			echo '<p>YES! Lets do some work =)</p>';

			$query = $this->db->select('DISTINCT pages.*')->from($table)->get();
			echo $this->db->last_query();
			echo '<h3>Iterate through the result:</h3>';
			foreach($query as $item)
			{
				echo '<p>'.$item->title.'</p>';
			}
			echo '<h3>Numrows using count(): '.count($query).'</h3>';
			echo 'Table Listing:<pre>'.print_r($this->db->list_tables(), TRUE).'</pre>';

			echo '<h3>Try Query Binding with objects:</h3>';
			$sql = 'SELECT * FROM '.$table.' WHERE id = ?';
			$query = $this->db->query($sql, array(1));
			echo '<p>'.$this->db->last_query().'</p>';
			$query->result(TRUE);
			foreach($query as $item)
			{
				echo '<pre>'.print_r($item, true).'</pre>';
			}

			echo '<h3>Try Query Binding with arrays (returns both associative and numeric because I pass MYSQL_BOTH to result():</h3>';
			$sql = 'SELECT * FROM '.$table.' WHERE id = ?';
			$query = $this->db->query($sql, array(1));
			echo '<p>'.$this->db->last_query().'</p>';
			$query->result(FALSE, MYSQL_BOTH);
			foreach($query as $item)
			{
				echo '<pre>'.print_r($item, true).'</pre>';
			}

			echo '<h3>Look, we can also manually advance the result pointer!</h3>';
			$query = $this->db->select('title')->from($table)->get();
			echo 'First:<pre>'.print_r($query->current(), true).'</pre><br />';
			$query->next();
			echo 'Second:<pre>'.print_r($query->current(), true).'</pre><br />';
			$query->next();
			echo 'Third:<pre>'.print_r($query->current(), true).'</pre>';
			echo '<h3>And we can reset it to the beginning:</h3>';
			$query->rewind();
			echo 'Rewound:<pre>'.print_r($query->current(), true).'</pre>';

			echo '<p>Number of rows using count_records(): '.$this->db->count_records('pages').'</p>';
		}
		else
		{
			echo 'NO! The '.$table.' table doesn\'t exist, so we can\'t continue =( ';
		}
		echo "<br/><br/>\n";
		echo 'done in {execution_time} seconds';
	}

	/**
	 * Demonstrates how to use the Pagination library and Pagination styles.
	 */
	function pagination()
	{
		$this->pagination = new Pagination(array(
			// 'base_url'    => 'welcome/pagination_example/page/', // base_url will default to current uri
			'uri_segment'    => 'page', // pass a string as uri_segment to trigger former 'label' functionality
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
		echo 'done in {execution_time} seconds';
	}

	/**
	 * Demonstrates the User_Agent library.
	 */
	function user_agent()
	{
		$this->load->library('user_agent');

		foreach(array('agent', 'browser', 'version') as $key)
		{
			echo $key.': '.$this->user_agent->$key.'<br/>'."\n";
		}

		echo "<br/><br/>\n";
		echo 'done in {execution_time} seconds';
	}

	/**
	 * Demonstrates the Payment library.
	 */
	/*function payment()
	{
		$credit_card = new Payment();

		// You can also pass the driver name to the library to use multiple ones:
		$credit_card = new Payment('Paypal');
		$credit_card = new Payment('Authorize');

		// You can specify one parameter at a time:
		$credit_card->login = 'this';
		$credit_card->first_name = 'Jeremy';
		$credit_card->last_name = 'Bush';
		$credit_card->card_num = '1234567890';
		$credit_card->exp_date = '0910';
		$credit_card->amount = '478.41';

		// Or you can also set fields with an array and the <Payment.set_fields> method:
		$credit_card->set_fields(array('login' => 'test',
		                               'first_name' => 'Jeremy',
		                               'last_name' => 'Bush',
		                               'card_num' => '1234567890',
		                               'exp_date' => '0910',
		                               'amount' => '487.41'));

		echo '<pre>'.print_r($credit_card, true).'</pre>';

		echo 'Success? ';
		echo ($response = $credit_card->process() == TRUE) ? 'YES!' : $response;
	}*/

	function calendar()
	{
		$profiler = new Profiler;

		$cal = new Calendar(5, 2007);

		echo $cal->render();
	}

	function image()
	{
		$profiler = new Profiler;

		// Upload directory
		$dir = realpath(DOCROOT.'upload').'/';

		// Image filename
		$image = $dir.'mypic.jpg';

		// Create an instance of Image, with file
		$image = new Image($image);

		// Resize the image
		$image->resize(100, 100);

		// Save the image
		$image->save($dir.'mypic_thumb.jpg');

		echo Kohana::debug($image);
	}

} // End Examples
