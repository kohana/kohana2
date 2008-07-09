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

	// Image resource
	public $image;

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
	 * TODO: some generic Captcha image generating helper methods
	 *       to prevent duplicate code in drivers.
	 */
	public function image_create() {}
	public function image_gradient() {}

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