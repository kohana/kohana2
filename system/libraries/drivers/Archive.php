<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Archive driver interface.
 *
 * $Id$
 *
 * @package    Archive
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
interface Archive_Driver {

	/**
	 * Creates an archive and optionally, saves it to a file.
	 *
	 * @param   array    filenames to add
	 * @param   string   file to save the archive to
	 * @return  boolean
	 */
	public function create($paths, $filename = FALSE);

} // End Archive_Driver Interface