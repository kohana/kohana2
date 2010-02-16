<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Array helper class assists in transforming arrays.
 * 
 * *In order to use it, the class name is 'arr' instead of 'array'*
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class arr_Core {

	/**
	 * Return a callback array from a string, eg: limit[10,20] would become
	 * array('limit', array('10', '20'))
	 *
	 * ##### Example
	 *
	 *     print_r(arr::callback_string('limit[10,20]'));
	 *
	 *     // Outputs:
	 *     array('limit', array('10', '20'))
	 *
	 * @param   string  callback string
	 * @return  array
	 */
	public static function callback_string($str)
	{
		// command[param,param]
		if (preg_match('/([^\[]*+)\[(.+)\]/', (string) $str, $match))
		{
			// command
			$command = $match[1];

			// param,param
			$params = preg_split('/(?<!\\\\),/', $match[2]);
			$params = str_replace('\,', ',', $params);
		}
		else
		{
			// command
			$command = $str;

			// No params
			$params = NULL;
		}

		return array($command, $params);
	}

	/**
	 * Rotates a 2D array clockwise.
	 * Example, turns a 2x3 array into a 3x2 array.
	 *
	 * ##### Example
	 *
	 *     // Please note that the echo statements are for display only
	 *     $optical_discs = array
	 *     	(
	 *     	  'CD'  => array('700', '780'),
	 *     	  'DVD' => array('4700','650'),
	 *     	  'BD'  => array('25000','405')
	 *      );
	 *
	 *     echo Kohana::debug($optical_discs);
	 *     $optical_discs = arr::rotate(&$optical_discs, FALSE);
	 *
	 *     echo '<br /><br />';
	 *     echo Kohana::debug($optical_discs);
	 *
	 *     // Output:
	 *     Array
	 *     (
	 *         [CD] => Array
	 *             (
	 *                 [0] => 700
	 *                 [1] => 780
	 *             )
	 *         [DVD] => Array
	 *             (
	 *                 [0] => 4700
	 *                 [1] => 650
	 *             )
	 *         [BD] => Array
	 *             (
	 *                 [0] => 25000
	 *                 [1] => 405
	 *             )
	 *     )
	 *     Array
	 *     (
	 *         [0] => Array
	 *             (
	 *                 [CD] => 700
	 *                 [DVD] => 4700
	 *                 [BD] => 25000
	 *             )
	 *         [1] => Array
	 *             (
	 *                 [CD] => 780
	 *                 [DVD] => 650
	 *                 [BD] => 405
	 *             )
	 *     )
	 *
	 * @param   array    array to rotate
	 * @param   boolean  keep the keys in the final rotated array. the sub arrays of the source array need to have the same key values.
	 *                   if your subkeys might not match, you need to pass FALSE here!
	 * @return  array
	 */
	public static function rotate($source_array, $keep_keys = TRUE)
	{
		$new_array = array();
		foreach ($source_array as $key => $value)
		{
			$value = ($keep_keys === TRUE) ? $value : array_values($value);
			foreach ($value as $k => $v)
			{
				$new_array[$k][$key] = $v;
			}
		}

		return $new_array;
	}

	/**
	 * Removes a key from an array and returns the value.
	 *
	 * ##### Example
	 *
	 *     // Please note that the echo statements are for display only
	 *     $optical_discs = array
	 *     	(
	 *     	  'CD'  => array('700', '780'),
	 *     	  'DVD' => array('4700','650'),
	 *     	  'BD'  => array('25000','405')
	 *      );
	 *
	 *     echo Kohana::debug($optical_discs);
	 *     $cd = arr::remove('CD', $optical_discs);
	 *
	 *     echo '<br /><br />';
	 *     echo '<br />';
	 *     echo  Kohana::debug($cd);
	 *     echo  '<br />';
	 *     echo  Kohana::debug($optical_discs);
	 *
	 *     // Output:
	 *     Array
	 *     (
	 *         [CD] => Array
	 *             (
	 *                 [0] => 700
	 *                 [1] => 780
	 *             )
	 *         [DVD] => Array
	 *             (
	 *                 [0] => 4700
	 *                 [1] => 650
	 *             )
	 *         [BD] => Array
	 *             (
	 *                 [0] => 25000
	 *                 [1] => 405
	 *             )
	 *     )
	 *
	 *     Array
	 *     (
	 *         [0] => 700
	 *         [1] => 780
	 *     )
	 *      
	 *     Array
	 *     (
	 *         [DVD] => Array
	 *             (
	 *                 [0] => 4700
	 *                 [1] => 650
	 *             )
	 *         [BD] => Array
	 *             (
	 *                 [0] => 25000
	 *                 [1] => 405
	 *             )
	 *     )
	 *
	 * @param   string  key to return
	 * @param   array   array to work on
	 * @return  mixed   value of the requested array key
	 */
	public static function remove($key, & $array)
	{
		if ( ! array_key_exists($key, $array))
			return NULL;

		$val = $array[$key];
		unset($array[$key]);

		return $val;
	}


	/**
	 * Extract one or more keys from an array. Each key given after the first
	 * argument (the array) will be extracted. Keys that do not exist in the
	 * search array will be NULL in the extracted data.
	 *
	 * ##### Example
	 *
	 *     // Please note that the echo statements are for display only
	 *     $optical_discs = array
	 *     	(
	 *     	  'CD'  => array('700', '780'),
	 *     	  'DVD' => array('4700','650'),
	 *     	  'BD'  => array('25000','405')
	 *      );
	 *
	 *      $optical_discs = arr::extract($optical_discs, 'DVD', 'Bluray');
	 *      echo Kohana::debug($optical_discs);
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [DVD] => Array
	 *              (
	 *                  [0] => 4700
	 *                  [1] => 650
	 *              )
	 *          [Bluray] => NULL
	 *      )
	 *
	 * @param   array   array to search
	 * @param   string  key name
	 * @return  array
	 */
	public static function extract(array $search, $keys)
	{
		// Get the keys, removing the $search array
		$keys = array_slice(func_get_args(), 1);

		$found = array();
		foreach ($keys as $key)
		{
			$found[$key] = isset($search[$key]) ? $search[$key] : NULL;
		}

		return $found;
	}

	/**
	 * Get the value of array[key]. If it doesn't exist, return default.
	 *
	 * ##### Example
	 *
	 *     $optical_discs = array
	 *     	(
	 *     	  'CD'  => array('700', '780'),
	 *     	  'DVD' => array('4700','650'),
	 *     	  'BD'  => array('25000','405')
	 *      );
	 *
	 *      // Extract a key or return a default value (NULL if default is not passed a parameter)
	 *      echo Kohana::debug(arr::get($optical_discs, 'CD', 'Not-existent!'));
	 *      echo Kohana::debug(arr::get($optical_discs, 'BLRY', 'Non-existent!'));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => 700
	 *          [1] => 780
	 *      )
	 *
	 *      Non-existent!
	 * 
	 * @param   array   array to search
	 * @param   string  key name
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function get(array $array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}

	/**
	 * Replace the value of an association by it's key in an associative array.
	 *
	 * ##### Example
	 *
	 *      $fruits = array('fruit1' => 'apple', 'fruit2' => 'mango', 'fruit3' => 'pineapple');
	 *      arr::unshift_assoc($fruits, 'fruit1', 'starwberry');
	 *      echo Kohana::debug($fruits);
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [fruit1] => strawberry
	 *          [fruit2] => mango
	 *          [fruit3] => pineapple
	 *      )
	 *
	 * @param   array   array to unshift
	 * @param   string  key to unshift
	 * @param   mixed   value to unshift
	 * @return  array
	 * @todo This function is badly named, IMHO, and none of the other array helper methods pass by reference!
	 *       I didn't change it because the API is locked.
	 */
	public static function unshift_assoc( array & $array, $key, $val)
	{
		$array = array_reverse($array, TRUE);
		$array[$key] = $val;
		$array = array_reverse($array, TRUE);

		return $array;
	}

	/**
	 * Because PHP does not have this function, and array_walk_recursive creates
	 * references in arrays and is not truly recursive.
	 *
	 * ##### Example
	 *
	 *      // Pre PHP 5.3
	 *      function plus_one($value)
	 *      {
	 *          return $value + 1;
	 *      }
	 *
	 *      echo Kohana::debug(arr::map_recursive(array($this, 'plus_one'), array('a' => 1, 'b' => 2, 'c' => array(3, 4), 'd' => array('e' => 5))));
	 *
	 *      // Or, you can use the lambda function syntax of PHP 5.3
	 *      echo Kohana::debug(arr::map_recursive(function($value){return $value+1;}, array('a' => 1, 'b' => 2, 'c' => array(3, 4), 'd' => array('e' => 5))));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [a] => 2
	 *          [b] => 3
	 *          [c] => Array
	 *              (
	 *                  [0] => 4
	 *                  [1] => 5
	 *              )
	 *          [d] => Array
	 *              (
	 *                  [e] => 6
	 *              )
	 *      )
	 *
	 * @param   mixed  callback to apply to each member of the array
	 * @param   array  array to map to
	 * @return  array
	 */
	public static function map_recursive($callback, array $array)
	{
		foreach ($array as $key => $val)
		{
			// Map the callback to the key
			$array[$key] = is_array($val) ? arr::map_recursive($callback, $val) : call_user_func($callback, $val);
		}

		return $array;
	}

	/**
	 * Emulates array_merge_recursive, but appends numeric keys and replaces
	 * associative keys, instead of appending all keys.
	 *
	 * ##### Example
	 *
	 *      echo Kohana::debug(arr::merge(array('a', 'b'), array('c', 'd'), array('e' => array('f', 'g'))));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => a
	 *          [1] => b
	 *          [2] => c
	 *          [3] => d
	 *          [e] => Array
	 *              (
	 *                  [0] => f
	 *                  [1] => g
	 *              )
	 *      )
	 *
	 * @param   array  any number of arrays
	 * @return  array
	 */
	public static function merge()
	{
		$total = func_num_args();

		$result = array();
		for ($i = 0; $i < $total; $i++)
		{
			foreach (func_get_arg($i) as $key => $val)
			{
				if (isset($result[$key]))
				{
					if (is_array($val))
					{
						// Arrays are merged recursively
						$result[$key] = arr::merge($result[$key], $val);
					}
					elseif (is_int($key))
					{
						// Indexed arrays are appended
						array_push($result, $val);
					}
					else
					{
						// Associative arrays are replaced
						$result[$key] = $val;
					}
				}
				else
				{
					// New values are added
					$result[$key] = $val;
				}
			}
		}

		return $result;
	}

	/**
	 * Overwrites an array with values from input array(s).
	 * Non-existing keys will not be appended!
	 *
	 * ##### Example
	 *
	 *      $array1 = array('fruit1' => 'apple', 'fruit2' => 'mango', 'fruit3' => 'pineapple');
	 *      $array2 = array('fruit1' => 'strawberry', 'fruit4' => 'coconut');
	 *      echo Kohana::debug(arr::overwrite($array1, $array2));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [fruit1] => strawberry
	 *          [fruit2] => mango
	 *          [fruit3] => pineapple
	 *      )
	 *
	 * @param   array   key array
	 * @param   array   input array(s) that will overwrite key array values
	 * @return  array
	 */
	public static function overwrite($array1, $array2)
	{
		foreach (array_intersect_key($array2, $array1) as $key => $value)
		{
			$array1[$key] = $value;
		}

		if (func_num_args() > 2)
		{
			foreach (array_slice(func_get_args(), 2) as $array2)
			{
				foreach (array_intersect_key($array2, $array1) as $key => $value)
				{
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}

	/**
	 * Recursively convert an array to an object.
	 *
	 * ##### Example
	 *
	 *      $array = arr::to_object(array('test' => 13));
	 *      echo $array ->test;
	 *      echo Kohana::debug($array);
	 *
	 *      // Output:
	 *      13
	 *      (object) stdClass Object
	 *      (
	 *          [test] => 13
	 *      )
	 *
	 * @param   array   array to convert
	 * @return  object
	 */
	public static function to_object(array $array, $class = 'stdClass')
	{
		$object = new $class;

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				// Convert the array to an object
				$value = arr::to_object($value, $class);
			}

			// Add the value to the object
			$object->{$key} = $value;
		}

		return $object;
	}

	/**
	 * Returns specific key/column from an array of objects.
	 *
	 * ##### Example
	 *
	 *      $arr1	= array('reptile' => array('one' => 'snake'), 'mammal' => array('one' => 'dog'));
	 *      echo Kohana::debug(arr::pluck('one', $arr1));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [reptile] => snake
	 *          [mammal] => dog
	 *      )
	 *      
	 * @param string|integer $key The key or column number to pluck from each object.
	 * @param array $array        The array of objects to pluck from.
	 * @return array
	 */
	public static function pluck($key, $array)
	{
		$result = array();
		foreach ($array as $i => $object)
		{
			$result[$i] = isset($object[$key]) ? $object[$key] : NULL;
		}
		return $result;
	}

	/**
	 * Returns a slice of the array (like array_slice()) but resets
	 * the original array to the rest of the array that wasn't in the
	 * slice.
	 *
	 * ##### Example
	 *
	 *      $arr1	= array('cat', 'dog', 'horse', 'cow', 'llama', 'giraffe');
	 *      $arr2	= array('cat', 'dog', 'horse', 'cow', 'llama', 'giraffe');
	 
	 *      // Returning the slice
	 *      echo Kohana::debug(arr::slice($arr1, 0, 2));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => cat
	 *          [1] => dog
	 *      )
	 *
	 *      // Rest of the array has been shortened
	 *      echo Kohana::debug($arr1);
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => horse
	 *          [1] => cow
	 *          [2] => llama
	 *          [3] => giraffe
	 *      )
	 *
	 *      // It's also intelligent and preserves arrays
	 *      echo Kohana::debug(arr::slice($arr2, 2, 2));
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => horse
	 *          [1] => cow
	 *      )
	 *
	 *      echo Kohana::debug($arr2);
	 *
	 *      // Output:
	 *      (array) Array
	 *      (
	 *          [0] => cat
	 *          [1] => dog
	 *          [2] => llama
	 *          [3] => giraffe
	 *      )
	 *      
	 * @param	array	$array	Pass by reference array to be sliced
	 * @param	integer	$offset	Array index to offset by
	 * @param	integer	$limit	Number of elements to slice from the offset
	 * @return	array
	 */
	public static function slice(&$array, $offset, $limit=NULL)
	{
		// Make limit optional - but compatible with our pass by
		// reference implementation.
		if (is_null($limit))
		{
			$limit	= count($array) - $offset;
		}
		
		// This part is easy
		$slice	= array_slice($array, $offset, $limit);
		
		// Figure out what we are doing with the index and slice n'
		// dice accordingly
		if ($offset > 0)
		{
			$arr	= array_slice($array, 0, $offset);
			
			if (count($array) > ($offset + $limit))
			{
				$arr	= array_merge($arr, array_slice($array, $offset + $limit, count($array) - ($offset + $limit)));
			}
		}
		else
		{
			$arr	= array_slice($array, $limit, null);
		}
		
		// Assign our left over array to the reference variable
		$array	= $arr;
		
		return $slice;
	}
} // End arr
