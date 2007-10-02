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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Download Helper
 *
 * @package     Kohana
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Rick Ellis
 * @link        http://www.codeigniter.com/user_guide/helpers/download_helper.html
 */

class download {
	
	/**
	 * Force Download
	 *
	 * Generates headers that force a download to happen
	 *
	 * @access	public
	 * @param	string	filename
	 * @param	mixed	the data to be downloaded
	 * @return	boolean
	 */	
	public static function force($filename = '', $data = '')
	{
		if ($filename == '' OR $data == '')
			return FALSE;

		// Try to determine if the filename includes a file extension.
		// We need it in order to set the MIME type
		if (strpos($filename, '.') === FALSE)
			return FALSE;

		// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);
		
		// Set a default mime if we can't find it
		if (($mime = Config::item('mimes.'.$extension)) === FALSE)
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mime)) ? $mime[0] : $mime;
		}

		// Generate the server headers
		header('Content-Type: "'.$mime.'"');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Content-Length: '.strlen($data));
		
		// IE headers
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
		{
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Pragma: no-cache');
		}

		echo $data;
		return TRUE;
	}

} // End download class
