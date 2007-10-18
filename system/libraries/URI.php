<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * URI Class
 *
 * @category    Libraries
 * @author      Rick Ellis, Kohana Team
 * @copyright   Copyright (c) 2006, EllisLab, Inc.
 * @license     http://www.codeigniter.com/user_guide/license.html
 * @link        http://kohanaphp.com/user_guide/en/libraries/uri.html
 */
class URI_Core extends Router {

	public function __construct()
	{
		if ( ! empty($_GET))
		{
			self::$query_string = '?';

			foreach($_GET as $key => $val)
			{
				self::$query_string .= $key.'='.rawurlencode($val);
			}
		}
	}

	/**
	 * Retrieve a specific URI segment
	 *
	 * @access	public
	 * @param	integer
	 * @param	mixed
	 * @return	mixed
	 */
	public function segment($index = 1, $default = FALSE)
	{
		$index = (int) $index - 1;

		return isset(self::$segments[$index]) ? self::$segments[$index] : $default;
	}

	/**
	 * Retrieve a specific routed URI segment
	 *
	 * @access	public
	 * @param	integer
	 * @param	mixed
	 * @return	mixed
	 */
	public function rsegment($index = 1, $default = FALSE)
	{
		$index = (int) $index - 1;

		return isset(self::$rsegments[$index]) ? self::$rsegments[$index] : $default;
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
	
	public function __toString()
	{
		return $this->string();
	}

	/**
	 * Returns the URI segment that is preceded by a certain other segment (label)
	 *
	 * @access	public
	 * @param	string
	 * @param	mixed
	 * @return	mixed
	 */
	public function label($label, $default = FALSE)
	{
		if (($key = array_search($label, self::$segments)) === FALSE)
			return $default;

		return $this->segment($key + 2, $default);
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
	 * @param	mixed
	 * @return	string
	 */
	public function last_segment($default = FALSE)
	{
		if ($this->total_segments() < 1)
			return $default;
		
		return end(self::$segments);
	}

} // End URI Class