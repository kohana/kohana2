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
 * Kohana Security Helpers
 *
 * @package		Kohana
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/helpers/security_helper.html
 */

// ------------------------------------------------------------------------

/**
 * XSS Filtering
 *
 * @access	public
 * @param	string
 * @param	string	the character set of your data
 * @return	string
 */
function xss_clean($str, $charset = 'ISO-8859-1')
{
	$CI =& get_instance();
	return $CI->input->xss_clean($str, $charset);
}

// --------------------------------------------------------------------

/**
 * Hash encode a string
 *
 * @access	public
 * @param	string
 * @return	string
 */
function dohash($str, $type = 'sha1')
{
	if ($type == 'sha1')
	{
		if ( ! function_exists('sha1'))
		{
			if ( ! function_exists('mhash'))
			{
				require_once(BASEPATH.'libraries/Sha1'.EXT);
				$SH = new CI_SHA;
				return $SH->generate($str);
			}
			else
			{
				return bin2hex(mhash(MHASH_SHA1, $str));
			}
		}
		else
		{
			return sha1($str);
		}
	}
	else
	{
		return md5($str);
	}
}

// ------------------------------------------------------------------------

/**
 * Strip Image Tags
 *
 * @access	public
 * @param	string
 * @return	string
 */
function strip_image_tags($str)
{
	$str = preg_replace('#<img.*?(?:src\s*=\s*["\'](.*?)["\'].*?)?>#is', '$1', $str);
	$str = preg_replace('#<img.*?(?:src\s*=\s*(.*?).*?)?>#is', '$1', $str);

	return trim($str);
}

// ------------------------------------------------------------------------

/**
 * Convert PHP tags to entities
 *
 * @access	public
 * @param	string
 * @return	string
 */
function encode_php_tags($str)
{
	return str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
}

?>