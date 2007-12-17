<?php defined('SYSPATH') or die('No direct script access.');

class Image_ImageMagick_Driver extends Image_Driver {

	protected $dir = '';
	protected $tmp = '';

	protected $output = array();

	public function __construct($config)
	{
		if (empty($config['directory']))
		{
			$path = trim(exec('which convert'));

			if ( ! is_file($path))
				throw new Kohana_Exception('image.imagemagick.not_found');

			$config['directory'] = dirname($path);
		}

		// Set the installation directory
		$this->dir = str_replace('\\', '/', realpath($config['directory'])).'/';
	}

	public function process($image, $actions, $new_file)
	{
		$this->tmp_image =
			// Temporary directory is the same directory as the new file
			substr($new_file, 0, strrpos($new_file, '/') + 1).
			// Temporary filename is a hash of the new filename
			'k2img--'.sha1($new_file).substr($image, strrpos($image, '.'));

		// Copy the image to the temporary location
		copy($image, $this->tmp_image);

		// Do this during the final processing
		$quality = (int) arr::remove('quality', $actions);

		// Use 95 for the default quality
		empty($quality) and $quality = 95;

		if ($status = parent::process($image, $actions, $new_file))
		{
			// Set the new file to the original file name
			empty($new_file) and $new_file = $image;

			// Delete the existing file
			is_file($new_file) and unlink($new_file);

			// Use convert to change the image into it's final version. This is
			// done to allow the file type to change correctly, and to handle
			// the quality conversion in the most effective way possible.
			$this->output[__FUNCTION__] = exec(escapeshellcmd($this->dir.'convert').' -quality '.$quality.' '.escapeshellarg($this->tmp_image).' '.escapeshellarg($new_file));
		}

		// Remove the temporary image
		unlink($this->tmp_image);

		return $status;
	}

	public function resize($properties)
	{
		switch($properties['master'])
		{
			case Image::WIDTH:
				$dim = escapeshellarg('x'.$properties['width']);
			break;
			case Image::HEIGHT:
				$dim = escapeshellarg($properties['height'].'x');
			break;
			case Image::AUTO:
				$dim = escapeshellarg($properties['width'].'x'.$properties['height']);
			break;
			case Image::NONE:
				$dim = escapeshellarg($properties['width'].'x'.$properties['height'].'!');
			break;
		}

		// File is the tmp image
		$file = escapeshellarg($this->tmp_image);

		// Use "convert" to change the width and height
		$this->output[__FUNCTION__] = exec(escapeshellcmd($this->dir.'convert').' -resize '.$dim.' '.$file.' '.$file);

		return TRUE;
	}

}