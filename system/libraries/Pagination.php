<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Pagination
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Pagination_Core {
	
	public $base_url           = '';
	public $style              = 'classic';
	public $uri_segment        = 3;
	public $items_per_page     = 10;
	
	public $current_page;
	public $total_pages;
	public $total_items;
	public $current_first_item;
	public $current_last_item;
	
	public $first_page;
	public $last_page;
	public $previous_page;
	public $next_page;

	/*
	 * Method: __construct
	 *  Sets up the config values
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	public function __construct($config = array())
	{
		// Merge all pagination config values
		$config = array_merge(Config::load('pagination', FALSE), (array) $config);
		
		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		
		// Set a default base_url if none given via config
		if ($this->base_url == '')
		{
			$this->base_url = (string) Kohana::instance()->uri;
		}
		
		// Explode base_url into segments
		$this->base_url = explode('/', trim($this->base_url, '/'));

		// Convert uri 'label' to corresponding integer if needed
		if (is_string($this->uri_segment))
		{
			if (($key = array_search($this->uri_segment, $this->base_url)) === FALSE)
			{
				// If uri 'label' is not found, auto add it to base_url
				$this->base_url[] = $this->uri_segment;
				$this->uri_segment = count($this->base_url) + 1;
			}
			else
			{
				$this->uri_segment = $key + 2;
			}
		}

		// Create a generic base_url with {page} placeholder
		$this->base_url[$this->uri_segment - 1] = '{page}';
		$this->base_url = url::site(implode('/', $this->base_url));
		
		// Core pagination values
		$this->total_items        = (int) max(0, $this->total_items);
		$this->items_per_page     = (int) max(1, $this->items_per_page);
		$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_page       = (int) min(max(1, Kohana::instance()->uri->segment($this->uri_segment)), max(1, $this->total_pages));
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
		
		// Helper variables
		// - first_page/last_page     FALSE if the current page is the first/last page
		// - previous_page/next_page  FALSE if that page doesn't exist relative to the current page
		$this->first_page         = ($this->current_page == 1) ? FALSE : 1;
		$this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
		$this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
		$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
		
		// Initialization done
		Log::add('debug', 'Pagination Library initialized');
	}

	/*
	 * Method: create_links
	 *  Generates the HTML for the chosen pagination style
	 *
	 * Parameters:
	 *  style - style of generated links
	 *
	 * Returns:
	 *  Generated pagination HTML
	 */
	public function create_links($style = NULL)
	{
		$style = (isset($style)) ? $style : $this->style;
		
		return (string) new View('pagination/'.$style, get_object_vars($this));
	}
	
	public function __toString()
	{
		return $this->create_links();
	}

	/*
	 * Method: url
	 *  Get the base_url with the specified page number
	 *
	 * Parameters:
	 *  page - page number
	 *
	 * Returns:
	 *  Base URL with specified page number
	 */
	public function url($page = NULL)
	{
		$page = (int) (isset($page)) ? $page : $this->current_page;
		
		return str_replace('{page}', $page, $this->base_url);
	}

	/*
	 * Method: sql_offset
	 *  Get the SQL offset of the first row to return
	 *
	 * Returns:
	 *  SQL offset
	 */
	public function sql_offset()
	{
		return (int) ($this->current_page - 1) * $this->items_per_page;
	}

	/*
	 * Method: url
	 *  Generate the complete SQL LIMIT clause
	 *
	 * Returns:
	 *  SQL LIMIT clause
	 */
	public function sql_limit()
	{
		return sprintf(' LIMIT %d OFFSET %d ', $this->items_per_page, $this->sql_offset());
	}

} // End Pagination Class