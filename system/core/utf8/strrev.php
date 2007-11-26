<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: strrev
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _strrev($str)
{
	if (utf8::is_ascii($str))
	{
		return strrev($str);
	}

	preg_match_all('/./us', $str, $matches);
	return implode('', array_reverse($matches[0]));
}