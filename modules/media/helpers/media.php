<?php defined('SYSPATH') or die('No direct script access.');
/**
* Class: media
*  Media helper class.
*
* Kohana Source Code:
*  author    - Kohana Team
*  copyright - (c) 2007 Kohana Team
*  license   - <http://kohanaphp.com/license.html>
*/
class media_Core {
	/**
	* Method: stylesheet
	*  Creates a stylesheet link.
	*
	* Parameters:
	*  style - filename, or array of filenames (do not include path)
	*  media - media type of stylesheet
	*  index  - include the index_page in the link
	*
	* Returns:
	*  An HTML stylesheet link.
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