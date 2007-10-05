<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Team.
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
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

	// The view file name and type
	private $kohana_filename  = FALSE;
	private $kohana_filetype  = FALSE;

	// Set variables
	private $data = array();

	public function __construct($name, $data = NULL)
	{
		if (preg_match('/\.([g|t]if|jpe?g|png|swf|js|css)$/Di', $name, $type))
		{
			$type = $type[1];

			$this->kohana_filename = Kohana::find_file('views', $name, TRUE, $type);
			$this->kohana_filetype = current(Config::item('mimes.'.$type));
		}
		else
		{
			$this->kohana_filename = Kohana::find_file('views', $name, TRUE);
			$this->kohana_filetype = EXT;
		}

		// Preload data
		if (is_array($data) AND ! empty($data))
		{
			foreach($data as $key => $val)
			{
				$this->data[$key] = $val;
			}
		}

		Log::add('debug', 'View Class Initialized ['.str_replace(DOCROOT, '', $this->kohana_filename).']');
	}

	public function set($name, $value)
	{
		$this->__set($name, $value);
		return $this;
	}

	public function __set($name, $value)
	{
		$protected = array('kohana_filename', 'kohana_renderer', 'kohana_filetype');

		if (in_array($name,  $protected) AND $this->$name === FALSE)
		{
			$this->$name = $value;
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

	public function render($print = FALSE, $renderer = FALSE)
	{
		if ($this->kohana_filetype === EXT)
		{
			// Load the view in the controller for access to $this
			$output = Kohana::instance()->kohana_include_view($this->kohana_filename, $this->data);

			// Pass the output through the user defined renderer
			if ($renderer == TRUE AND is_callable($renderer, TRUE))
			{
				$output = call_user_func($renderer, $output);
			}

			// Display the output
			if ($print == TRUE)
			{
				print $output;
				return;
			}
		}
		else
		{
			// Send the filetype header
			header('Content-type: '.$this->kohana_filetype);

			// Display the output
			if ($print == TRUE)
			{
				if ($file = fopen($this->kohana_filename, 'rb'))
				{
					fpassthru($file);
					fclose($file);
				}
				return;
			}

			// Fetch the file contents
			$output = file_get_contents($this->kohana_filename);
		}

		// Output has not been printed, return it
		return $output;
	}

} // End View Class