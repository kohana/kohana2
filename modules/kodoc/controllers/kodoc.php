<?php defined('SYSPATH') or die('No direct script access.');

class Kodoc_Controller extends Controller {

	protected $kodoc;

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

			if ($type === 'config')
			{
				if ($file === 'config')
				{
					// This file can only exist in one location
					$file = APPPATH.$type.'/config'.EXT;
				}
				else
				{
					foreach(array_reverse(Config::include_paths()) as $path)
					{
						if (is_file($path.$type.'/'.$file.EXT))
						{
							// Found the file
							$file = $path.$type.'/'.$file.EXT;
							break;
						}
					}
				}
			}
			else
			{
				// Get absolute path to file
				$file = Kohana::find_file($type, $file);
			}
		}
		else
		{
			// Nothing to document
			url::redirect('kodoc');
		}

		if (in_array($type, Kodoc::get_types()));
		{
			$this->kodoc = new Kodoc($type, $file);

			$content = new View('kodoc_html');

			print $content;
		}

		print Kohana::lang('core.stats_footer');
	}

}