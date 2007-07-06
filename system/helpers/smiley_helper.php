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
 * Kohana Smiley Helpers
 *
 * @package		Kohana
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/smiley_helper.html
 */

// ------------------------------------------------------------------------

/**
 * JS Insert Smiley
 *
 * Generates the javascrip function needed to insert smileys into a form field
 *
 * @access	public
 * @param	string	form name
 * @param	string	field name
 * @return	string
 */
function js_insert_smiley($form_name = '', $form_field = '')
{
return <<<EOF
<script type="text/javascript">
	function insert_smiley(smiley)
	{
		document.{$form_name}.{$form_field}.value += " " + smiley;
	}
</script>
EOF;
}

// ------------------------------------------------------------------------

/**
 * Get Clickable Smileys
 *
 * Returns an array of image tag links that can be clicked to be inserted
 * into a form field.
 *
 * @access	public
 * @param	string	the URL to the folder containing the smiley images
 * @return	array
 */
function get_clickable_smileys($image_url = '', $smileys = NULL)
{
	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $str;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = rtrim($image_url, '/') .'/';

	$used = array();
	foreach ($smileys as $key => $val)
	{
		// Keep duplicates from being used, which can happen if the
		// mapping array contains multiple identical replacements.  For example:
		// :-) and :) might be replaced with the same image so both smileys
		// will be in the array.
		if (isset($used[$smileys[$key][0]]))
		{
			continue;
		}

		$link[] = "<a href=\"javascript:void(0);\" onClick=\"insert_smiley('".$key."')\"><img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" style=\"border:0;\" /></a>";

		$used[$smileys[$key][0]] = TRUE;
	}

	return $link;
}

// ------------------------------------------------------------------------

/**
 * Parse Smileys
 *
 * Takes a string as input and swaps any contained smileys for the actual image
 *
 * @access	public
 * @param	string	the text to be parsed
 * @param	string	the URL to the folder containing the smiley images
 * @return	string
 */
function parse_smileys($str = '', $image_url = '', $smileys = NULL)
{
	if ($image_url == '')
	{
		return $str;
	}

	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $str;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = rtrim($image_url, '/') .'/';

	foreach ($smileys as $key => $val)
	{
		$str = str_replace($key, "<img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" style=\"border:0;\" />", $str);
	}

	return $str;
}

// ------------------------------------------------------------------------

/**
 * Get Smiley Array
 *
 * Fetches the config/smiley.php file
 *
 * @access	private
 * @return	mixed
 */
function _get_smiley_array()
{
	if(($abs_resource_path = find_resource('smileys'.EXT,'config')) === FALSE)
		return FALSE;

	include($abs_resource_path);

	if ( ! isset($smileys) OR ! is_array($smileys))
		return FALSE;

	return $smileys;
}

?>