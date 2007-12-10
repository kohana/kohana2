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

			// Feed caching
			foreach($this->feeds as $name => $data)
			{
				$cache_id = 'feed--'.$name;

				if (($feed = $this->cache->get($cache_id)) == FALSE)
				{
					// Initialize cURL
					$curl = curl_init();

					// Set cURL options
					curl_setopt($curl, CURLOPT_URL, $data['url']); // Remote feed location
					curl_setopt($curl, CURLOPT_HEADER, 0);          // No headers in fetched page
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  // Return the fetched page, instead of printing it
					curl_setopt($curl, CURLOPT_TIMEOUT, 3);         // Five second timeout

					// Fetch the remote feed
					$feed = curl_exec($curl);

					if (curl_errno($curl) === CURLE_OK)
					{
						// Cache the content if there was no error
						$this->cache->set($cache_id, $feed, array('feed'));
					}
					else
					{
						// Log fetching errors
						Log::add('error', 'Error fetching remote feed ('.$data['url'].'): '.curl_error($curl));
					}
				}

				$this->feeds[$name]['items'] = feed::parse($feed, 3);
			}

			// Add the feeds to the sidebar
			$this->template->sidebar->feeds = $this->feeds;

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