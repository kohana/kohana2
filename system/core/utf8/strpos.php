<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: strpos
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _strpos($str, $search, $offset = 0)
{
	$offset = (int) $offset;

	if (SERVER_UTF8)
		return mb_strpos($str, $search, $offset);

	if (utf8::is_ascii($str) AND utf8::is_ascii($search))
		return strpos($str, $search, $offset);

	if ($offset == 0)
	{
		$array = explode($search, $str, 2);
		return (isset($array[1])) ? utf8::strlen($array[0]) : FALSE;
	}

	$str = utf8::substr($str, $offset);
	$pos = utf8::strpos($str, $search);
	return ($pos === FALSE) ? FALSE : $pos + $offset;
}