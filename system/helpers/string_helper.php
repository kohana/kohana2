<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana String Helpers
 *
 * @package		Kohana
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/string_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Trim Slashes
 *
 * Removes any leading/traling slashes from a string:
 *
 * /this/that/theother/
 *
 * becomes:
 *
 * this/that/theother
 *
 * @access	public
 * @param	string
 * @return	string
 */
function trim_slashes($str)
{
    return trim($str, '/');
}

// ------------------------------------------------------------------------

/**
 * Reduce Double Slashes
 *
 * Converts double slashes in a string to a single slash,
 * except those found in http://
 *
 * http://www.some-site.com//index.php
 *
 * becomes:
 *
 * http://www.some-site.com/index.php
 *
 * @access	public
 * @param	string
 * @return	string
 */
function reduce_double_slashes($str)
{
	return preg_replace('#(?<!:)//+#', '/', $str);
}

// ------------------------------------------------------------------------

/**
 * Create a Random String
 *
 * Useful for generating passwords or hashes.
 *
 * @access	public
 * @param	string 	type of random string.  Options: alunum, numeric, nozero, unique
 * @param	integer	number of characters
 * @return	string
 */
function random_string($type = FALSE, $len = 8)
{
	if ($type == FALSE) $type = 'alnum';
	
	if ($type == 'unique')
	{
		$str = md5(uniqid(mt_rand()));
	}
	else
	{
		$pool = '';
		switch ($type)
		{
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'numeric':
				$pool = '0123456789';
				break;
			case 'nozero':
				$pool = '123456789';
				break;
		}
		
		$str = '';
		$max = strlen($pool)-1;
		for ($i=0; $i < $len; $i++)
		{
			$str .= substr($pool, rand(0, $max), 1);
		}
	}
	
	return $str;
}
// ------------------------------------------------------------------------

/**
 * Alternator
 *
 * Allows strings to be alternated.  See docs...
 *
 * @access	public
 * @param	string (as many parameters as needed)
 * @return	string
 */
function alternator()
{
	static $i;

	if (func_num_args() == 0)
	{
		$i = 0;
		return '';
	}
	$args = func_get_args();
	return $args[($i++ % count($args))];
}

// ------------------------------------------------------------------------

/**
 * Repeater function
 * 
 * This function is deprecated. Please use PHP's native str_repeat().
 *
 * @access	public
 * @param	string
 * @param	integer	number of repeats
 * @return	string
 */
function repeater($str, $num = 1)
{
	return str_repeat($str, $num);
}

?>