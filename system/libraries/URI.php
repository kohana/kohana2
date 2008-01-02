<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: URI
 *
 * Kohana Source Code:
 *  author    - Rick Ellis, Kohana Team
 *  copyright - Copyright (c) 2006, EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
 */
class URI_Core extends Router {

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		Log::add('debug', 'URI library initialized.');
	}

	/**
	 * Method: segment
	 *  Retrieve a specific URI segment.
	 *
	 * Parameters:
	 *  index   - segment number or label
	 *  default - default value returned if segment does not exist
	 *
	 * Returns:
	 *   Value of segment
	 */
	public function segment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, self::$segments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(self::$segments[$index]) ? self::$segments[$index] : $default;
	}

	/**
	 * Method: rsegment
	 *  Retrieve a specific routed URI segment.
	 *
	 * Parameters:
	 *  index   - segment number or label
	 *  default - default value returned if segment does not exist
	 *
	 * Returns:
	 *   Value of segment
	 */
	public function rsegment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, self::$rsegments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(self::$rsegments[$index]) ? self::$rsegments[$index] : $default;
	}
	
	/**
	 * Method: argument
	 *  Retrieve a specific URI argument. This is the part of the segments that does not indicate controller
	 *  or method
	 *
	 * Parameters:
	 *  index   - argument number or label
	 *  default - default value returned if segment does not exist
	 *
	 * Returns:
	 *   Value of segment
	 */
	public function argument($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, self::$arguments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(self::$arguments[$index]) ? self::$arguments[$index] : $default;
	}

	/**
	 * Method: segment_array
	 *  Returns an array containing all the URI segments.
	 *
	 * Parameters:
	 *  offset      - segment offset
	 *  associative - return an associative array
	 *
	 * Returns:
	 *   Array of URI segments
	 */
	public function segment_array($offset = 0, $associative = FALSE)
	{
		$segment_array = self::$segments;
		array_unshift($segment_array, 0);
		$segment_array = array_slice($segment_array, $offset + 1, $this->total_segments(), TRUE);

		if ( ! $associative)
			return $segment_array;

		$segment_array_assoc = array();

		foreach (array_chunk($segment_array, 2) as $pair)
		{
			$segment_array_assoc[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $segment_array_assoc;
	}

	/**
	 * Method: rsegment_array
	 *  Returns an array containing all the re-routed URI segments.
	 *
	 * Parameters:
	 *  offset      - rsegment offset
	 *  associative - return an associative array
	 *
	 * Returns:
	 *   Array of re-routed URI segments
	 */
	public function rsegment_array($offset = 0, $associative = FALSE)
	{
		$segment_array = self::$rsegments;
		array_unshift($segment_array, 0);
		$segment_array = array_slice($segment_array, $offset + 1, $this->total_segments(), TRUE);

		if ( ! $associative)
			return $segment_array;

		$segment_array_assoc = array();

		foreach (array_chunk($segment_array, 2) as $pair)
		{
			$segment_array_assoc[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $segment_array_assoc;
	}
	
	/**
	 * Method: argument_array
	 *  Returns an array containing all the URI arguments.
	 *
	 * Parameters:
	 *  offset      - segment offset
	 *  associative - return an associative array
	 *
	 * Returns:
	 *   Array of URI segment arguments
	 */
	public function argument_array($offset = 0, $associative = FALSE)
	{
		$argument_array = self::$arguments;
		array_unshift($argument_array, 0);
		$argument_array = array_slice($argument_array, $offset + 1, $this->total_arguments(), TRUE);

		if ( ! $associative)
			return $argument_array;

		$argument_array_assoc = array();

		foreach (array_chunk($argument_array, 2) as $pair)
		{
			$argument_array_assoc[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $argument_array_assoc;
	}
	
	/**
	 * Method: string
	 *  Returns the complete URI as a string.
	 *
	 * Returns:
	 *   Full URI as string
	 */
	public function string()
	{
		return self::$current_uri;
	}

	/**
	 * Method: __toString
	 *  Magic method for converting an object to a string.
	 *
	 * Returns:
	 *  Full URI as string
	 */	
	public function __toString()
	{
		return $this->string();
	}

	/**
	 * Method: total_segments
	 *  Returns the total number of URI segments.
	 *
	 * Returns:
	 *   Total number of URI segments
	 */
	public function total_segments()
	{
		return count(self::$segments);
	}

	/**
	 * Method: total_rsegments
	 *  Returns the total number of re-routed URI segments.
	 *
	 * Returns:
	 *   Total number of re-routed URI segments
	 */
	public function total_rsegments()
	{
		return count(self::$rsegments);
	}
	
	/**
	 * Method: total_arguments
	 *  Returns the total number of URI arguments.
	 *
	 * Returns:
	 *   Total number of URI arguments
	 */
	public function total_arguments()
	{
		return count(self::$arguments);
	}

	/**
	 * Method: last_segment
	 *  Returns the last URI segment.
	 *
	 * Returns:
	 *   Last URI segment
	 */
	public function last_segment($default = FALSE)
	{
		if ($this->total_segments() < 1)
			return $default;
		
		return end(self::$segments);
	}

	/**
	 * Method: last_rsegment
	 *  Returns the last re-routed URI segment.
	 *
	 * Returns:
	 *   Last re-routed URI segment
	 */
	public function last_rsegment($default = FALSE)
	{
		if ($this->total_rsegments() < 1)
			return $default;
		
		return end(self::$rsegments);
	}

} // End URI Class