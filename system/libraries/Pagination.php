<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Pagination Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/pagination.html
 */
class Pagination_Core {
	
	public $base_url           = '';
	public $style              = 'classic';
	public $uri_segment        = 3;
	public $uri_label;
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

	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   array
	 * @return  void
	 */
	public function __construct($setup = array())
	{
		// Load pagination setup values
		$setup = array_merge(Config::load('pagination', FALSE), $setup);
		
		foreach ($setup as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
		
		// Explode the base_url into segments
		$this->base_url = explode('/', trim($this->base_url, '/'));

		// If a uri_label is given, look for the corresponding uri_segment
		if (isset($this->uri_label))
		{
			$uri_label_segment = array_search($this->uri_label, $this->base_url);
			
			if ($uri_label_segment !== FALSE)
			{
				$this->uri_segment = $uri_label_segment + 2;
			}
		}
		
		// Create a generic base_url with {page} placeholder
		$this->base_url[$this->uri_segment - 1] = '{page}';
		$this->base_url = implode('/', $this->base_url);
		$this->base_url = url::site($this->base_url);
		
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
	
	/**
	 * Generates the HTML for the chosen pagination style
	 *
	 * @access  public
	 * @param   string
	 * @return  string
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

	/**
	 * Returns the base_url with the specified page number
	 *
	 * @access  public
	 * @param   integer
	 * @return  string
	 */
	public function url($page = NULL)
	{
		$page = (int) (isset($page)) ? $page : $this->current_page;
		
		return str_replace('{page}', $page, $this->base_url);
	}
	
	/**
	 * Returns the SQL offset of the first row to return
	 *
	 * @access  public
	 * @return  integer
	 */
	public function sql_offset()
	{
		return (int) ($this->current_page - 1) * $this->items_per_page;
	}
	
	/**
	 * Returns the complete SQL LIMIT clause
	 *
	 * @access  public
	 * @return  string
	 */
	public function sql_limit()
	{
		return sprintf(' LIMIT %d OFFSET %d ', $this->items_per_page, $this->sql_offset());
	}

} // End Pagination Class