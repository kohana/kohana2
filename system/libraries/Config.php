<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/libraries/config.html
 */
class Core_Config {

	var $config = array();
	var $search_paths;
	var $is_loaded = array();

	/**
	 * Constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable
	 *
	 * @access   public
	 * @param   string	the config file name
	 * @param   boolean  if configuration values should be loaded into their own section
	 * @param   boolean  true if errors should just return false, false if an error message should be displayed
	 * @return  boolean  if the file was successfully loaded or not
	 */
	function Core_Config()
	{
		$this->config =& get_config();
		$this->_set_search_paths();
		log_message('debug', 'Config Class Initialized');
	}

	/**
	 * Set list of paths in which to search for config files
	 *
	 * @access private
	 * @return void
	 */
	function _set_search_paths()
	{
		global $CPATHS;
		$this->search_paths = (isset($CPATHS) AND is_array($CPATHS) AND count($CPATHS)>0 )
		                    ? $CPATHS
		                    : array(BASEPATH,APPPATH);
	}

	// --------------------------------------------------------------------

	/**
	 * Load Config File
	 *
	 * @access	public
	 * @param	string	the config file name
	 * @return	boolean	if the file was loaded correctly
	 */
	function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$file = ($file == '') ? 'config' : str_replace(EXT, '', $file);

		if (in_array($file, $this->is_loaded, TRUE))
			return TRUE;

		$include_count = 0;
		foreach($this->search_paths as $path)
		{
			if (is_file($path.'config/'.$file.EXT))
			{
				include($path.'config/'.$file.EXT);

				if (!isset($config) OR ! is_array($config))
				{
					if ($fail_gracefully === TRUE)
						continue;

					show_error('Your '.$file.EXT.' file does not appear to contain a valid configuration array.');
				}
			}
			else
				continue;

			$include_count++;
			if ($use_sections === TRUE)
			{
				if (isset($this->config[$file]))
				{
					$this->config[$file] = array_merge($this->config[$file], $config);
				}
				else
				{
						$this->config[$file] = $config;
				}
			}
			else
			{
				$this->config = array_merge($this->config, $config);
			}
			unset($config);
		}
		if($include_count>0)
		{
			$this->is_loaded[] = $file;
		}
		else
		{
			if ($fail_gracefully === TRUE)
			{
				log_message('error', 'The configuration file '.$file.EXT.' does not exist in the search paths.');
				return FALSE;
			}
			
			show_error('The configuration file '.$file.EXT.' does not exist in the search paths.');
		}
		log_message('debug', 'Config file loaded: config/'.$file.EXT);
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config file item
	 *
	 *
	 * @access	public
	 * @param	string	the config item name
	 * @param	string	the index name
	 * @param	bool
	 * @return	string
	 */
	function item($item, $index = '')
	{
		if ($index == '')
		{
			if ( ! isset($this->config[$item]))
				return FALSE;

			$pref = $this->config[$item];
		}
		else
		{
			if ( ! isset($this->config[$index]))
				return FALSE;

			if ( ! isset($this->config[$index][$item]))
				return FALSE;

			$pref = $this->config[$index][$item];
		}

		return $pref;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config file item - adds slash after item
	 *
	 * @access	public
	 * @param	string	the config item name
	 * @return	string
	 */
	function slash_item($item)
	{
		if ( ! isset($this->config[$item]))
			return FALSE;

		$pref = $this->config[$item];

		if ($pref != '')
		{
			$pref = rtrim($pref, '/').'/';
		}

		return $pref;
	}

	// --------------------------------------------------------------------

	/**
	 * Site URL
	 *
	 * @access	public
	 * @param	string	the URI string
	 * @return	string
	 */
	function site_url($uri = '')
	{
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}
		else
		{
			$uri = trim($uri, '/');
		}

		if (strpos($uri, '?') !== FALSE)
		{
			$get = explode('?', $uri);
			$uri = array_shift($get);
			// Remove the query string if GET is disabled
			$get = ($this->item('enable_get_requests') == TRUE) ?  '?'.implode('&', $get) : '';
		}
		else
		{
			$get = '';
		}

		$url = $this->slash_item('base_url');
		// Append uri to the site_url
		if ($uri != '')
		{
			$url .= $this->slash_item('index_page').$uri.$this->item('url_suffix').$get;
		}
		else
		{
			$url .= $this->item('index_page');
		}

		return $url;
	}

	// --------------------------------------------------------------------

	/**
	 * System URL
	 *
	 * @access	public
	 * @return	string
	 */
	function system_url()
	{
		$uri = explode('/', trim(BASEPATH, '/'));
		return $this->slash_item('base_url').end($uri).'/';
	}

	// --------------------------------------------------------------------

	/**
	 * Set a config file item
	 *
	 * @access	public
	 * @param	string	the config item key
	 * @param	string	the config item value
	 * @return	void
	 */
	function set_item($item, $value)
	{
		$this->config[$item] = $value;
	}

}

// END Core_Config class
?>