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
	protected $image_type = 'png'; // 'png', 'gif' or 'jpeg'

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
	 * @param   string  path to the background image file
	 * @return  void
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
	}

	/**
	 * Fills the background with a gradient.
	 *
	 * @param   resource  gd image color identifier for start color
	 * @param   resource  gd image color identifier for end color
	 * @param   string    direction: 'horizontal' or 'vertical', 'random' by default
	 * @return  void
	 */
	public function image_gradient($color1, $color2, $direction = NULL)
	{
		$directions = array('horizontal', 'vertical');

		// Pick a random direction if needed
		if ( ! in_array($direction, $directions))
		{
			$direction = $directions[array_rand($directions)];

			// Switch colors
			if (mt_rand(0, 1) === 1)
			{
				$temp = $color1;
				$color1 = $color2;
				$color2 = $temp;
			}
		}

		// Extract RGB values
		$color1 = imagecolorsforindex($this->image, $color1);
		$color2 = imagecolorsforindex($this->image, $color2);

		// Preparations for the gradient loop
		$steps = ($direction === 'horizontal') ? Captcha::$config['width'] : Captcha::$config['height'];

		$r1 = ($color1['red'] - $color2['red']) / $steps;
		$g1 = ($color1['green'] - $color2['green']) / $steps;
		$b1 = ($color1['blue'] - $color2['blue']) / $steps;

		if ($direction === 'horizontal')
		{
			$x1 =& $i;
			$y1 = 0;
			$x2 =& $i;
			$y2 = Captcha::$config['height'];
		}
		else
		{
			$x1 = 0;
			$y1 =& $i;
			$x2 = Captcha::$config['width'];
			$y2 =& $i;
		}

		// Execute the gradient loop
		for ($i = 0; $i <= $steps; $i++)
		{
			$r2 = $color1['red'] - floor($i * $r1);
			$g2 = $color1['green'] - floor($i * $g1);
			$b2 = $color1['blue'] - floor($i * $b1);
			$color = imagecolorallocate($this->image, $r2, $g2, $b2);

			imageline($this->image, $x1, $y1, $x2, $y2, $color);
		}
	}

	/**
	 * Outputs the image to the browser.
	 *
	 * @param   boolean  html output
	 * @return  mixed    html string or void
	 */
	public function image_render($html)
	{
		// Output html element
		if ($html)
			return '<img alt="Captcha" src="'.url::site('captcha').'" width="'.Captcha::$config['width'].'" height="'.Captcha::$config['height'].'" />';

		// Send the correct HTTP header
		header('Content-Type: image/'.$this->image_type);

		// Pick the correct output function
		$function = 'image'.$this->image_type;
		$function($this->image);

		// Free up resources
		imagedestroy($this->image);
	}

} // End Captcha Driver