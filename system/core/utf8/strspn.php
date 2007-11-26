<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: strspn
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _strspn($str, $mask, $offset = NULL, $length = NULL)
{
	if ($str == '' OR $mask == '')
		return 0;

	if (utf8::is_ascii($str) AND utf8::is_ascii($mask))
		return ($offset === NULL) ? strspn($str, $mask) : (($length === NULL) ? strspn($str, $mask, $offset) : strspn($str, $mask, $offset, $length));

	if ($offset !== NULL OR $length !== NULL)
	{
		$str = utf8::substr($str, $offset, $length);
	}

	// Escape these characters:  - [ ] . : \ ^ /
	// The . and : are escaped to prevent possible warnings about POSIX regex elements
	$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
	preg_match('/^[^'.$mask.']+/u', $str, $matches);

	return (isset($matches[0])) ? utf8::strlen($matches[0]) : 0;
}