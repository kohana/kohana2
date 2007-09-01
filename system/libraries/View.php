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

	private $kohana_filename  = FALSE;
	private $kohana_renderer  = FALSE;
	private $kohana_filetype  = FALSE;
	private $kohana_protected = FALSE;
	private $data = array();

	public function __construct($name, $data = NULL)
	{
		if (preg_match('/\.(gif|jpg|png|swf)$/Di', $name, $type))
		{
			$type = $type[1];
			$this->kohana_filename = Kohana::find_file('views', $name, TRUE, $type);
			
			if (function_exists('exif_imagetype'))
			{
				$this->kohana_filetype = image_type_to_mime_type(exif_imagetype($this->kohana_filename));
			}
			else
			{
				$this->kohana_filetype = 'image/'.$type;
			}
		}
		else
		{
			$this->kohana_filename = Kohana::find_file('views', $name, TRUE);
		}

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

	public function render($print = FALSE, $callback = FALSE)
	{
		if ($this->kohana_filetype == FALSE)
		{
			$output = Kohana::instance()->kohana_include_view($this->kohana_filename, $this->data);

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
		else
		{
			header('Content-type: '.$this->kohana_filetype);

			if ($print == TRUE)
			{
				fpassthru(($file = fopen($this->kohana_filename, 'rb')));
				fclose($file);
			}
			else
			{
				return file_get_contents($this->kohana_filename);
			}
		}
	}

} // End View Class