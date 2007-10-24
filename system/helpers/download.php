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
 * Download Class
 *
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/helpers/download.html
 */
class download {

	/**
	 * Force Download
	 *
	 * Generates headers that force a download to happen
	 *
	 * @access  public
	 * @param   string  filename
	 * @param   mixed   the data to be downloaded
	 * @return  void
	 */
	public static function force($filename = '', $data = '')
	{
		static $user_agent;

		if ($filename == '')
			return FALSE;

		// Load the user agent
		if ($user_agent === NULL)
		{
			$user_agent = new User_agent();
		}

		if (is_file($filename))
		{
			// Get the real path
			$filepath = str_replace('\\', '/', realpath($filename));

			// Get extension
			$extension = pathinfo($filepath, PATHINFO_EXTENSION);

			// Remove directory path from the filename
			$filename = end(explode('/', $filepath));
		}
		else
		{
			// Grab the file extension
			$extension = end(explode('.', $filename));

			// Try to determine if the filename includes a file extension.
			// We need it in order to set the MIME type
			if ($data == '' OR $extension === $filename)
				return FALSE;
		}

		// Set a default mime if we can't find it
		if (($mime = Config::item('mimes.'.$extension)) === NULL)
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = current((array) $mime);
		}

		// Generate the server headers
		header('Content-Type: "'.$mime.'"');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Content-Length: '.strlen($data));

		// IE headers
		if ($user_agent->browser === 'Internet Explorer')
		{
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Pragma: no-cache');
		}

		if (isset($filepath))
		{
			// Open the file
			$handle = fopen($filepath, 'rb');

			// Send the file data
			fpassthru($handle);

			// Close the file
			fclose($handle);
		}
		else
		{
			// Send the file data
			echo $data;
		}
	}

} // End download class