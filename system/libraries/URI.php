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
	 * @return	array
	 */
	public function segment_array()
	{
		$i = 1;
		$segment_array = array();

		foreach (self::$segments as $segment)
		{
			$segment_array[$i++] = $segment;
		}

		return $segment_array;
	}

	/**
	 * Returns the complete URI as a string
	 *
	 * @access	public
	 * @return	string
	 */
	public function string()
	{
		return implode('/', self::$segments);
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

} // End URI Class