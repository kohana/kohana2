<?php defined('SYSPATH') or die('No direct access allowed.');
/* $Id$ */

class User_Guide_Controller extends Controller {

	public function __construct()
	{
		parent::__construct();

		$this->lang = array
		(
			'benchmark' => array
			(
				'total_execution_time' => 'Total execution time of the application, starting at the earliest possible point',
				'base_classes_loading' => 'Time to load the core classes that are required for Kohana to run'
			),
			'event' => array
			(
				'system.ready'    => 'Basic system is prepared, but no routing has been performed',
				'system.shutdown' => 'Last event called before Kohana stops processing the current request'
			)
		);
	}

	public function _remap()
	{
		$category = strtolower($this->uri->segment(2));
		$section  = strtolower($this->uri->segment(3));

		// Media resource loading
		if ($category === 'js' OR $category === 'css')
		{
			return $this->$category($section);
		}

		// For Kohana's custom tags to be handled properly
		Event::add('system.pre_output', array($this, '_tags'));

		// Set the view that will be loaded
		$category = ($category == FALSE)  ? 'kohana' : $category;
		$content  = rtrim('user_guide/content/'.$category.'/'.$section, '/');

		// Load session for AJAX page storage
		$this->load->library('session');

		// Load markdown
		require Kohana::find_file('vendor', 'Markdown');

		// Show content
		if ($this->input->get('ajax') === 'true')
		{
			// Set the AJAX return page, for refreshing
			$this->session->set('ajax_return', $this->uri->string());

			// Just the content, ma'am!
			$this->load->view($content)->render(TRUE, 'Markdown');
		}
		else
		{
			// Return the user to the page they were on
			if ($ajax_return = $this->session->get_once('ajax_return'))
				url::redirect($ajax_return);
			
			$this->data['menu'] = $this->load->view('user_guide/menu', array('active_category' => $category, 'active_section' => $section));
			$this->data['content'] = $this->load->view($content)->render(FALSE, 'Markdown');

			// Display output
			$this->load->view('user_guide/template', $this->data)->render(TRUE);
		}
	}

	public function _tags()
	{
		self::$output = preg_replace_callback('!<(benchmark|event|file|definition)>.+?</[^>]+>!', array($this, '_tag_update'), self::$output);
	}

	public function _tag_update($match)
	{
		preg_match('!^<([^>]+)>(.+?)</[^>]+>$!', $match[0], $tag);

		$type = $tag[2];
		$tag  = $tag[1];

		switch($tag)
		{
			case 'definition':
				return html::anchor('user_guide/general/definitions?search='.$type, $type);
			case 'file':
				return '<tt class="filename">'.$type.EXT.'</tt>';
			case 'benchmark':
				return isset($this->lang['benchmark'][$type]) ? '<abbr title="Benchmark: '.$this->lang['benchmark'][$type].'">'.$type.'</abbr>' : $type;
			case 'event':
				return isset($this->lang['event'][$type]) ? '<abbr title="Event: '.$this->lang['event'][$type].'">'.$type.'</abbr>' : $type;
		}
	}

	public function js($filename)
	{
		header('Content-type: text/javascript');

		$this->_media('js', preg_replace('/\.js$/D', '', $filename));
	}

	public function css($filename)
	{
		header('Content-type: text/css');

		$this->_media('css', preg_replace('/\.css$/D', '', $filename));
	}

	private function _media($type, $filename)
	{
		/**
		 * @todo Enable Caching
		 */
		try
		{
			$this->load->view('user_guide/'.$type.'/'.$filename)->render(TRUE);
		}
		catch (Kohana_Exception $exception)
		{
			print '/* script not found */';
		}
	}

} // End User_guide Controller