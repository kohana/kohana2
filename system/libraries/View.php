<?php defined('SYSPATH') or die('No direct script access.');
 /*
 * Class: View
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class View_Core {

	// The view file name and type
	protected $kohana_filename = FALSE;
	protected $kohana_filetype = FALSE;

	// Set variables
	protected $data = array();

	/*
	 * Constructor: __construct
	 *
	 * Parameters:
	 *  name - view filename string
	 *  data - view data
	 */
	public function __construct($name, $data = NULL)
	{
		if (preg_match('/\.(?:gif|jpe?g|png|css|js|tiff?|swf)$/Di', $name, $type))
		{
			$type = substr($type[0], 1);

			$this->kohana_filename = Kohana::find_file('views', $name, TRUE, $type);
			$this->kohana_filetype = current(Config::item('mimes.'.$type));

			// Clear output Events to be safe
			Event::clear('system.display');
		}
		else
		{
			$this->kohana_filename = Kohana::find_file('views', $name, TRUE);
			$this->kohana_filetype = EXT;
		}

		// Preload data
		if (is_array($data) AND ! empty($data))
		{
			foreach($data as $name => $value)
			{
				$this->data[$name] = $value;
			}
		}

		Log::add('debug', 'View Class Initialized ['.str_replace(DOCROOT, '', $this->kohana_filename).']');
	}

	/*
	 * Method: set
	 *  Sets a view variable.
	 *
	 * Parameters:
	 *  name  - variable name
	 *  value - variable contents
	 * 
	 * Returns:
	 *  View object
	 */
	public function set($name, $value)
	{
		$this->__set($name, $value);
		return $this;
	}

	/*
	 * Method: __set
	 *  Magically sets a view variable.
	 *
	 * Parameters:
	 *  name  - variable name
	 *  value - variable contents
	 */
	public function __set($name, $value)
	{
		if ( ! isset($this->$name))
		{
			$this->data[$name] = $value;
		}
	}

	/*
	 * Method: __get
	 *  Magically gets a view variable.
	 *
	 * Parameters:
	 *  name - variable name
	 * 
	 * Returns:
	 *  The variable contents or NULL if the variable does not exist
	 */
	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : NULL;
	}

	/*
	 * Method: __toString
	 *  Magically converts view object to string.
	 *
	 * Returns:
	 *  The rendered view
	 */
	public function __toString()
	{
		return $this->render();
	}

	/*
	 * Method: render
	 *  Renders a view.
	 *
	 * Parameters:
	 *  print    - echo the output instead of returning it
	 *  renderer - user defined renderer callback
	 * 
	 * Returns:
	 *  The rendered view if print is set to FALSE
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
			// Overwrite the content-type header
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