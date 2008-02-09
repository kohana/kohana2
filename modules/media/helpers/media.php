<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Media helper class.
 *
 * $Id$
 *
 * @package	   Media Module
 * @author	   Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license	   http://kohanaphp.com/license.html
 */
class media_Core {

	/**
	 * Creates a stylesheet link.
	 *
	 * @param   string|array  filename, or array of filenames (do not include path)
	 * @param   string        media type of stylesheet
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function stylesheet($style, $media = FALSE, $index = TRUE)
	{
		if (is_array($style)) {
			$style = implode('+', $style);
		}
		$style = 'media/css/'.$style;
		return html::stylesheet($style, $media, $index);
	}
}