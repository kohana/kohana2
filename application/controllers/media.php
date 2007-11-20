<?php defined('SYSPATH') or die('No direct script access.');

class Media_Controller extends Controller {

	public function _remap()
	{
		try
		{
			echo new View($this->uri->string());
		}
		catch (Kohana_Exception $e)
		{
			throw new Kohana_404_Exception();
		}
	}

} // End Media_Controller