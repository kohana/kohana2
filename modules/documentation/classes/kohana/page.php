<?php
/**
 * User guide page.
 *
 * $Id$
 *
 * @package    Documentation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Page_Core {

	public $language;
	public $path;
	public $metadata;
	public $output;
	public $headers;

	/**
	 * Create full URL to user guide page
	 *
	 * @param   string  uri to user guide page
	 * @param   string  language
	 * @return  string
	 */
	public static function url($uri, $language = NULL)
	{
		if ($language === NULL)
		{
			$language = isset(Router::$arguments['lang']) ? Router::$arguments['lang'] : 'en';
		}

		$uri = ltrim($uri, '/');

		if (preg_match('@^(?![a-z]+://).*@', $uri))
		{
			return url::site('docs/user_guide/'.$language.'/'.$uri);
		}

		return $uri;
	}

	public function __construct($language = 'en', $path = NULL, $data = array())
	{
		$this->language = $language;
		$this->path = $path;
		$path = 'kohana_docs/user_guide/' . $language . '/' . $path;

		$data['page'] = $this;
		$this->output = View::factory($path, $data)->render();
		$this->headers = $this->parse_headers($this->output);
	}

	/**
	 * Parse file contents to find all linkable headers
	 *
	 * @param   string  contents to parse
	 * @return  array
	 */
	protected function parse_headers($contents)
	{
		Benchmark::start('parse_headers');

		$headers = array();

		if (preg_match_all('/#{1,6}\s*(.+)\s*\{#(.+)}/', $contents, $matches))
		{
			if (count($matches) > 1)
			{
				foreach ($matches[1] as $num => $title)
				{
					$id = $matches[2][$num];
					$headers[$id] = $title;
				}
			}
		}
		Benchmark::stop('parse_headers');

		return $headers;
	}

	/**
	 * Create menu for current page
	 *
	 * @return  array
	 */
	public function menu()
	{
		$lang_menus = Kohana::lang('kohana_docs_menu');
		$page_menus = array();
		foreach ($lang_menus as $match => $menu)
		{
			if (preg_match('#^' . $match . '#', $this->path))
			{
				foreach ($menu as $group => $items)
				{
					foreach ($items as $url => $item)
					{
						$url = Kohana_Page::url($url);
						$page_menus[$group][$url] = $item;
					}
				}
			}
		}
		return $page_menus;
	}

	/**
	 * Create sidebar for current page
	 *
	 * @return  array
	 */
	public function sidebar()
	{
		$sidebar = array();

		// Create page contents
		$contents = array();
		foreach ($this->headers as $id => $title)
		{
			$id = '#'.$id;
			$contents[$id] = $title;
		}
		if ( ! empty($contents))
			$sidebar['Page Contents'] = $contents;

		// Create related links
		$related = array();
		if (isset($this->metadata['related']))
		{
			foreach ($this->metadata['related'] as $url => $title)
			{
				$url = Kohana_Page::url($url);
				$related[$url] = $title;
			}
		}
		if ( ! empty($related))
			$sidebar['Related Links'] = $related;

		// Create API links
		if ( ! empty($this->metadata['class']))
		{
			$methods = Kohana_Kodoc::class_methods($this->metadata['class']);

			foreach ($methods as $method)
			{
				$type = $method['static'] ? 'Static ' : '';
				$type .= ucfirst($method['visibility']).' Methods';
				$url = url::site('docs/api/class/'.$this->metadata['class'].'#'.$method['name']);
				$sidebar['API'][$type][$url] = $method['name'];
			}
		}

		return $sidebar;
	}

	/**
	 * Create navigation links for current page
	 *
	 * @return  array
	 */
	public function navigation_links()
	{
		$metadata = array
		(
			'status'    => 'stub',         // Completion status of the page
			'version'   => KOHANA_VERSION, // Version of Kohana it's compatible with
			'class'     => '',             // Class name the page contents relates to, used to generate API links
			'prev_page' => array(),        // URI to use in previous link
			'next_page' => array(),        // URI to use in next link
			'related'   => array()         // Links related to this page, can be an internal URI or a full URL
		);

		if (isset($this->metadata))
		{
			$metadata = array_merge($metadata, $this->metadata);
		}

		$navigation = array(
			'previous' => array(),
			'next'     => array()
		);

		if ( ! empty($metadata['prev_page']))
		{
			$metadata['prev_page'][0] = Kohana_Page::url($metadata['prev_page'][0]);
			$navigation['previous'] = $metadata['prev_page'];
		}
		if ( ! empty($metadata['next_page']))
		{
			$metadata['next_page'][0] = Kohana_Page::url($metadata['next_page'][0]);
			$navigation['next'] = $metadata['next_page'];
		}

		return $navigation;
	}

	/**
	 * Render current page
	 *
	 * @param   callback  renderer
	 * @return  string
	 */
	public function render($renderer = FALSE)
	{
		$output = $this->output;

		if ($renderer !== FALSE AND is_callable($renderer, TRUE))
		{
			// Pass the output through the user defined renderer
			$output = call_user_func($renderer, $output);
		}

		// Replace URLs in Markdown links with user guide URLS
		$matches = array(
			'@(?<!!)\[([^\]]+)\]\((?![a-z]+://)([^)]++)\)@',
			'@(?<!!)\[([^\]]+)\]:\s((?![a-z]+://)(\S+))@',
			'@!\[([^\]]+)\]\((?![a-z]+://)([^)]++)\)@'
		);

		$url = Kohana_Page::url('$2');
		$img_url = url::site('docs/img/$2');

		$replacements = array(
			'[$1]('.$url.')',
			'[$1]: '.$url,
			'![$1]('.$img_url.')'
		);

		$output = preg_replace($matches, $replacements, $output);

		// Load markdown
		require Kohana::find_file('vendor', 'Markdown');

		return Markdown($output);
	}
} // End Kohana_Page
