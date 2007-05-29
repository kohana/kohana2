<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BlueFlame
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		BlueFlame
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeigniter.com/user_guide/license.html
 * @link		http://blueflame.ciforge.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * BlueFlame XML Helpers
 *
 * @package		BlueFlame
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