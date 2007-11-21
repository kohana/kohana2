<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: file
 *  File helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class file {

	/**
	 * Method: split
	 *  Split a file into pieces matching a specific size.
	 *
	 * Parameters:
	 *  filename   - file to be split
	 *  output_dir - directory to output to, defaults to the same directory as the file
	 *  piece_size - size, in MB, for each chunk to be
	 *
	 * Returns:
	 *  The number of pieces that were created.
	 */
	public static function split($filename, $output_dir = FALSE, $piece_size = 10)
	{
		// Find output dir
		$output_dir = ($output_dir == FALSE) ? pathinfo(str_replace('\\', '/', realpath($filename)), PATHINFO_DIRNAME) : str_replace('\\', '/', realpath($output_dir));
		$output_dir = rtrim($output_dir, '/').'/';

		// Open files for writing
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
			$piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT);
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
	 * Method: join
	 *  Join a split file into a whole file.
	 *
	 * Parameters:
	 *  filename  - split filename, without .000 extension
	 *  output    - output filename, if different then an the filename
	 *
	 * Returns:
	 *  The number of pieces that were joined.
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