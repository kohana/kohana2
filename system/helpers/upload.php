<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Upload helper class for working with the global $_FILES
 * array and Validation library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class upload_Core {

	/**
	 * Save an uploaded file to a new location.
	 *
	 * @param   mixed    name of $_FILE input or array of upload data
	 * @param   string   new filename
	 * @param   string   new directory
	 * @param   integer  chmod mask
	 * @return  string   full path to new file
	 */
	public function save($file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		// Load file data from FILES if not passed as array
		$file = is_array($file) ? $file : $_FILES[$file];

		if ($filename === NULL)
		{
			// Use the default filename, with a timestamp pre-pended
			$filename = time().$file['name'];
		}

		if (Config::item('upload.remove_spaces'))
		{
			// Remove spaces from the filename
			$filename = preg_replace('/\s+/', '_', $filename);
		}

		if ($directory === NULL)
		{
			// Use the pre-configured upload directory
			$directory = Config::item('upload.directory', TRUE);
		}

		// Make sure the directory ends with a slash
		$directory = rtrim($directory, '/').'/';

		if ( ! is_writable($directory))
			throw new Kohana_Exception('upload.not_writable', $directory);

		if (is_uploaded_file($file['tmp_name']) AND move_uploaded_file($file['tmp_name'], $filename = $directory.$filename))
		{
			if ($chmod !== FALSE)
			{
				// Set permissions on filename
				chmod($filename, $chmod);
			}

			// Return new file path
			return $filename;
		}

		return FALSE;
	}

	/* Validation Rules */

	/**
	 * Tests if a $_FILES item exists.
	 *
	 * @param   array  $_FILES item
	 * @return  bool
	 */
	public static function required($file)
	{
		return (is_array($file)
			AND isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size']));
	}

	/**
	 * Tests if a $_FILES item is valid.
	 *
	 * @param   array    $_FILES item
	 * @return  bool
	 */
	public static function valid(array $file)
	{
		return (isset($file['tmp_name'])
			AND isset($file['error'])
			AND is_uploaded_file($file['tmp_name'])
			AND (int) $file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by extension.
	 *
	 * @param   array    $_FILES item
	 * @param   array    allowed file extensions
	 * @return  bool
	 */
	public static function type(array $file, array $allowed_types)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Get the default extension of the file
		$extension = strtolower(file::extension($file['name']));

		// Get the mime types for the extension
		$mime_types = Config::item('mimes.'.$extension);

		// Make sure there is an extension, that the extension is allowed, and that mime types exist
		return ( ! empty($extension) AND in_array($extension, $allowed_types) AND is_array($mime_types));
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size.
	 * File sizes are defined as: SB, where S is the size (1, 15, 300, etc) and
	 * B is the byte modifier: (B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
	 * Eg: to limit the size to 1MB or less, you would use "1M".
	 *
	 * @param   array    $_FILES item
	 * @param   array    maximum file size
	 * @return  bool
	 */
	public function size(array $file, array $size)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Only one size is allowed
		$size = strtoupper($size[0]);

		if ( ! preg_match('/[0-9]+[BKMG]/', $size))
			return FALSE;

		// Make the size into a power of 1024
		switch (substr($size, -1))
		{
			case 'G': $size = intval($size) * pow(1024, 3); break;
			case 'M': $size = intval($size) * pow(1024, 2); break;
			case 'K': $size = intval($size) * pow(1024, 1); break;
			default:  $size = intval($size);                break;
		}

		// Test that the file is under or equal to the max size
		return ($file['size'] <= $size);
	}

} // End upload