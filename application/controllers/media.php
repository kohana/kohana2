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
			// Send the 404 header
			header('HTTP/1.1 404 File Not Found');

			// Display a comment, valid for both JS and CSS
			print '/* No file found */';
		}
	}

} // End Media_Controller