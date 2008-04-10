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
	 * @param   string   name of $_FILE input
	 * @param   string   new filename
	 * @param   string   new directory
	 * @return  string   full path to new file
	 */
	public function save($name, $filename = NULL, $directory = NULL)
	{
		if ($filename === NULL)
		{
			// Use the default filename, with a timestamp pre-pended
			$filename = time().$_FILES[$name]['name'];
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
		else
		{
			// Make sure the directory ends with a slash
			$directory = rtrim($directory, '/').'/';
		}

		if ( ! is_writable($directory))
			throw new Kohana_Exception('upload.not_writable', $directory);

		if (is_uploaded_file($_FILES[$name]['tmp_name']) AND move_uploaded_file($_FILES[$name]['tmp_name'], $filename = $directory.$filename))
		{
			// Move the file to the upload directory
			return $filename;
		}

		return FALSE;
	}

	/* Validation Rules */

	/**
	 * Tests if a $_FILES item is valid.
	 *
	 * @param   array    $_FILES item
	 * @return  boolean
	 */
	public static function valid($file)
	{
		return (is_array($file)
			AND isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size'])
			AND is_uploaded_file($file['tmp_name'])
			AND $file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by extension.
	 *
	 * @param   array    $_FILES item
	 * @param   array    allowed file extensions
	 * @return  boolean
	 */
	public static function type(array $file, array $allowed_types)
	{
		// Get the default extension of the file
		$extension = strtolower(preg_replace('/^.+\.(.+?)$/', '$1', $file['name']));

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
	 * @return  boolean
	 */
	public function size(array $file, array $size)
	{
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