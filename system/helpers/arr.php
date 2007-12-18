<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: array_helper
 *  Array helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class arr_Core {
	
	/**
	 * Method: rotate
	 *  Rotates a 2D array clockwise.
	 *  Example, turns a 2x3 array into a 3x2 array
	 *
	 * Parameters:
	 *  source_array - the array to rotate
	 *  keep_keys - keep the keys in the final rotated array. the sub arrays of the source array need to have the same key values.
	 *              if your subkeys might not match, you need to pass FALSE here!
	 *
	 * Returns:
	 *  The transformed array
	 */
	public function rotate($source_array, $keep_keys = TRUE)
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
	
	/**
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
	public function remove($key, & $array)
	{
		if ( ! isset($array[$key]))
			return NULL;

		$val = $array[$key];
		unset($array[$key]);

		return $val;
	}

	/**
	 * Because PHP does not have this function.
	 *
	 * @param   array   array to unshift
	 * @param   string  key to unshift
	 * @param   mixed   value to unshift
	 * @return  array
	 */
	public function unshift_assoc( array & $array, $key, $val)
	{
		$array = array_reverse($array, TRUE);
		$array[$key] = $val;
		$array = array_reverse($array, TRUE);

		return $array;
	}

	/**
	 * Binary search algorithm.
	 *
	 * @param  mixed    the value to search for
	 * @param  array    an array of values to search in
	 * @param  boolean  return false, or the nearest value
	 * @param  mixed    sort the array by the requested sorting method (available types are bubble, insertion, quick, selection, merge)
	 * @return integer
	 */
	public function binary_search($needle, $haystack, $nearest = FALSE, $sort = FALSE)
	{
		if ($sort != FALSE AND is_string($sort))
		{
			$method = $sort.'_sort';
			self::$method($haystack);
		}

		$high = count($haystack);
		$low = 0;

		while ($high - $low > 1)
		{
			$probe = ($high + $low) / 2;
			if ($haystack[$probe] < $needle)
			{
				$low = $probe;
			}
			else
			{
				$high = $probe;
			}
		}

		if ($high == count($haystack) OR $haystack[$high] != $needle)
		{
			if ($nearest == FALSE)
				return FALSE;

			// return the nearest value
			$high_distance = $haystack[ceil($low)] - $needle;
			$low_distance = $needle - $haystack[floor($low)];

			return ($high_distance >= $low_distance) ? $haystack[ceil($low)] : $haystack[floor($low)];
		}
		else
			return $high;
	}

	/**
	 * Bubble sort algorithm.
	 *
	 * @param  array the array to sort
	 */
	public function bubble_sort(&$array)
	{
		$size = count($array);
		do
		{
			$swapped = FALSE;
			$size--;
			for ($i = 0; $i < $size; $i++)
			{
				if ($array[$i] > $array[$i+1])
				{
					// Swap the values
					$temp = $array[$i+1];
					$array[$i+1] = $array[$i];
					$array[$i] = $temp;
					$swapped = TRUE;
				}
			}
		} while ($swapped);
	}

	/**
	 * Insertion sort algorithm.
	 *
	 * @param  array the array to sort
	 */
	public function insertion_sort(&$array)
	{
		for ($i = 0; $i < count($array); $i++)
		{
			$value = $array[$i];
			$j = $i - 1;
			while ($j >= 0 AND $array[$j] > $value)
			{
				$array[$j+1] = $array[$j];
				$j--;
			}
			$array[$j+1] = $value;
		}
	}

	/**
	 * Quick sort algorithm.
	 *
	 * @param  array the array to sort
	 * @param  integer low position to start at
	 * @param  integer high position to start at
	 * @param  integer minumum number of items to sort. If below the value, we go to insertion sort since its faster on small datasets
	 */
	public function quick_sort(&$array, $low_pos, $high_pos, $cutoff = 1500)
	{
		// Quick sort is actually slow on small data sets
		if($low_pos + $cutoff > $high_pos)
		{ 
			self::insertion_sort($array); 
		}
		else
		{
			$left = $low_pos;
			$right = $high_pos;
			$pivot = $array[$left];
			while ($left < $right)
			{ 
				while (($array[$right] >= $pivot) && ($left < $right))
				{
					$right--;
				} 
				if ($left != $right)
				{
					$array[$left] = $array[$right];
					$left++;
				}
				while (($array[$left] <= $pivot) && ($left < $right)) 
				{
					$left++; 
					if ($left != $right)
					{ 
						$array[$right] = $array[$left]; 
						$right--; 
					} 
				} 
				$array[$left] = $pivot; 
				$pivot = $left; 
				if ($low_pos < $pivot)
				{
					self::qsort($array, $low_pos, $pivot, $cutoff);
				}
				if ($high_pos > $pivot)
				{
					self::qsort($array, $pivot+1, $high_pos, $cutoff); 
		        }
			}
		}
	}

	/**
	 * Selection sort algorithm.
	 *
	 * @param  array the array to sort
	 */
	public function selection_sort(&$array)
	{
		
	}

	/**
	 * Merge sort algorithm.
	 *
	 * @param  array the array to sort
	 */
	public function merge_sort(&$array)
	{
		
	}
} // End arr