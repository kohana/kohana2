<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Captcha driver for "basic" style.
 *
 * $Id$
 *
 * @package    Captcha
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Basic_Driver extends Captcha_Driver {

	/**
	 * Generates a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	public function generate_challenge()
	{
		// Complexity setting is used as character count
		return text::random('distinct', max(1, Captcha::$config['complexity']));
	}

	/**
	 * Outputs the Captcha image.
	 *
	 * @param   boolean  html output
	 * @return  mixed
	 */
	public function render($html)
	{
		// Creates $this->image
		$this->image_create(Captcha::$config['background']);

		// TODO: everything, font-size, spacing, colors, background, etc.
		$color = imagecolorexact($this->image, 255, 255, 255);
		imagefttext($this->image, 20, 5, 10, 40, $color, Captcha::$config['font'], Captcha::$answer);

		// Output
		return ($html) ? $this->image_html() : $this->image_output();
	}

} // End Captcha Basic Driver Class