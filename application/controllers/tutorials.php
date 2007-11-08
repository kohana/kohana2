<?php defined('SYSPATH') or die('No direct script access.');

class Tutorials_Controller extends Controller {

	protected $auto_render = TRUE;

	public function _default()
	{
		// Include Geshi syntax highlighter
		include Kohana::find_file('vendor', 'geshi/geshi');

		try
		{
			// Attempt to load a tutorial
			$this->template->content = new View('pages/tutorials/'.$this->uri->segment(2));
		}
		catch(Kohana_Exception $e)
		{
			// Load the index page instead
			$this->template->content = new View('pages/tutorials/index');
		}
	}

} // End Tutorial_Controller