<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: strrpos
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _strrpos($str, $search, $offset = 0)
{
	$offset = (int) $offset;

	if (SERVER_UTF8)
		return mb_strrpos($str, $search, $offset);

	if (utf8::is_ascii($str) AND utf8::is_ascii($search))
		return strrpos($str, $search, $offset);

	if ($offset == 0)
	{
		$array = explode($search, $str, -1);
		return (isset($array[0])) ? utf8::strlen(implode($search, $array)) : FALSE;
	}

	$str = utf8::substr($str, $offset);
	$pos = utf8::strrpos($str, $search);
	return ($pos === FALSE) ? FALSE : $pos + $offset;
}