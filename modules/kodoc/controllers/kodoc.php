<?php defined('SYSPATH') or die('No direct script access.');

class Kodoc_Controller extends Controller {

	public function index()
	{
		print new View('kodoc_menu');
	}

	public function _default()
	{
		if (count($segments = $this->uri->segment_array(1)) > 1)
		{
			// Find directory (type) and filename
			$type = array_shift($segments);
			$file = implode('/', $segments);

			if (substr($file, -(strlen(EXT))) === EXT)
			{
				// Remove extension
				$file = substr($file, 0, -(strlen(EXT)));
			}

			// Get absolute path to file
			$file = Kohana::find_file($type, $file);
		}
		else
		{
			// Nothing to document
			url::redirect('kodoc');
		}

		if (in_array($type, Kodoc::get_types()));
		{
			$docs = new Kodoc($type, $file);
			print "Debug for $file: ".Kohana::debug($docs->get());
		}

		print Kohana::lang('core.stats_footer');
	}

}