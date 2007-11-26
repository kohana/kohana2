<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: str_split
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _str_split($str, $split_length = 1)
{
	$split_length = (int) $split_length;

	if (utf8::is_ascii($str))
	{
		return str_split($str, $split_length);
	}

	if ($split_length < 1)
	{
		return FALSE;
	}

	if (utf8::strlen($str) <= $split_length)
	{
		return array($str);
	}

	preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);

	return $matches[0];
}