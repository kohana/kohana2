<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Archive
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Archive_Core {

	// Files and directories
	protected $paths;

	// Driver instance
	protected $driver;

	/**
	 * Constructor: __construct
	 *  Loads the archive driver.
	 *
	 * Throws: Kohana_Exception
	 */
	public function __construct($type = NULL)
	{
		$type = empty($type) ? 'zip' : $type;

		try
		{
			// Set driver name
			$driver = 'Archive_'.ucfirst($type).'_Driver';

			// Manually call auto-loading, for proper exception handling
			Kohana::auto_load($driver);

			// Initialize the driver
			$this->driver = new $driver();
		}
		catch (Kohana_Exception $exception)
		{
			throw new Kohana_Exception('archive.driver_not_supported', $type);
		}

		// Validate the driver
		if ( ! in_array('Archive_Driver', class_implements($this->driver)))
			throw new Kohana_Exception('archive.driver_implements', $type);

		Log::add('debug', 'Archive Library initialized');
	}

	/**
	 * Method: add
	 *  Adds files or directories, recursively, to an archive.
	 *
	 * Parameters:
	 *  path - file or directory to add
	 *
	 * Returns:
	 *  Archive object
	 */
	public function add($path, $recursive = TRUE)
	{
		// Normalize to forward slashes
		$path = str_replace('\\', '/', $path);

		if (file_exists($path) AND is_dir($path))
		{
			// Normalize ending slash
			$path = rtrim($path, '/').'/';

			// Add directory to paths
			$this->paths[] = $path;

			if ($recursive == TRUE)
			{
				$dir = opendir($path);
				while (($file = readdir($dir)) !== FALSE)
				{
					// Do not add hidden files or directories
					if (substr($file, 0, 1) === '.')
						continue;

					// Read directory contents
					$this->add($path.$file);
				}
				closedir($dir);
			}
		}
		else
		{
			$this->paths[] = $path;
		}

		return $this;
	}

	/**
	 * Method: save
	 *  Creates an archive and saves it into a file.
	 *
	 * Parameters:
	 *  filename - archive filename
	 *
	 * Throws: Kohana_Exception
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function save($filename)
	{
		// Get the directory name
		$directory = pathinfo($filename, PATHINFO_DIRNAME);

		if ( ! is_writable($directory))
			throw new Kohana_Exception('archive.directory_unwritable', $directory);

		if (file_exists($filename))
		{
			// Unable to write to the file
			if ( ! is_writable($filename))
				throw new Kohana_Exception('archive.filename_conflict', $filename);

			// Remove the file
			unlink($filename);
		}

		return $this->driver->create($this->paths, $filename);
	}

	/**
	 * Method: download
	 *  Forces a download of a created archive.
	 *
	 * Parameters:
	 *  filename - name of the file that will be downloaded
	 */
	public function download($filename)
	{
		download::force($filename, $this->driver->create($this->paths));
	}

} // End Archive
