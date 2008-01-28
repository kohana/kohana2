<?php defined('SYSPATH') or die('No direct script access.');

class Image_GD_Driver extends Image_Driver {


	// GD image create function name
	protected $imagecreate;

	// GD image save function name
	protected $imagesave;

	// A transparent PNG as a string
	protected static $blank_png;

	public function __construct()
	{
		// Make sure that GD2 is available
		if ( ! function_exists('imageconvolution'))
			throw new Kohana_Exception('image.gd.requires_v2');

		// Decode the blank PNG if it has not been done already
		(self::$blank_png === NULL) and self::$blank_png = base64_decode
		(
			'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29'.
			'mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADqSURBVHjaYvz//z/DYAYAAcTEMMgBQAANegcCBN'.
			'CgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQ'.
			'AANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoH'.
			'AgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB'.
			'3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAgAEAMpcDTTQWJVEAAAAASUVORK5CYII='
		);
	}

	protected function set_functions($type)
	{
		static $imagecreate;
		static $imagesave;

		if ($imagecreate === NULL)
		{
			$imagecreate = array
			(
				1 => 'imagecreatefromgif',
				2 => 'imagecreatefromjpeg',
				3 => 'imagecreatefrompng'
			);

			$imagesave = array
			(
				1 => 'imagegif',
				2 => 'imagejpeg',
				3 => 'imagepng'
			);
		}

		// Set the create function
		isset($imagecreate[$type])
			and function_exists($imagecreate[$type])
			and $this->imagecreate = $imagecreate[$type];

		// Set the save function
		isset($imagesave[$type])
			and function_exists($imagesave[$type])
			and $this->imagesave = $imagesave[$type];

		return ! (empty($this->imagecreate) OR empty($this->imagesave));
	}

	public function process($image, $actions, $dir, $file)
	{
		// Make sure the image type is supported
		if ( ! $this->set_functions($image['type']))
			throw new Kohana_Exception('image.type_not_allowed', $image['file']);

		// Load the image
		$this->image = $image;

		// Image create function alias
		$create = $this->imagecreate;

		// Create the GD image resource
		$this->tmp_image = $create($image['file']);

		if ($status = $this->execute($actions))
		{
			// Image save function alias
			$save = $this->imagesave;

			// Save the image to set the status
			$status = $save($this->tmp_image, $dir.$file);

			// Destroy the temporary image
			imagedestroy($this->tmp_image);
		}

		return $status;
	}

	public function flip($direction)
	{
		echo Kohana::debug($direction);
	}

	public function crop($properties)
	{
		// Sanitize the cropping settings
		$this->sanitize_geometry($properties);

		// Get the current width and height
		list($width, $height) = $this->properties();

		// Create the temporary image to copy to
		$tmp = $this->imagecreatetransparent($properties['width'], $properties['height']);

		// Execute the crop
		imagecopyresampled($tmp, $this->tmp_image, 0, 0, $properties['left'], $properties['top'], $width, $height, $width, $height);

		// Destroy the temporary image
		imagedestroy($this->tmp_image);

		// Set the temporary image to this image
		$this->tmp_image = $tmp;

		return TRUE;
	}

	public function resize($properties)
	{
		// Sanitize the resize settings
		$this->sanitize_geometry($properties);

		// Get the current width and height
		list($width, $height) = $this->properties();

		// Create the temporary image to copy to
		$img = $this->imagecreatetransparent($properties['width'], $properties['height']);

		// Execute the resize
		imagecopyresampled($img, $this->tmp_image, 0, 0, 0, 0, $properties['width'], $properties['height'], $width, $height);

		// Destroy the temporary image
		imagedestroy($this->tmp_image);

		// Set the temporary image to this image
		$this->tmp_image = $img;

		return TRUE;
	}

	public function rotate($amount)
	{
		// Prevent the alpha from being lost
		imagealphablending($this->tmp_image, TRUE);
		imagesavealpha($this->tmp_image, TRUE);

		// Rotate, using -1 as the color to preserve transparency
		$img = imagerotate($this->tmp_image, $amount, -1);

		// Destory the current image
		imagedestroy($this->tmp_image);

		// Swap the new image for the old one
		$this->tmp_image = $img;

		return TRUE;
	}

	public function sharpen($amount)
	{
		throw new Kohana_Exception('image.unsupported_driver_method', 'sharpen');
	}

	protected function properties()
	{
		return array(imagesx($this->tmp_image), imagesy($this->tmp_image));
	}

	/**
	 * Returns an image with a transparent background. Used for rotating to
	 * prevent unfilled backgrounds.
	 *
	 * @param   integer  image width
	 * @param   integer  image height
	 * @return  GD resource
	 */
	protected function imagecreatetransparent($width, $height)
	{
		$img = imagecreatetruecolor($width, $height);
		$tmp = imagecreatefromstring(self::$blank_png);

		// Prevent the alpha from being lost
		imagealphablending($img, FALSE);
		imagesavealpha($img, TRUE);

		// Resize the blank image
		imagecopyresized($img, $tmp, 0, 0, 0, 0, $width, $height, imagesx($tmp), imagesy($tmp));

		return $img;
	}

} // End Image GD Driver