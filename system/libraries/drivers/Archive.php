<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Interface: Archive_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
interface Archive_Driver {

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
	public function create($paths, $filename = FALSE);

} // End Archive_Driver Interface