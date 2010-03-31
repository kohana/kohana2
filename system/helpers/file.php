<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The file helper class provides convenience methods for manipulating
 * and operating on files.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class file_Core {

	/**
	 * This method attempts to retreive the mime type of a given file.
	 *
	 * The first function argument takes as its value a string and
	 * must be a qualified path + name to the file's location on the
	 * filesystem.
	 * 
	 * [!!] This method is not reliable because of short-comings in PHP's mime type detection facilities.
	 *
	 * @link http://php.net/manual/en/function.mime-content-type.php
	 * 
	 * ##### Example
	 *     
	 *     echo Kohana::debug(file::mime('/kohana/trunk/application/controllers/welcome.php'))
	 *     
	 *     // Output:
	 *     (string) text/x-php; charset=us-ascii
	 *
	 * @param   string $filename File path and name
	 * @return  mixed
	 */
	public static function mime($filename)
	{
		// Make sure the file is readable
		if ( ! (is_file($filename) AND is_readable($filename)))
			return FALSE;

		// Get the extension from the filename
		$extension = strtolower(substr(strrchr($filename, '.'), 1));

		if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension))
		{
			// Disable error reporting
			$ER = error_reporting(0);

			// Use getimagesize() to find the mime type on images
			$mime = getimagesize($filename);

			// Turn error reporting back on
			error_reporting($ER);

			// Return the mime type
			if (isset($mime['mime']))
				return $mime['mime'];
		}

		if (function_exists('finfo_open'))
		{
			// Use the fileinfo extension
			$finfo = finfo_open(FILEINFO_MIME);
			$mime  = finfo_file($finfo, $filename);
			finfo_close($finfo);

			// Return the mime type
			return $mime;
		}

		if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type'))
		{
			// Return the mime type using mime_content_type
			return mime_content_type($filename);
		}

		if ( ! KOHANA_IS_WIN)
		{
			// Attempt to locate use the file command, checking the return value
			if ($command = trim(exec('which file', $output, $return)) AND $return === 0)
			{
				return trim(exec($command.' -bi '.escapeshellarg($filename)));
			}
		}

		if ( ! empty($extension) AND is_array($mime = Kohana::config('mimes.'.$extension)))
		{
			// Return the mime-type guess, based on the extension
			return $mime[0];
		}

		// Unable to find the mime-type
		return FALSE;
	}

	/**
	 * This method splits a file into chunks by a given chunk size.
	 *
	 * The first function argument takes as its value a string and
	 * must be a qualified path + name to the file's location on the
	 * filesystem.
	 *
	 * The second function argument takes as its value a string or
	 * boolean `FALSE`; if a string is provided it must be a qualified
	 * path to the desired output directory.
	 *
	 * The third function argument takes as its value an integer
	 * representing in bytes the intended size of the file chunks.
	 * 
	 * ##### Example
	 *     
	 *     // Filesize is 9.9M
	 *     $file = 'the-dune-encyclopedia.pdf';
	 *     
	 *     echo Kohana::debug(file::split($file, FALSE, 1));
	 *     
	 *     // Output:
	 *     (integer) 10
	 * 
	 * ##### Directory Listing
	 *     
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.001
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.002
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.003
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.004
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.005
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.006
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.007
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.008
	 *     -rw-r--r--   1 _www     staff   1.0M Mar 31 12:54 the-dune-encyclopedia.pdf.009
	 *     -rw-r--r--   1 _www     staff   952K Mar 31 12:54 the-dune-encyclopedia.pdf.010
	 *
	 * @param   string  $filename   File (including its path) to be split
	 * @param   string  $output_dir Directory to output to, defaults to the same directory as the file
	 * @param   integer $piece_size Size, in MB, for each chunk to be
	 * @return  integer
	 */
	public static function split($filename, $output_dir = FALSE, $piece_size = 10)
	{
		// Find output dir
		$output_dir = ($output_dir == FALSE) ? pathinfo(str_replace('\\', '/', realpath($filename)), PATHINFO_DIRNAME) : str_replace('\\', '/', realpath($output_dir));
		
		$output_dir = rtrim($output_dir, '/').'/';
		
		// Extract the filename
		$base_name	= basename($filename);

		// Open files for reading
		$input_file = fopen($filename, 'rb');

		// Change the piece size to bytes
		$piece_size = 1024 * 1024 * (int) $piece_size; // Size in bytes

		// Set up reading variables
		$read  = 0; // Number of bytes read
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Split the file
		while ( ! feof($input_file))
		{
			// Open a new piece
			$piece_name = $output_dir.$base_name.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT);
			$piece_open = @fopen($piece_name, 'wb+') or die('Could not write piece '.$piece_name);

			// Fill the current piece
			while ($read < $piece_size AND $data = fread($input_file, $chunk))
			{
				fwrite($piece_open, $data) or die('Could not write to open piece '.$piece_name);
				$read += $chunk;
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare to open a new piece
			$read = 0;
			$piece++;

			// Make sure that piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded, try a larger piece size');
		}

		// Close input file
		fclose($input_file);

		// Returns the number of pieces that were created
		return ($piece - 1);
	}

	/**
	 * Join a split file into a whole file.
	 * 
	 * #### Example
	 * ##### Code
	 * 		$file_in = 'humpty_dumpty.mp3'; // from our last example
	 * 		$file_out = 'humpty_dumpty-back_together_again.mp3'; // output name
	 * 		echo file::join($file_in, $file_out);
	 * 
	 * ##### Returns
	 * 		4
	 * 
	 * ##### Directory Listing
	 * 		-rwxrwxrwx 1 www-data www-data 8186302 2008-05-06 20:11 humpty_dumpty.mp3
	 * 		-rw-r--r-- 1 www-data www-data 2097152 2008-05-06 20:15 humpty_dumpty.mp3.001
	 * 		-rw-r--r-- 1 www-data www-data 2097152 2008-05-06 20:15 humpty_dumpty.mp3.002
	 * 		-rw-r--r-- 1 www-data www-data 2097152 2008-05-06 20:15 humpty_dumpty.mp3.003
	 * 		-rw-r--r-- 1 www-data www-data 1894846 2008-05-06 20:15 humpty_dumpty.mp3.004
	 * 		-rw-r--r-- 1 www-data www-data 8186302 2008-05-06 20:17 humpty_dumpty-back_together_again.mp3
	 * 
	 * @param string $filename Split filename, without .000 extension
	 * @param string $output Output filename, if different then an the filename
	 * @return integer The number of pieces that were joined.
	 */
	public static function join($filename, $output = FALSE)
	{
		if ($output == FALSE)
			$output = $filename;

		// Set up reading variables
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Open output file
		$output_file = @fopen($output, 'wb+') or die('Could not open output file '.$output);

		// Read each piece
		while ($piece_open = @fopen(($piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT)), 'rb'))
		{
			// Write the piece into the output file
			while ( ! feof($piece_open))
			{
				fwrite($output_file, fread($piece_open, $chunk));
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare for a new piece
			$piece++;

			// Make sure piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded');
		}

		// Close the output file
		fclose($output_file);

		// Return the number of pieces joined
		return ($piece - 1);
	}

} // End file