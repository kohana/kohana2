<?php defined('SYSPATH') or die('No direct script access.');

class Image_ImageMagick_Driver extends Image_Driver {

	// Directory that IM is installed in
	protected $dir = '';

	// Temporary image filename
	protected $tmp_image;

	// Processing errors
	protected $errors = array();

	/**
	 * Attempts to detect the ImageMagick installation directory.
	 *
	 * @throws  Kohana_Exception
	 * @param   array   configuration
	 * @return  void
	 */
	public function __construct($config)
	{
		if (empty($config['directory']))
		{
			// Attempt to locate IM by using "which"
			if ( ! is_file($path = exec('which convert')))
				throw new Kohana_Exception('image.imagemagick.not_found');

			$config['directory'] = dirname($path);
		}

		// Check to make sure the provided path is correct
		if ( ! file_exists(realpath($config['directory']).'/convert'))
			throw new Kohana_Exception('image.imagemagick.not_found');

		// Set the installation directory
		$this->dir = str_replace('\\', '/', realpath($config['directory'])).'/';
	}

	/**
	 * Creates a temporary image and executes the given actions. By creating a
	 * temporary copy of the image before manipulating it, this process is atomic.
	 */
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

	public function crop($prop)
	{
		// Sanitize and normalize the properties into geometry
		$this->sanitize_geometry($prop);

		// Set the IM geometry based on the properties
		$geometry = escapeshellarg($prop['width'].'x'.$prop['height'].'+'.$prop['left'].'+'.$prop['top']);

		if ($error = exec(escapeshellcmd($this->dir.'convert').' -crop '.$geometry.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	public function flip($dir)
	{
		// Convert the direction into a IM command
		$dir = ($dir === Image::HORIZONTAL) ? '-flop' : '-flip';

		if ($error = exec(escapeshellcmd($this->dir.'convert').' '.$dir.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	public function resize($prop)
	{
		switch($prop['master'])
		{
			case Image::WIDTH:  // Wx
				$dim = escapeshellarg($prop['width'].'x');
			break;
			case Image::HEIGHT: // xH
				$dim = escapeshellarg('x'.$prop['height']);
			break;
			case Image::AUTO:   // WxH
				$dim = escapeshellarg($prop['width'].'x'.$prop['height']);
			break;
			case Image::NONE:   // WxH!
				$dim = escapeshellarg($prop['width'].'x'.$prop['height'].'!');
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

	public function rotate($amt)
	{
		if ($error = exec(escapeshellcmd($this->dir.'convert').' -rotate '.escapeshellarg($amt).' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	public function sharpen($amount)
	{
		// Set the sigma, radius, and amount. The amount formula allows a nice
		// spread between 1 and 100 without pixelizing the image badly.
		$sigma  = 0.5;
		$radius = $sigma * 2;
		$amount = round(($amount / 80) * 3.14, 2);

		// Convert the amount to an IM command
		$sharpen = escapeshellarg($radius.'x'.$sigma.'+'.$amount.'+0');

		if ($error = exec(escapeshellcmd($this->dir.'convert').' -unsharp '.$sharpen.' '.$this->cmd_image.' '.$this->cmd_image))
		{
			$this->errors[] = $error;
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Return the current width and height of the temporary image. This is mainly
	 * needed for sanitizing the geometry.
	 *
	 * @return  array  width, height
	 */
	protected function properties()
	{
		// Return the width and height as an array. Use with list()
		return explode(',', exec(escapeshellcmd($this->dir.'identify').' -format '.escapeshellarg('%w,%h').' '.$this->cmd_image));
	}

	/**
	 * Sanitize and normalize a geometry array based on the temporary image
	 * width and height. Valid properties are: width, height, top, left.
	 *
	 * @param   array  geometry properties
	 * @return  void
	 */
	protected function sanitize_geometry( & $geometry)
	{
		list($width, $height) = $this->properties();

		// Turn off error reporting
		$reporting = error_reporting(0);

		// Width and height cannot exceed current image size
		$geometry['width']  = min($geometry['width'], $width);
		$geometry['height'] = min($geometry['height'], $height);

		switch($geometry['top'])
		{
			case 'center':
				$geometry['top'] = floor(($height / 2) - ($geometry['height'] / 2));
			break;
			case 'top':
				$geometry['top'] = 0;
			break;
			case 'bottom':
				$geometry['top'] = $height - $geometry['height'];
			break;
		}

		switch($geometry['left'])
		{
			case 'center':
				$geometry['left'] = floor(($width / 2) - ($geometry['width'] / 2));
			break;
			case 'left':
				$geometry['left'] = 0;
			break;
			case 'right':
				$geometry['left'] = $width - $geometry['height'];
			break;
		}

		// Restore error reporting
		error_reporting($reporting);
	}

}