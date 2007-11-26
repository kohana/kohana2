<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: trim
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _trim($str, $charlist = NULL)
{
	if ($charlist === NULL OR utf8::is_ascii($charlist))
	{
		return ($charlist === NULL) ? trim($str) : trim($str, $charlist);
	}

	return utf8::ltrim(utf8::rtrim($str, $charlist), $charlist);
}