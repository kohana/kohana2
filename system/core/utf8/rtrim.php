<?php defined('SYSPATH') or die('No direct script access.');
/**
 * utf8::rtrim
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _rtrim($str, $charlist = NULL)
{
	if ($charlist === NULL OR utf8::is_ascii($charlist))
	{
		return ($charlist === NULL) ? rtrim($str) : rtrim($str, $charlist);
	}

	$charlist = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $charlist);

	// Try to support .. character ranges. If they cause errors, drop support.
	$charlist_ranged = str_replace('\.\.', '-', $charlist);
	$str_ranged = @preg_replace('/['.$charlist_ranged.']+$/u', '', $str);

	return ($str_ranged !== NULL) ? $str_ranged : preg_replace('/['.$charlist.']+$/u', '', $str);
}