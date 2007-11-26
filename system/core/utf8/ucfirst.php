<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: ucfirst
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _ucfirst($str)
{
	if (utf8::is_ascii($str))
		return ucfirst($str);

	preg_match('/^(.?)(.*)$/us', $str, $matches);
	return utf8::strtoupper($matches[1]).$matches[2];
}