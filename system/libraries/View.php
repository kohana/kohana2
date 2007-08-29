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
	private $kohana_renderer = '';
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

	public function render($print = FALSE, $callback = FALSE)
	{
		$output = Kohana::instance()->kohana_include_view($this->kohana_view_filename, $this->data);

		if ($callback != FALSE)
		{
			$output = Kohana::callback($callback, $output);
		}

		if ($print == TRUE)
		{
			print $output;
		}
		else
		{
			return $output;
		}
	}

} // End View Class