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
 * Kohana XML Helpers
 *
 * @package		Kohana
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/xml_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Convert Reserved XML characters to Entities
 *
 * @access	public
 * @param	string
 * @return	string
 */	
function xml_convert($str)
{
	//Convert ampersands to entities only if they're not part of an existing entity
	$str = preg_replace('/&(?!(?:#\d+|[a-z]+);)/i', '&amp;', $str);
	   
	// Convert: < > ' " -
	$str = str_replace(
		array('<', '>', '\'', '"', '-'),
		array('&lt;', '&gt;', '&#39;', '&quot;', '&#45;'),
		$str
	);
	   
	return $str;
}


?>