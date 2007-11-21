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
	 * Constructor: __construct
	 *  Detects current query string.
	 */
	public function __construct()
	{
		if ( ! empty($_GET))
		{
			self::$query_string = '?';

			foreach($_GET as $key => $val)
			{
				if (is_array($val))
				{
					foreach($val as $sub_key => $sub_val)
					{
						// Integer subkeys are numerically indexed arrays
						$sub_key = is_int($sub_key) ? '[]' : '['.$sub_key.']';

						self::$query_string .= $key.rawurlencode($sub_key).'='.rawurlencode($sub_val).'&';
					}
				}
				else
				{
					self::$query_string .= $key.'='.rawurlencode($val).'&';
				}
			}

			// Remove the ending ampersand
			self::$query_string = rtrim(self::$query_string, '&');
		}
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

} // End URI Class