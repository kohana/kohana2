<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pagination library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Pagination_Core {

	// Config values
	protected $base_url       = '';
	protected $directory      = 'pagination';
	protected $style          = 'classic';
	protected $uri_segment    = 3;
	protected $query_string   = '';
	protected $items_per_page = 10;
	protected $total_items    = 0;
	protected $auto_hide      = FALSE;

	// Automatically generated values
	protected $url;
	protected $current_page;
	protected $total_pages;
	protected $current_first_item;
	protected $current_last_item;
	protected $first_page;
	protected $last_page;
	protected $previous_page;
	protected $next_page;
	protected $sql_offset;
	protected $sql_limit;

	/**
	 * Constructs and returns a new Pagination object.
	 *
	 * @param   array  configuration
	 * @return  object
	 */
	public function factory($config = array())
	{
		return new Pagination($config);
	}

	/**
	 * Constructs the Pagination object.
	 *
	 * @param   array  configuration
	 * @return  void
	 */
	public function __construct($config = array())
	{
		// Load configuration
		$config += Config::item('pagination', FALSE, FALSE);

		$this->initialize($config);

		Log::add('debug', 'Pagination Library initialized');
	}

	/**
	 * Sets or overwrites (some) config values.
	 *
	 * @param   array  configuration
	 * @return  void
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

		// Build generic URL with page in query string
		if ($this->query_string !== '')
		{
			// Extract current page
			$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;

			// Insert {page} placeholder
			$_GET[$this->query_string] = '{page}';

			// Create full URL
			$this->url = url::site(Router::$current_uri).'?'.str_replace('%7Bpage%7D', '{page}', http_build_query($_GET));
		}

		// Build generic URL with page as URI segment
		else
		{
			// Use current URI if no base_url set
			$this->url = ($this->base_url === '') ? Router::$segments : explode('/', trim($this->base_url, '/'));

			// Convert uri 'label' to corresponding integer if needed
			if (is_string($this->uri_segment))
			{
				if (($key = array_search($this->uri_segment, $this->url)) === FALSE)
				{
					// If uri 'label' is not found, auto add it to base_url
					$this->url[] = $this->uri_segment;
					$this->uri_segment = count($this->url) + 1;
				}
				else
				{
					$this->uri_segment = $key + 2;
				}
			}

			// Insert {page} placeholder
			$this->url[$this->uri_segment - 1] = '{page}';

			// Create full URL
			$this->url = url::site(implode('/', $this->url)).Router::$query_string;

			// Extract current page
			$this->current_page = URI::instance()->segment($this->uri_segment);
		}

		// Core pagination values
		$this->total_items        = (int) max(0, $this->total_items);
		$this->items_per_page     = (int) max(1, $this->items_per_page);
		$this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);

		// If there is no first/last/previous/next page, relative to the
		// current page, value is set to FALSE. Valid page number otherwise.
		$this->first_page         = ($this->current_page === 1) ? FALSE : 1;
		$this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
		$this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
		$this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;

		// SQL values
		$this->sql_offset         = (int) ($this->current_page - 1) * $this->items_per_page;
		$this->sql_limit          = sprintf(' LIMIT %d OFFSET %d ', $this->items_per_page, $this->sql_offset);
	}

	/**
	 * Generates the HTML for the chosen pagination style.
	 *
	 * @param   string  pagination style
	 * @return  string  pagination html
	 */
	public function render($style = NULL)
	{
		// Hide single page pagination
		if ($this->auto_hide === TRUE AND $this->total_pages <= 1)
			return '';

		if ($style === NULL)
		{
			// Use default style
			$style = $this->style;
		}

		// Return rendered pagination view
		return View::factory($this->directory.$style, get_object_vars($this))->render();
	}

	/**
	 * Magically converts pagination object to string.
	 *
	 * @return  string  pagination html
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Magically gets a pagination variable.
	 *
	 * @param   string  variable key
	 * @return  mixed   variable value if the key is found
	 * @return  void    if the key is not found
	 */
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;
	}

	/**
	 * So that users can use $pagination->total_pages() or $pagination->total_pages.
	 *
	 * @param   string  function name
	 * @return  string
	 */
	public function __call($func, $args = FALSE)
	{
		return $this->__get($func);
	}

} // End Pagination Class