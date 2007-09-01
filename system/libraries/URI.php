<?php defined('SYSPATH') or die('No direct access allowed.');

class URI_Core extends Router {

	/**
	 * Retrieve a specific URI segment
	 *
	 * @access	public
	 * @param	int
	 * @param	mixed
	 * @return	mixed
	 */
	public function segment($index = 1, $default = FALSE)
	{
		$index = (int) $index - 1;

		return isset(self::$segments[$index]) ? self::$segments[$index] : $default;
	}

	/**
	 * Returns an array containing all the URI segments
	 *
	 * @access	public
	 * @param	integer
	 * @param	boolean
	 * @return	array
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
	 * Returns the complete URI as a string
	 *
	 * @access	public
	 * @return	string
	 */
	public function string()
	{
		return self::$current_uri;
	}
	
	/**
	 * Returns the total number of URI segments
	 *
	 * @access	public
	 * @return	integer
	 */
	public function total_segments()
	{
		return count(self::$segments);
	}
	
	/**
	 * Returns the last URI segment
	 *
	 * @access	public
	 * @return	string
	 */
	public function last_segment()
	{
		return self::$segments[$this->total_segments() - 1];
	}

} // End URI Class