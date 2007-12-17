<?php defined('SYSPATH') or die('No direct script access.');

abstract class Image_Driver {

	// Reference to the current image
	protected $image;

	// Reference to the temporary processing image
	protected $tmp_image;

	// Processing errors
	protected $errors;

	/**
	 * Executes a set of actions, defined in pairs.
	 *
	 * @param   array    actions
	 * @return  boolean
	 */
	public function execute($actions)
	{
		foreach($actions as $func => $args)
		{
			if ( ! $this->$func($args))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Process an image with a set of actions.
	 *
	 * @param   string   image filename
	 * @param   array    actions to execute
	 * @param   string   destination directory path
	 * @param   string   destination filename
	 * @return  boolean
	 */
	abstract public function process($image, $actions, $dir, $file);

	/**
	 * Flip an image. Valid directions are horizontal and vertical.
	 *
	 * @param   integer   direction to flip
	 * @return  boolean
	 */
	abstract function flip($direction);

	/**
	 * Crop an image. Valid properties are: width, height, top, left.
	 *
	 * @param   array     new properties
	 * @return  boolean
	 */
	abstract function crop($properties);

	/**
	 * Resize an image. Valid properties are: width, height, and master.
	 *
	 * @param   array     new properties
	 * @return  boolean
	 */
	abstract public function resize($properties);

	/**
	 * Rotate an image. Validate amounts are -180 to 180.
	 *
	 * @param   integer   amount to rotate
	 * @return  boolean
	 */
	abstract public function rotate($amount);

} // End Image Driver