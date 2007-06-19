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
 * Kohana File Helpers
 *
 * @package		Kohana
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/file_helpers.html
 */

// ------------------------------------------------------------------------

/**
 * Read File
 *
 * Opens the file specfied in the path and returns it as a string.
 *
 * @access	public
 * @param	string	path to file
 * @return	string
 */	
function read_file($file)
{
	if ( ! file_exists($file))
	{
		return FALSE;
	}
	
	if (function_exists('file_get_contents'))
	{
		return file_get_contents($file);		
	}

	if ( ! $fp = @fopen($file, 'rb'))
	{
		return FALSE;
	}
		
	flock($fp, LOCK_SH);
	
	$data = '';
	if (filesize($file) > 0)
	{
		$data =& fread($fp, filesize($file));
	}

	flock($fp, LOCK_UN);
	fclose($fp);

	return $data;
}
	
// ------------------------------------------------------------------------

/**
 * Write File
 *
 * Writes data to the file specified in the path.
 * Creates a new file if non-existent.
 *
 * @access	public
 * @param	string	path to file
 * @param	string	file data
 * @return	bool
 */	
function write_file($path, $data, $mode = 'wb')
{
	if ( ! $fp = @fopen($path, $mode))
	{
		return FALSE;
	}
		
	flock($fp, LOCK_EX);
	fwrite($fp, $data);
	flock($fp, LOCK_UN);
	fclose($fp);	

	return TRUE;
}
	
// ------------------------------------------------------------------------

/**
 * Delete Files
 *
 * Deletes all files contained in the supplied directory path.
 * Files must be writable or owned by the system in order to be deleted.
 * If the second parameter is set to TRUE, any directories contained
 * within the supplied base directory will be nuked as well.
 *
 * @access	public
 * @param	string	path to file
 * @param	bool	whether to delete any directories found in the path
 * @return	bool
 */	
function delete_files($path, $del_dir = FALSE, $level = 0)
{	
	// Trim the trailing slash
	$path = rtrim($path, '/');
			
	if ( ! $current_dir = @opendir($path))
		return;
	
	while(($filename = @readdir($current_dir)) !== FALSE)
	{
		if ($filename != "." and $filename != "..")
		{
			if (is_dir($path.'/'.$filename))
			{
				$level++;
				delete_files($path.'/'.$filename, $del_dir, $level);
			}
			else
			{
				unlink($path.'/'.$filename);
			}
		}
	}
	@closedir($current_dir);
	
	if ($del_dir == TRUE AND $level > 0)
	{
		@rmdir($path);
	}
}

// ------------------------------------------------------------------------

/**
 * Get Filenames
 *
 * Reads the specified directory and builds an array containing the filenames.  
 * Any sub-folders contained within the specified path are read as well.
 *
 * @access	public
 * @param	string	path to source
 * @param	bool	whether to include the path as part of the filename
 * @return	array
 */	
function get_filenames($source_dir, $include_path = FALSE)
{
	static $_filedata = array();
	
	if ($fp = @opendir($source_dir))
	{
		while (($file = readdir($fp)) !== FALSE)
		{
			if (@is_dir($source_dir.$file) && substr($file, 0, 1) != '.')
			{
				 get_filenames($source_dir.$file."/", $include_path);
			}
			elseif (substr($file, 0, 1) != ".")
			{
			
				$_filedata[] = ($include_path == TRUE) ? $source_dir.$file : $file;
			}
		}
		return $_filedata;
	}
}

?>