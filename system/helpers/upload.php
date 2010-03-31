<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * THe upload helper class provides convenience methods for working
 * with and manipulating the global $_FILES array.
 *
 * [!!] Vanilla server-side file upload procedures can only validate the size of a file *once it is uploaded on the server*
 * 
 * For file size validation **before** upload, research
 * Javascript/AJAX methods of validation pre-upload.
 *
 * ##### Complete Example
 *     
 *     $files = Validation::factory($_FILES)->add_rules('picture', 'upload::valid', 'upload::required', 'upload::type[gif,jpg,png]', 'upload::size[1M]');
 *
 *     if ($files->validate())
 *     {
 *			// Temporary file name
 *			$filename = upload::save('picture');
 *
 *			// Resize, sharpen, and save the image
 *			Image::factory($filename)->resize(100, 100, Image::WIDTH)
 *									 ->save(DOCROOT.'media/pictures/'.basename($filename));
 *			// Remove the temporary file
 *			unlink($filename);
 *     }
 *
 * ##### Upload Multiple Files
 *     
 *     foreach( arr::rotate($_FILES['image']) as $file )
 *     {
 *			$filename = upload::save($file);
 *     
 *			Image::factory($filename)->resize(30, 30, Image::AUTO)
 *									 ->save(DOCROOT.'upload/'.basename($filename));
 *			unlink($filename);
 *     }
 *     
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class upload_Core {

	/**
	 * This method will write a given uploaded file to the filesystem.
	 * 
	 * Provided a new filename, target directory, and permissions
	 * bitmask the file will be written appropriately.
	 *
	 * The first function argument takes as its value a string or
	 * array and specifies the identifier of the file to be written
	 * contained within the `$_FILES` global array given by the
	 * submitting form input field. If supplied an array, a *name*
	 * key will be the default key => value pair from which the
	 * uploaded filename is to be assumed.
	 *
	 * The second function argument takes as its value a string and is
	 * used to replace the given filename upon writing.
	 *
	 * The third function argument takes as its value a string and
	 * specifies the target directory of which the file is to be
	 * written.
	 *
	 * The fourth function argument takes as its value an octal
	 * integer (leading zero) and is a bitmask representing the
	 * intended permissions for that file.
	 *
	 * ###### Example
	 *     
	 *     // Assuming the value of $_FILES['name'] is "picture"
	 *     $filename = upload::save('picture', 'my_new_file01.jpg', '/some/new/location/');
	 *     
	 *     echo Kohana::debug($filename);
	 *     
	 *     // Output:
	 *     (string) /some/new/location/my_new_file01.jpg
	 *
	 * @param   mixed    $file      Name of $_FILE input or array of upload data
	 * @param   string   $filename  Replacement filename
	 * @param   string   $directory Target directory
	 * @param   integer  $chmod     Octal bitmask
	 * @return  string   Full path to the writte file
	 */
	public static function save($file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		// Load file data from FILES if not passed as array
		$file = is_array($file) ? $file : $_FILES[$file];

		if ($filename === NULL)
		{
			// Use the default filename, with a timestamp pre-pended
			$filename = time().$file['name'];
		}

		if (Kohana::config('upload.remove_spaces') === TRUE)
		{
			// Remove spaces from the filename
			$filename = preg_replace('/\s+/', '_', $filename);
		}

		if ($directory === NULL)
		{
			// Use the pre-configured upload directory
			$directory = Kohana::config('upload.directory', TRUE);
		}

		// Make sure the directory ends with a slash
		$directory = rtrim($directory, '/').'/';

		if ( ! is_dir($directory) AND Kohana::config('upload.create_directories') === TRUE)
		{
			// Create the upload directory
			mkdir($directory, 0777, TRUE);
		}

		if ( ! is_writable($directory))
			throw new Kohana_Exception('The upload destination folder, :dir:, does not appear to be writable.', array(':dir:' => $directory));

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
	 * This method tests the input data to validate if it is of a
	 * valid file type, even if no upload is present.
	 *
	 * [!!] These methods *can* be used independently; but, they are best utilized in conjunction with [Validation:add_rules]
	 *
	 * ###### Example
	 *     
	 *     // Assuming the value of $_FILES['name'] is "picture"
	 *     $files = Validation::factory($_FILES)->add_rules('picture', 'upload::valid');
	 * 
	 * @param   array  $file $_FILES item
	 * @return  bool
	 */
	public static function valid($file)
	{
		return (is_array($file)
			AND isset($file['error'])
			AND isset($file['name'])
			AND isset($file['type'])
			AND isset($file['tmp_name'])
			AND isset($file['size']));
	}

	/**
	 * This method tests the input data to validate if it has
	 * *non*-empty data.
	 *
	 * [!!] These methods *can* be used independently; but, they are best utilized in conjunction with [Validation:add_rules]
	 *
	 * ###### Example
	 *     
	 *     // Assuming the value of $_FILES['name'] is "picture"
	 *     $files = Validation::factory($_FILES)->add_rules('picture', 'upload::required');
	 *
	 * @param   array    $file $_FILES item
	 * @return  bool
	 */
	public static function required(array $file)
	{
		return (isset($file['tmp_name'])
			AND isset($file['error'])
			AND is_uploaded_file($file['tmp_name'])
			AND (int) $file['error'] === UPLOAD_ERR_OK);
	}

	/**
	 * This method tests the input data to validate if it is permitted
	 * by extension.
	 *
	 * [!!] These methods *can* be used independently; but, they are best utilized in conjunction with [Validation:add_rules]
	 *
	 * ###### Example
	 *     
	 *     // Assuming the value of $_FILES['name'] is "picture"
	 *     $files = Validation::factory($_FILES)->add_rules('picture', 'upload::type[gif,jpg,png]');
	 * 
	 * @param   array    $file          $_FILES item
	 * @param   array    $allowed_types Allowed file extensions
	 * @return  bool
	 */
	public static function type(array $file, array $allowed_types)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Get the default extension of the file
		$extension = strtolower(substr(strrchr($file['name'], '.'), 1));

		// Make sure there is an extension and that the extension is allowed
		return ( ! empty($extension) AND in_array($extension, $allowed_types));
	}

	/**
	 * This method tests the input data to validate if it is allowed
	 * by file size.
	 *
	 * File sizes are defined as: SB, where S is the size (1, 15, 300,
	 * etc) and B is the byte modifier: (B)ytes, (K)ilobytes,
	 * (M)egabytes, (G)igabytes. Eg: to limit the size to 1MB or less,
	 * you would use "1M".
	 *
	 * @link http://en.wikipedia.org/wiki/Binary_prefix
	 *
	 * [!!] These methods *can* be used independently; but, they are best utilized in conjunction with [Validation:add_rules]
	 *
	 * ###### Example
	 *     	
	 *     // Assuming the value of $_FILES['name'] is "picture"
	 *     $files = Validation::factory($_FILES)->add_rules('picture', 'upload::size[1M]');
	 *
	 * @param   array    $file $_FILES item
	 * @param   array    $size Maximum file size
	 * @return  bool
	 */
	public static function size(array $file, array $size)
	{
		if ((int) $file['error'] !== UPLOAD_ERR_OK)
			return TRUE;

		// Only one size is allowed
		$size = strtoupper($size[0]);

		if ( ! preg_match('/[0-9]++[BKMG]/', $size))
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