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
 * Database Cache Class
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/database/
 */
class Core_DB_Cache {

	var $CORE;

	/**
	 * Constructor
	 *
	 * Grabs the CI super object instance so we can access it.
	 *
	 */	
	function Core_DB_Cache()
	{
		// Assign the main CI object to $this->CORE
		// and load the file helper since we use it a lot
		$this->CORE =& get_instance();
		$this->CORE->load->helper('file');	
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache Directory Path
	 *
	 * @access	public
	 * @param	string	the path to the cache directory
	 * @return	bool
	 */		
	function check_path($path = '')
	{
		if ($path == '')
		{
			if ($this->CORE->db->cachedir == '')
			{
				return $this->CORE->db->cache_off();
			}
		
			$path = $this->CORE->db->cachedir;
		}
	
		// Add a trailing slash to the path if needed
		$path = rtrim($path, '/') .'/';
	
		if ( ! is_dir($path) OR ! is_writable($path))
		{
			if ($this->CORE->db->db_debug)
			{
				return $this->CORE->db->display_error('db_invalid_cache_path');
			}
			
			// If the path is wrong we'll turn off caching
			return $this->CORE->db->cache_off();
		}
		
		$this->CORE->db->cachedir = $path;
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Retrieve a cached query
	 *
	 * The URI being requested will become the name of the cache sub-folder.
	 * An MD5 hash of the SQL statement will become the cache file name
	 *
	 * @access	public
	 * @return	string
	 */
	function read($sql)
	{
		if ( ! $this->check_path())
		{
			return $this->CORE->db->cache_off();
		}
	
		$uri  = ($this->CORE->uri->segment(1) == FALSE) ? 'default.'	: $this->CORE->uri->segment(1).'+';
		$uri .= ($this->CORE->uri->segment(2) == FALSE) ? 'index'		: $this->CORE->uri->segment(2);
		
		$filepath = $uri.'/'.md5($sql);
		
		if (FALSE === ($cachedata = read_file($this->CORE->db->cachedir.$filepath)))
		{	
			return FALSE;
		}
		
		return unserialize($cachedata);			
	}	

	// --------------------------------------------------------------------

	/**
	 * Write a query to a cache file
	 *
	 * @access	public
	 * @return	bool
	 */
	function write($sql, $object)
	{
		if ( ! $this->check_path())
		{
			return $this->CORE->db->cache_off();
		}

		$uri  = ($this->CORE->uri->segment(1) == FALSE) ? 'default.'	: $this->CORE->uri->segment(1).'+';
		$uri .= ($this->CORE->uri->segment(2) == FALSE) ? 'index'		: $this->CORE->uri->segment(2);
		
		$dir_path = $this->CORE->db->cachedir.$uri.'/';
		
		$filename = md5($sql);
	
		if ( ! @is_dir($dir_path))
		{
			if ( ! @mkdir($dir_path, 0777))
			{
				return FALSE;
			}
			
			@chmod($dir_path, 0777);			
		}
		
		if (write_file($dir_path.$filename, serialize($object)) === FALSE)
		{
			return FALSE;
		}
		
		@chmod($dir_path.$filename, 0777);
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete cache files within a particular directory
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete($segment_one = '', $segment_two = '')
	{	
		if ($segment_one == '')
		{
			$segment_one  = ($this->CORE->uri->segment(1) == FALSE) ? 'default' : $this->CORE->uri->segment(2);
		}
		
		if ($segment_two == '')
		{
			$segment_two = ($this->CORE->uri->segment(2) == FALSE) ? 'index' : $this->CORE->uri->segment(2);
		}
		
		$dir_path = $this->CORE->db->cachedir.$segment_one.'+'.$segment_two.'/';
		
		delete_files($dir_path, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete all existing cache files
	 *
	 * @access	public
	 * @return	bool
	 */
	function delete_all()
	{
		delete_files($this->CORE->db->cachedir, TRUE);
	}

}

?>