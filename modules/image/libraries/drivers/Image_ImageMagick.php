<?php defined('SYSPATH') or die('No direct script access.');

class Image_ImageMagick_Driver extends Image_Driver {

	// Directory that IM is installed in
	protected $dir = '';

	// Temporary image filename
	protected $tmp_image;

	// Processing errors
	protected $errors = array();

	public function __construct($config)
	{
		if (empty($config['directory']))
		{
			// Attempt to locate IM by using "which"
			if ( ! is_file($path = exec('which convert')))
				throw new Kohana_Exception('image.imagemagick.not_found');

			$config['directory'] = dirname($path);
		}

		// Set the installation directory
		$this->dir = str_replace('\\', '/', realpath($config['directory'])).'/';
	}

	public function process($image, $actions, $dir, $file)
	{
		// Unique temporary filename
		$this->tmp_image = $dir.'k2img--'.sha1($dir.$file).substr($file, strrpos($file, '.'));

		// Copy the image to the temporary file
		copy($image, $this->tmp_image);

		// Quality change is done last
		$quality = (int) arr::remove('quality', $actions);

		// Use 95 for the default quality
		empty($quality) and $quality = 95;

		// All calls to these will need to be escaped, so do it now
		$this->cmd_image = escapeshellarg($this->tmp_image);
		$this->new_image = escapeshellarg($dir.$file);

		if ($status = $this->execute($actions))
		{
			// Use convert to change the image into it's final version. This is
			// done to allow the file type to change correctly, and to handle
			// the quality conversion in the most effective way possible.
			if ($error = exec(escapeshellcmd($this->dir.'convert').' -quality '.$quality.'% '.$this->cmd_image.' '.$this->new_image))
			{
				$this->errors[] = $error;
			}
		}

		// Remove the temporary image
		unlink($this->tmp_image);

		return $status;
	}

	public function resize($properties)
	{
		switch($properties['master'])
		{
			case Image::WIDTH: // Wx
				$dim = escapeshellarg($properties['width'].'x');
			break;
			case Image::HEIGHT: // xH
				$dim = escapeshellarg('x'.$properties['height']);
			break;
			case Image::AUTO: // WxH
				$dim = escapeshellarg($properties['width'].'x'.$properties['height']);
			break;
			case Image::NONE: // WxH!
				$dim = escapeshellarg($properties['width'].'x'.$properties['height'].'!');
			break;
		}

		// Use "convert" to change the width and height
		if ($error = exec(escapeshellcmd($this->dir.'convert').' -resize '.$dim.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	public function crop($prop)
	{
		list($width, $height) = $this->properties();

		// Width and height cannot exceed current image size
		$prop['width']  = min($prop['width'], $width);
		$prop['height'] = min($prop['height'], $height);

		switch($prop['top'])
		{
			case 'center':
				$prop['top'] = floor(($height / 2) - ($prop['height'] / 2));
			break;
			case 'top':
				$prop['top'] = 0;
			break;
			case 'bottom':
				$prop['top'] = $height - $prop['height'];
			break;
		}
		switch($prop['left'])
		{
			case 'center':
				$prop['left'] = floor(($width / 2) - ($prop['width'] / 2));
			break;
			case 'left':
				$prop['left'] = 0;
			break;
			case 'right':
				$prop['left'] = $width - $prop['height'];
			break;
		}

		// Set the IM geometry based on the properties
		$geometry = escapeshellarg($prop['width'].'x'.$prop['height'].'+'.$prop['left'].'+'.$prop['top']);


		if ($error = exec(escapeshellcmd($this->dir.'convert').' -crop '.$geometry.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	public function flip($direction)
	{
		// Convert the direction into a IM command
		$direction = ($direction === Image::HORIZONTAL) ? '-flop' : '-flip';

		if ($error = exec(escapeshellcmd($this->dir.'convert').' '.$direction.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	protected function properties()
	{
		// Return the width and height as an array. Use with list()
		return explode(' ', exec(escapeshellcmd($this->dir.'identify').' -format '.escapeshellarg('%w %h').' '.$this->cmd_image));
	}

}