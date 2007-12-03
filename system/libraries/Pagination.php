<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Pagination
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Pagination_Core {

	// Config values
	protected $base_url       = '';
	protected $directory      = 'pagination';
	protected $style          = 'classic';
	protected $uri_segment    = 3;
	protected $items_per_page = 10;

	// Automatically calculated values
	protected $current_page;
	protected $total_pages;
	protected $total_items;
	protected $current_first_item;
	protected $current_last_item;
	protected $first_page;
	protected $last_page;
	protected $previous_page;
	protected $next_page;

	/**
	 * Constructor: __construct
	 *  Sets up the config values.
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	public function __construct($config = array())
	{
		// Load configuration
		$config += Config::item('pagination', FALSE, FALSE);

		$this->initialize($config);

		Log::add('debug', 'Pagination Library initialized');
	}

	/**
	 * Method: initialize
	 *  Sets or overwrites (some) config values.
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	public function initialize($config = array())
	{
		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}

		// Clean view directory
		$this->directory = trim($this->directory, '/').'/';

		// Don't bother about full base_urls
		if (strpos($this->base_url, '://') === FALSE)
		{
			// Default base_url
			if ($this->base_url == '')
			{
				$this->base_url = url::current();
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
		}

		// Core pagination values
		$this->total_items        = (int) max(0, $this->total_items);
		$this->items_per_page     = (int) max(1, $this->items_per_page);
		$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_page       = (int) min(max(1, Kohana::instance()->uri->segment($this->uri_segment)), max(1, $this->total_pages));
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);

		// If there is no first/last/previous/next page, relative to the
		// current page, value is set to FALSE. Valid page number otherwise.
		$this->first_page         = ($this->current_page == 1) ? FALSE : 1;
		$this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
		$this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
		$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
	}

	/**
	 * Method: create_links
	 *  Generates the HTML for the chosen pagination style.
	 *
	 * Parameters:
	 *  style - style of generated links
	 *
	 * Returns:
	 *  Generated pagination HTML.
	 */
	public function create_links($style = NULL)
	{
		$style = (isset($style)) ? $style : $this->style;

		$view = new View($this->directory.$style, get_object_vars($this));
		return $view->render();
	}

	/**
	 * Method: __toString
	 *  Magic method for converting an object to a string.
	 *
	 * Returns:
	 *  The generated pagination HTML.
	 */
	public function __toString()
	{
		return $this->create_links();
	}

	/**
	 * Method: sql_offset
	 *  Gets the SQL offset of the first row to return.
	 *
	 * Returns:
	 *  SQL offset integer.
	 */
	public function sql_offset()
	{
		return (int) ($this->current_page - 1) * $this->items_per_page;
	}

	/**
	 * Method: sql_limit
	 *  Generates the complete SQL LIMIT clause.
	 *
	 * Returns:
	 *  SQL LIMIT clause.
	 */
	public function sql_limit()
	{
		return sprintf(' LIMIT %d OFFSET %d ', $this->items_per_page, $this->sql_offset());
	}

} // End Pagination Class