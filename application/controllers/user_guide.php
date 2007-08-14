<?php defined('SYSPATH') or die('No direct access allowed.');

class User_guide extends Controller {

	function index()
	{
		include Kohana::find_file('vendor', 'markdown');

		$view['menu'] = markdown($this->load->view('user_guide/menu')->render());

		$this->load->view('user_guide/template', $view)->render(TRUE);
	}

	function js($filename)
	{
		header('Content-type: text/javascript');

		$this->_media('js', preg_replace('/\.js$/u', '', $filename));
	}

	function css($filename)
	{
		header('Content-type: text/css');

		$this->_media('css', preg_replace('/\.css$/u', '', $filename));
	}

	private function _media($type, $filename)
	{
		try
		{
			$this->load->view('user_guide/'.$type.'/'.$filename)->render(TRUE);
		}
		catch (file_not_found $exception)
		{
			print '/* script not found */';
		}
	}


} // End User_guide Controller