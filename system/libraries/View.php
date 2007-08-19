<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * View Class
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    Views
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/libraries/view.html
 */

// View($kohana_name, $kohana_data, $kohana_return)

class View_Core {

	private $kohana_view_filename;
	private $data = array();

	public function __construct($name, $data = NULL)
	{
		$this->kohana_view_filename = Kohana::find_file('views', $name, TRUE);

		// Preload data
		if (is_array($data) AND ! empty($data))
		{
			foreach($data as $key => $val)
			{
				$this->data[$key] = $val;
			}
		}
	}

	public function set($name, $value)
	{
		$this->__set($name, $value);
		return $this;
	}

	public function __set($name, $value)
	{
		if ($name == 'kohana_view_filename' AND $this->kohana_view_filename == FALSE)
		{
			$this->kohana_view_filename = $value;
		}
		else
		{
			$this->data[$name] = $value;
		}
	}

	public function & __get($name)
	{
		if (isset($this->data))
			return $this->data;

		return FALSE;
	}

	public function __toString()
	{
		return $this->render();
	}

	public function render($kohana_display_output = FALSE)
	{
		ob_start();
		extract($this->data);
		include $this->kohana_view_filename;
		$kohana_view_output = ob_get_contents();
		ob_end_clean();
		
		if ($kohana_display_output == TRUE)
		{
			print $kohana_view_output;
		}
		else
		{
			return $kohana_view_output;
		}
	}

}

class NOT_Core_View {

	var $load     = '';
	var $data     = array();
	var $template = 'template';

	/**
	 * Constructor
	 */
	function Core_View()
	{
		$CORE =& get_instance();
		$this->load =& $CORE->load;
	}

	/**
	 * Get Variable
	 *
	 * @access	public
	 * @return	string
	 */
	function get($key)
	{
		return (isset($this->data[$key]) ? $this->data[$key] : '');
	}

	/**
	 * Set Variable(s)
	 *
	 * @access	public
	 * @param	mixed
	 * @param	mixed
	 * @return	void
	 */
	function set($key, $data = FALSE)
	{
		if (is_array($key))
		{
			$this->data = array_merge($this->data, $key);
		}
		else
		{
			$this->data[$key] = $data;
		}
	}

	/**
	 * Add to a Variable(s)
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function add($key, $data)
	{
		if (! is_string($key) OR ! is_string($data))
			return;

		if (isset($this->data[$key]))
		{
			$this->data[$key] .= $data;
		}
		else
		{
			$this->set($key, $data);
		}
	}

	/**
	 * Delete Variables
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function del($key)
	{
		if (isset($this->data[$key]))
		{
			unset($this->data[$key]);
		}
	}

	/**
	 * Load a View
	 *
	 * Loads a template for inclusion into the template, or displays the template
	 *
	 * EXAMPLES:
	 * Load the "blog_content" view into the "body" variable:
	 *   load('blog_content', 'body')
	 * Load the "blog_content" view into the "blog_content" variable:
	 *   load('blog_content', TRUE)
	 * Load the currently set template and return it as a string:
	 *   load(TRUE)
	 * Load the current template and display it:
	 *   load()
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function load($view = FALSE, $partial = FALSE)
	{
		if ($view == FALSE)
		{
			$view = $this->template;
		}
		elseif ($view === TRUE)
		{
			return $this->load->view($this->template, $this->data, TRUE);
		}

		if ($partial != FALSE)
		{
			$key = ($partial === TRUE) ? $view : $partial;
			$this->data[$key] = $this->load->view($view, $this->data, TRUE);

			return $this->data[$key];
		}
		else
		{
			$this->load->view($view, $this->data);
		}
	}
} // End View Class