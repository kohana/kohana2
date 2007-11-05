<?php defined('SYSPATH') or die('No direct script access.');

class Controller extends Controller_Core {

	// Use auto-rendering, defaults to false
	protected $auto_render = FALSE;

	// Main template
	protected $template = 'layout';

	public function __construct()
	{
		parent::__construct();

		if ($this->auto_render == TRUE)
		{
			// Load the template
			$this->template = new View($this->template);

			// Menu items
			$this->template->menu = array
			(
				'home'       => 'Home',
				'download'   => 'Download',
				'tutorials'  => 'Tutorials',
				'forums'     => 'Forums',
				'user_guide' => 'User Guide'
			);

			// Sidebar
			$this->template->sidebar = new View('sidebar');

			// Auto-rendering
			Event::add('system.post_controller', array($this, '_display'));
		}
	}

	public function _display()
	{
		if ($this->auto_render == TRUE)
		{
			$this->template->render(TRUE);
		}
	}

} // End Controller