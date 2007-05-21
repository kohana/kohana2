<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BlueFlame
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		BlueFlame
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeigniter.com/user_guide/license.html
 * @link		http://blueflame.ciforge.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * BlueFlame Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package		BlueFlame
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config {

	var $config = array();
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
	function CI_Config()
	{
		$this->config =& get_config();
		log_message('debug', "Config Class Initialized");
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
		
		if ( ! file_exists(APPPATH.'config/'.$file.EXT))
		{
			if ($fail_gracefully === TRUE)
				return FALSE;
			
			show_error('The configuration file '.$file.EXT.' does not exist.');
		}
		
		include(APPPATH.'config/'.$file.EXT);
		
		if ( ! isset($config) OR ! is_array($config))
		{
			if ($fail_gracefully === TRUE)
				return FALSE;
			
			show_error('Your '.$file.EXT.' file does not appear to contain a valid configuration array.');
		}
		
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
		
		$this->is_loaded[] = $file;
		unset($config);
		
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
	 * The second parameter allows a slash to be added to the end of
	 * the item, in the case of a path.
	 *
	 * @access	public
	 * @param	string	the config item name
	 * @param	bool
	 * @return	string
	 */		
	function slash_item($item)
	{
		if ( ! isset($this->config[$item]))
			return FALSE;
		
		$pref = $this->config[$item];
		
		if ($pref != '')
		{
			$pref = rtrim($pref, "/")."/";
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
			$uri = implode("/", $uri);
		}
		
		$url = $this->slash_item('base_url').$this->item('index_page');
		// Append uri to the site_url
		if ($uri != '')
		{
			$url .= rtrim($uri, "/")."/".$this->item('url_suffix');
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
		$uri = explode("/", rtrim(BASEPATH, "/"));
		return $this->slash_item('base_url').end($uri)."/";
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

// END CI_Config class
?>