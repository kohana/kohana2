<?php defined('SYSPATH') or die('No direct script access.');

abstract class Image_Driver {

	public function calculate_offset($width, $height, $top, $left)
	{
		if (is_string($top))
		{
			switch($top)
			{
				case 'top':
					$top = 0;
				break;
				case 'bottom':
					/**
					 * @todo calculate the offset to v-align bottom
					 */
				break;
				case 'center':
					/**
					 * @todo calculate the offset to v-align center
					 */
				break;
			}
		}

		if (is_string($left))
		{
			switch($left)
			{
				case 'left':
					$left = 0;
				break;
				case 'right':
					/**
					 * @todo calculate the offset to align right
					 */
				break;
				case 'center':
					/**
					 * @todo calculate the offset to align center
					 */
				break;
			}
		}
	}

}