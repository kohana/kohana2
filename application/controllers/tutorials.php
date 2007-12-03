<?php defined('SYSPATH') or die('No direct script access.');

class Tutorials_Controller extends Controller {

	protected $auto_render = TRUE;

	public function _default()
	{
		$titles = array
		(
			'Video Tutorials' => array
			(
				'video/hello_world' => 'Hello World Introduction',
				'video/hello_world_part_2' => 'Hello World, Part 2'
			),
			'Security' => array
			(
				'xss' => 'XSS Attack Filtering',
			),
			'Static Content' => array
			(
				'page' => 'Creating a Page Controller',
			),
			'Forms and Validation' => array
			(
				'model_validation' => 'Built-in Model Validation',
				'quick_forms' => 'Quick Form Generation'
			),
			'Advanced Tutorials' => array
			(
				'remove_index' => 'Removing index.php From URLs',
				'multilingual' => 'Setting Up a Multilingual Website'
			)
		);

		// Include Geshi syntax highlighter
		include Kohana::find_file('vendor', 'geshi/geshi');

		try
		{
			$tut   = $this->uri->segment(2);
			$title = 'Tutorial';

			foreach($titles as $heading => $group)
			{
				if (isset($group[$tut]))
				{
					$title = $group[$tut];
					break;
				}
			}

			// Attempt to load a tutorial
			$this->template->set(array
			(
				'title'   => $title,
				'content' => new View('pages/tutorials/'.$tut)
			));
		}
		catch (Kohana_Exception $e)
		{
			// Load the index page instead
			$this->template->set(array
			(
				'title'   => 'Tutorials',
				'content' => new View('pages/tutorials/index', array('titles' => $titles))
			));
		}
	}

	public function video($name)
	{
		$video = new Video_Tutorial_Model($this->uri->segment(3));

		// Change the video to a real URL
		$video->video = url::base(FALSE).'video/'.$video->video.'.swf';

		// Set the video contents to the player
		$player = new View('pages/tutorials/video', $video->as_array());

		$this->template->title   = $video->title;
		$this->template->content = $player->render();
	}

	public function download($filename = FALSE)
	{
		// Disable auto rendering
		$this->auto_render = FALSE;

		if ($filename == FALSE OR ! file_exists(APPPATH.'views/media/downloads/'.$filename))
			url::redirect('tutorials');

		// Download the file
		download::force(APPPATH.'views/media/downloads/'.$filename);
	}

} // End Tutorial_Controller