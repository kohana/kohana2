<?php
/**
 * Archive driver interface.
 *
 * $Id: Archive.php 3326 2008-08-09 21:24:30Z Shadowhand $
 *
 * @package    Archive
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Archive_Driver {

	/**
	 * Creates an archive and optionally, saves it to a file.
	 *
	 * @param   array    filenames to add
	 * @param   string   file to save the archive to
	 * @return  boolean
	 */
	abstract public function create($paths, $filename = FALSE);

	/**
	 * Add data to the archive.
	 *
	 * @param   string   filename
	 * @param   string   name of file in archive
	 * @return  void
	 */
	abstract public function add_data($file, $name, $contents = NULL);

} // End Archive_Driver Interface