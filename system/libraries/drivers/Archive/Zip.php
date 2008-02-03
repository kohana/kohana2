<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Archive_Zip_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Archive_Zip_Driver implements Archive_Driver {

	// Compiled directory structure
	protected $dirs = '';

	// Compiled archive data
	protected $data = '';

	// Offset location
	protected $offset = 0;

	/**
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
		// Sort the paths to make sure that directories come before files
		sort($paths);

		foreach ($paths as $set)
		{
			// Add each file
			$this->add_file($set[0], $set[1]);
		}

		// File data
		$data = implode('', $this->data);

		// Directory data
		$dirs = implode('', $this->dirs);

		$zipfile =
			$data.                              // File data
			$dirs.                              // Directory data
			"\x50\x4b\x05\x06\x00\x00\x00\x00". // Directory EOF
			pack('v', count($this->dirs)).      // Total number of entries "on disk"
			pack('v', count($this->dirs)).      // Total number of entries in file
			pack('V', strlen($dirs)).           // Size of directories
			pack('V', strlen($data)).           // Offset to directories
			"\x00\x00";                         // Zip comment length

		if ($filename == FALSE)
		{
			return $zipfile;
		}

		if (substr($filename, -3) != 'zip')
		{
			// Append zip extension
			$filename .= '.zip';
		}

		// Create the file in binary write mode
		$file = fopen($filename, 'wb');

		// Lock the file
		flock($file, LOCK_EX);

		// Write the zip file
		$return = fwrite($file, $zipfile);

		// Unlock the file
		flock($file, LOCK_UN);

		// Close the file
		fclose($file);

		return (bool) $return;
	}

	/**
	 * Method: add_dir
	 *  Adds a directory to a zip.
	 *
	 * Parameters:
	 *  dir - name of directory
	 */
	protected function add_dir($dir, $name)
	{
		// Fetch the timestamp
		$timestamp = date::unix2dos(filemtime($dir));

		$this->data[] =
			// Start "local file header"
			"\x50\x4b\x03\x04".       // Zip header
			"\x0a\x00".               // Version required for extraction
			"\x00\x00".               // General bit flag
			"\x00\x00".               // Compression method
			pack('V', $timestamp).    // Last mod time and date
			pack('V', crc32($name)).  // CRC32
			pack('V', 0).             // Compressed filesize
			pack('V', 0).             // Uncompressed filesize
			pack('v', strlen($name)). // Length of directory name
			pack('v', 0).             // Extra field length
			$name;                    // Directory name

		$this->dirs[] =
			"\x50\x4b\x01\x02".       // Zip header
			"\x00\x00".               // Version made by
			"\x0a\x00".               // Version required for extraction
			"\x00\x00".               // General bit flag
			"\x00\x00".               // Compression method
			pack('V', $timestamp).    // Last mod time and date
			pack('V', crc32($name)).  // CRC32
			pack('V', 0).             // Compressed filesize
			pack('V', 0).             // Uncompressed filesize
			pack('v', strlen($name)). // Length of directory name
			pack('v', 0).             // Extra field length
			// Data description
			pack('v', 0).             // CRC32
			pack('v', 0).             // Compressed filesize
			pack('v', 0).             // Uncompressed filesize
			pack('V', 16).            // Internal file attribute "directory"
			pack('V', $this->offset). // Directory offset
			$name;                    // Directory name

		// Set the new offset
		$this->offset = strlen(implode('', $this->data));
	}

	/**
	 * Method: add_file
	 *  Adds a file to a zip.
	 *
	 * Parameters:
	 *  file - name of file
	 */
	protected function add_file($file, $name)
	{
		// Fetch the timestamp
		$timestamp = date::unix2dos(filemtime($file));

		// Read the file
		$data = file_get_contents($file);

		// Gzip the data, use substr to fix a CRC bug
		$zdata = substr(gzcompress($data), 2, -4);

		$this->data[] =
			"\x50\x4b\x03\x04".       // Zip header
			"\x14\x00".               // Version required for extraction
			"\x00\x00".               // General bit flag
			"\x08\x00".               // Compression method
			pack('V', $timestamp).    // Last mod time and date
			pack('V', crc32($data)).  // CRC32
			pack('V', strlen($zdata)).// Compressed filesize
			pack('V', strlen($data)). // Uncompressed filesize
			pack('v', strlen($name)). // Length of file name
			pack('v', 0).             // Extra field length
			$name.                    // File name
			$zdata;                   // Compressed data

		$this->dirs[] =
			"\x50\x4b\x01\x02".       // Zip header
			"\x00\x00".               // Version made by
			"\x14\x00".               // Version required for extraction
			"\x00\x00".               // General bit flag
			"\x08\x00".               // Compression method
			pack('V', $timestamp).    // Last mod time and date
			pack('V', crc32($data)).  // CRC32
			pack('V', strlen($zdata)).// Compressed filesize
			pack('V', strlen($data)). // Uncompressed filesize
			pack('v', strlen($name)). // Length of file name
			pack('v', 0).             // Extra field length
			// End "local file header"
			// Start "data descriptor"
			pack('v', 0).             // CRC32
			pack('v', 0).             // Compressed filesize
			pack('v', 0).             // Uncompressed filesize
			pack('V', 32).            // External file attribute "file"
			pack('V', $this->offset). // Directory offset
			$name;                    // File name

		// Set the new offset
		$this->offset = strlen(implode('', $this->data));
	}

} // End Archive_Zip_Driver Class