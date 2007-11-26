<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: strlen
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _strlen($str)
{
	// Try mb_strlen() first because it's faster than combination of is_ascii() and strlen()
	if (SERVER_UTF8)
		return mb_strlen($str);

	if (utf8::is_ascii($str))
		return strlen($str);

	return strlen(utf8_decode($str));
}