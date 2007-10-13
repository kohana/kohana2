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
 * View Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/general/views.html
 */
class View_Core {

	// The view file name and type
	private $kohana_filename  = FALSE;
	private $kohana_filetype  = FALSE;

	// Set variables
	private $data = array();

	/**
	 * Construct
	 */
	public function __construct($name, $data = NULL)
	{
		if (preg_match('/\.(gif|jpe?g|png|tiff?|js|css|swf)$/Di', $name, $type))
		{
			$type = $type[1];

			$this->kohana_filename = Kohana::find_file('views', $name, TRUE, $type);
			$this->kohana_filetype = current(Config::item('mimes.'.$type));

			// Clear output Events to be safe
			Event::clear('system.output');
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

	/**
	 * Set a variable
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return object
	 */
	public function set($name, $value)
	{
		$this->__set($name, $value);
		return $this;
	}

	/**
	 * Magic setting of a variable
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function __set($name, $value)
	{
		$protected = array('kohana_filename', 'kohana_renderer', 'kohana_filetype');

		if (in_array($name, $protected) AND $this->$name === FALSE)
		{
			$this->$name = $value;
		}
		else
		{
			$this->data[$name] = $value;
		}
	}

	/**
	 * Magic getting of a variable
	 *
	 * @access public
	 * @param  string
	 * @return void
	 */
	public function __get($name)
	{
		return empty($this->data[$name]) ? NULL : $this->data[$name];
	}

	/**
	 * Magic object to string
	 *
	 * @access public
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render a view
	 *
	 * @access public
	 * @param  string
	 * @param  callback
	 * @return mixed
	 */
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