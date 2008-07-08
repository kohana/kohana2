<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Allows a CAPTCHA image to be displayed dynamically.
 * Captcha library is called to output the image.
 *
 *
 * Usage: Call the captcha controller from a view.
 *        `echo html::image(url::site().'captcha');`
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Controller extends Controller {

	public $session;
	public $captcha;

	protected $captcha_code;

	public function index()
	{
		$this->session = Session::instance();
		$this->captcha = new Captcha;

		// Create and store a random captcha string
		$this->captcha_code = $this->create_code();
		$this->captcha->set_code($this->captcha_code);
		$this->session->set('captcha_code', $this->captcha_code);

		// Output the image
		$this->captcha->render();
	}

	private function create_code()
	{
		if (Config::item('captcha.style') === 'math')
		{
			$code = (string) mt_rand(101, 991);
		}
		else
		{
			$code = text::random('distinct', Config::item('captcha.num_chars'));
		}

		return $code;
	}

} // End Captcha_Controller