<?php defined('SYSPATH') or die('No direct script access.');

class Image_GD_Driver extends Image_Driver {

	// GD image create function name
	protected $imagecreate;

	// GD image save function name
	protected $imagesave;

	// A transparent PNG as a string
	protected static $blank_png;
	protected static $blank_png_width;
	protected static $blank_png_height;

	public function __construct()
	{
		// Make sure that GD2 is available
		if ( ! function_exists('imageconvolution'))
			throw new Kohana_Exception('image.gd.requires_v2');
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
		// Get the current width and height
		list($width, $height) = $this->properties();

		if (substr($properties['width'], -1) === '%')
		{
			// Recalculate the percentage to a pixel size
			$properties['width'] = round($width * (substr($properties['width'], 0, -1) / 100));
		}

		if (substr($properties['height'], -1) === '%')
		{
			// Recalculate the percentage to a pixel size
			$properties['height'] = round($height * (substr($properties['height'], 0, -1) / 100));
		}

		if (empty($properties['width']))
		{
			/**
			 * @todo Determine the width difference and calculate, don't forget $properties['master']!
			 */
		}

		if (empty($properties['height']))
		{
			/**
			 * @todo Determine the height difference and calculate, don't forget $properties['master']!
			 */
		}

		// Create the temporary image to copy to
		$img = $this->imagecreatetransparent($properties['width'], $properties['height']);

		// Execute the resize
		if ($status = imagecopyresampled($img, $this->tmp_image, 0, 0, 0, 0, $properties['width'], $properties['height'], $width, $height))
		{
			// Swap the new image for the old one
			imagedestroy($this->tmp_image);
			$this->tmp_image = $img;
		}

		return $status;
	}

	public function rotate($amount)
	{
		// Use current image to rotate
		$img = $this->tmp_image;

		// White, with an alpha of 0
		$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);

		// Rotate, setting the transparent color
		$img = imagerotate($img, 360 - $amount, $transparent, -1);

		// Fill the background with the transparent "color"
		imagecolortransparent($img, $transparent);

		// Merge the images
		if ($status = imagecopymerge($this->tmp_image, $img, 0, 0, 0, 0, imagesx($this->tmp_image), imagesy($this->tmp_image), 100))
		{
			// Prevent the alpha from being lost
			imagealphablending($img, TRUE);
			imagesavealpha($img, TRUE);

			// Swap the new image for the old one
			imagedestroy($this->tmp_image);
			$this->tmp_image = $img;
		}

		return $status;
	}

	public function sharpen($amount)
	{
		// Amount should be in the range of 18-10
		$amount = round(abs(-18 + ($amount * 0.08)), 2);

		// Gaussian blur matrix
		$matrix = array
		(
			array(-1, -1, -1),
			array(-1, $amount, -1),
			array(-1, -1, -1)
		);

		// Perform the sharpen
		return imageconvolution($this->tmp_image, $matrix, $amount - 8, 0);
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
		if (self::$blank_png === NULL)
		{
			// Decode the blank PNG if it has not been done already
			self::$blank_png = imagecreatefromstring(base64_decode
			(
				'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29'.
				'mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADqSURBVHjaYvz//z/DYAYAAcTEMMgBQAANegcCBN'.
				'CgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQ'.
				'AANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoH'.
				'AgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB'.
				'3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAgAEAMpcDTTQWJVEAAAAASUVORK5CYII='
			));

			self::$blank_png_width = 40;
			self::$blank_png_height = 40;
		}

		$img = imagecreatetruecolor($width, $height);

		// Resize the blank image
		imagecopyresized($img, self::$blank_png, 0, 0, 0, 0, $width, $height, self::$blank_png_width, self::$blank_png_height);

		// Prevent the alpha from being lost
		imagealphablending($img, FALSE);
		imagesavealpha($img, TRUE);

		return $img;
	}

} // End Image GD Driver