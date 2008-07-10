<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Captcha driver class.
 *
 * $Id$
 *
 * @package    Captcha
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Captcha_Driver {

	protected $image;              // Image resource identifier
	protected $image_type = 'png'; // 'png', 'gif', or 'jpeg'

	/**
	 * Generate a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	abstract public function generate_challenge();

	/**
	 * Output the Captcha challenge.
	 *
	 * @param   boolean  html output
	 * @return  mixed    the rendered Captcha (e.g. an image, riddle, etc.)
	 */
	abstract public function render($html);

	/**
	 * Sets and returns the image type.
	 *
	 * @param   string  filename
	 * @return  string  image type
	 */
	public function image_type($filename)
	{
		switch (strtolower(file::extension($filename)))
		{
			case 'gif':
				$this->image_type = 'gif';
			break;
			case 'png':
				$this->image_type = 'png';
			break;
			case 'jpg':
			case 'jpeg':
				$this->image_type = 'jpeg';
			break;
		}

		return $this->image_type;
	}

	/**
	 * Wrapper for imagecreatefromXXX().
	 *
	 * @param   string    filename
	 * @return  resource  image identifier
	 */
	public function image_create_from($filename)
	{
		$function = 'imagecreatefrom'.$this->image_type($filename);
		return $function($filename);
	}

	/**
	 * Creates an image resource with the specified dimensions.
	 * If a background image is supplied, the image dimensions are used.
	 *
	 * @chainable
	 * @param   string  path to the background image file
	 * @return  object
	 */
	public function image_create($background = NULL)
	{
		// Use background image
		if ( ! empty($background))
		{
			$this->image = $this->image_create_from($background);

			// Overwrite the dimensions
			Captcha::$config['width']  = imagesx($this->image);
			Captcha::$config['height'] = imagesy($this->image);
		}
		// Default background (black)
		else
		{
			$this->image = imagecreatetruecolor(Captcha::$config['width'], Captcha::$config['height']);
		}

		return $this;
	}

	/**
	 * Fills the background with a gradient.
	 *
	 * @chainable
	 * @return  object
	 */
	public function image_gradient()
	{
		// TODO: build
		return $this;
	}

	/**
	 * Outputs the image to the browser.
	 *
	 * @return  void
	 */
	public function image_output()
	{
		// Send the correct HTTP header
		header('Content-Type: image/'.$this->image_type);

		// Pick the correct output function
		$function = 'image'.$this->image_type;
		$function($this->image);

		// Free up resources
		imagedestroy($this->image);
	}

	/**
	 * Generates html img element.
	 *
	 * @return  string
	 */
	public function image_html()
	{
		return '<img alt="Captcha" src="'.url::site('captcha').'" width="'.Captcha::$config['width'].'" height="'.Captcha::$config['height'].'" />';
	}

} // End Captcha Driver