<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Archive_Zip
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Archive_Zip_Driver implements Archive_Driver {

	/*
	 * Method: create
	 *  Creates a zip archive and optionally, saves it to a file.
	 *
	 * Parameters:
	 *  paths    - array of filenames to add
	 *  filename - file to save the archive to
	 *
	 * Returns:
	 *  FALSE if creation fails, TRUE if the filename is set, or archive data.
	 */
	public function create($paths, $filename = FALSE)
	{
		return TRUE;
		foreach($paths as $file)
		{
			if ( ! file_exists($file))
				continue;
		}
	}

} // End Archive_Zip