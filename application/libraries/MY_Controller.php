<?php defined('SYSPATH') or die('No direct script access.');

class Controller extends Controller_Core {

	// Use auto-rendering, defaults to false
	protected $auto_render = FALSE;

	// Main template
	protected $template = 'layout';

	// RSS feeds
	protected $feeds = array
	(
		'forums' => 'http://kohanaphp.com/forums/index.php?action=.xml;limit=3;type=rss2',
		'trac'   => 'http://kohanaphp.com/trac/timeline?milestone=on&ticket=on&changeset=on&max=3&daysback=90&format=rss'
	);

	public function __construct()
	{
		parent::__construct();

		if ($this->uri->segment(1) == FALSE)
		{
			// Need the first segment so that the main menu has an active tab
			url::redirect('home');
		}

		$cache = APPPATH.'cache/';

		if ( ! is_writable($cache))
		{
			throw new Kohana_User_Exception
			(
				'Cache Unwritable',
				'Please make the application/cache directory writable!'
			);
		}

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

			// Feed caching
			foreach($this->feeds as $name => $link)
			{
				$filename = $cache.$name.'.xml';

				// Cache the feed for 2 hours
				if ( ! file_exists($filename) OR (time() - 7200) > filemtime($filename))
				{
					file_put_contents($filename, file_get_contents($link));
				}

				// Add the feed to the template
				$feeds[$name] = feed::parse($filename, 3);
			}

			$this->template->sidebar->feeds = $feeds;

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