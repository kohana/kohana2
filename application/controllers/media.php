<?php defined('SYSPATH') or die('No direct script access.');

class Media_Controller extends Controller {

	public function _remap()
	{
		$type = $this->uri->segment(2);
		$file = $this->uri->segment(3);

		try
		{
			echo new View('media/'.$type.'/'.$file);
		}
		catch (Kohana_Exception $e)
		{
			Kohana::show_404();
		}
	}

} // End Media_Controller