<?php defined('SYSPATH') or die('No direct script access.');

class Main_Controller extends Controller {

	protected $layout;

	public function __construct()
	{
		parent::__construct();

		// Template
		$this->layout = new View('layout');

		// Menu items
		$this->layout->menu = array
		(
			'home'       => 'Home',
			'download'   => 'Download',
			'tutorials'  => 'Tutorials',
			'forums'     => 'Forums',
			'user_guide' => 'User Guide'
		);

		// Sidebar
		$this->layout->sidebar = new View('sidebar');
	}

	public function _default($page = 'index')
	{
		$this->layout->page = ($page == 'index') ? 'home' : $page;

		$this->layout->content = new View('pages/'.$this->layout->page);

		$this->layout->render(TRUE);
	}

	public function media($type = '', $file = '')
	{
		try
		{
			echo new View('media/'.$type.'/'.$file);die;
		}
		catch (Kohana_Exception $e)
		{
			echo '/* No file found */';
		}
	}

} // End Kohana Website Controller