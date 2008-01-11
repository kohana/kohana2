<?php defined('SYSPATH') or die('No direct script access.');

class Controller extends Controller_Core {

	// Enable auth
	protected $auth_required = FALSE;

	// Use auto-rendering, defaults to false
	protected $auto_render = FALSE;

	// Main template
	protected $template = 'layout';

	// Cache instance
	protected $cache;

	// RSS feeds
	protected $feeds = array
	(
		'forums' => array
		(
			'title' => 'Latest Forum Activity',
			'url'   => 'http://forum.kohanaphp.com/index.php?action=.xml;limit=20;type=rss2',
			'items' => array()
		),
		'trac' => array
		(
			'title' => 'Latest Changes',
			'url'   => 'http://trac.kohanaphp.com/timeline?milestone=on&ticket=on&changeset=on&max=20&daysback=90&format=rss',
			'items' => array()
		)
	);

	public function __construct()
	{
		parent::__construct();

		if ($this->uri->segment(1) == FALSE)
		{
			// Need the first segment so that the main menu has an active tab
			url::redirect('home');
		}

		if ($this->auto_render == TRUE)
		{
			// Load cache
			$this->cache = new Cache;

			// Load session
			$this->session = new Session;

			// Load database
			$this->db = new Database('website');

			// Load the template
			$this->template = new View($this->template);

			// Menu items
			$this->template->menu = array
			(
				'home'       => 'Home',
				'download'   => 'Download',
				'tutorials'  => 'Tutorials',
				// External links
				'http://forum.kohanaphp.com/' => 'Forum',
				'http://doc.kohanaphp.com/' => 'User Guide',
				// 'http://api.kohanaphp.com/'   => 'API Manual',
			);

			// Sidebar
			$this->template->sidebar = new View('sidebar');

			foreach($this->feeds as $name => $data)
			{
				// Load the feed from cache
				$feed = $this->cache->get('feed--'.$name);

				if (empty($feed))
				{
					// Queue the load feed for loading
					$this->_load_feed('feed--'.$name, $data['url']);
				}

				$this->feeds[$name]['items'] = empty($feed) ? array() : feed::parse($feed, 3);
			}

			// Add the feeds to the sidebar
			$this->template->sidebar->feeds = $this->feeds;

			// Auto-rendering
			Event::add('system.post_controller', array($this, '_display'));

			// Load feeds after display
			Event::add('system.shutdown', array($this, '_load_feed'));
		}
	}

	public function _display()
	{
		if ($this->auto_render == TRUE)
		{
			$this->template->render(TRUE);
		}
	}

	public function _load_feed($id = NULL, $url = NULL)
	{
		static $feeds;

		is_array($feeds) or $feeds = array();

		if (empty($id) AND empty($url))
		{
			// Disable error reporting
			$ER = error_reporting(0);

			// Send all current output
			while (ob_get_level() > 0) ob_end_flush();

			// Flush the buffer
			flush();

			foreach ($feeds as $id => $feed)
			{
				if ($feed = file_get_contents($feed))
				{
					// Cache the retrieved feed
					$this->cache->set($id, $feed);
				}
			}

			// Restore error reporting
			error_reporting($ER);
		}
		else
		{
			// Add the URL to the feeds
			$feeds[$id] = $url;
		}
	}

} // End Controller