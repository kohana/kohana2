<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File: ltrim
 *  Kohana utf8 file, loaded by <utf8.php>. 
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

function _ltrim($str, $charlist = NULL)
{
	if ($charlist === NULL OR utf8::is_ascii($charlist))
	{
		return ($charlist === NULL) ? ltrim($str) : ltrim($str, $charlist);
	}

	$charlist = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $charlist);

	// Try to support .. character ranges. If they cause errors, drop support.
	$charlist_ranged = str_replace('\.\.', '-', $charlist);
	$str_ranged = @preg_replace('/^['.$charlist_ranged.']+/u', '', $str);

	return ($str_ranged !== NULL) ? $str_ranged : preg_replace('/^['.$charlist.']+/u', '', $str);
}