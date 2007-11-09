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

	public function download($filename = FALSE)
	{
		if ($filename == FALSE OR ! file_exists(APPPATH.'views/media/downloads/'.$filename))
			url::redirect('tutorials');

		// Disable auto rendering
		$this->auto_render = FALSE;

		// Download the file
		download::force(APPPATH.'views/media/downloads/'.$filename);
	}

} // End Tutorial_Controller