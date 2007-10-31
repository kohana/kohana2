<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: array_helper
 *  Array helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class arr {
	
	/*
	 * Method: transform
	 *  Transforms a 2D array by swapping rows with columns.
	 *  Example, turns a 2x3 array into a 3x2 array
	 *
	 * Parameters:
	 *  source_array - the array to transform
	 *  keep_keys - keep the keys in the final transformed array. the sub arrays of the source array need to have the same key values.
	 *              if your subkeys might not match, you need to pass FALSE here!
	 *
	 * Returns:
	 *  The transformed array
	 */
	function transform($source_array, $keep_keys = TRUE)
	{
		$new_array = array();
		foreach ($source_array as $key => $value)
		{
			$value = ($keep_keys) ? $value : array_values($value);
			foreach ($value as $k => $v)
			{
				$new_array[$k][$key] = $v;
			}
		}
		
		return $new_array;
	}
	
	/*
	 * Method: remove
	 *  Removes a key from an array and returns the value
	 *
	 * Parameters:
	 *  key - to key to return
	 *  array - the array to work on
	 *
	 * Returns:
	 *  The value of the requested array key
	 */
	function remove($key, &$array)
	{
		if (!isset($array[$key]))
			return NULL;
			
		$temp = $array[$key];
		unset($array[$key]);
		return $temp;
	}
}