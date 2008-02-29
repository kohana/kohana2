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
	public $captcha;
	public $session;

	protected $captcha_code;

	public function index()
	{
		$this->session = Session::instance();

		$this->captcha = new Captcha;

		// Create a random text string for captcha code.
		$this->captcha_code = $this->create_code();
		$this->captcha->set_code($this->captcha_code) ;

		// Set the session to store the security code
		$this->session->set('captcha_code', $this->captcha_code);
		// Call the library to output the image
		$this->captcha->render();
	}

	private function create_code()
	{
		$num_chars = Config::item('captcha.num_chars');

		if (Config::item('captcha.style') == 'math')
		{
			$code = (string) mt_rand(101, 991);
		}
		else
		{
			// Character set to use, similar characters removed.
			$charset = '@2345#6BCDF$GH789KMNPQRT%VWXYZ';
			$code = '';
			for ($i = 0; $i < $num_chars; $i++)
			{
			$code .= substr($charset, mt_rand(0, strlen($charset)-1), 1);
			}
		}

		return $code;
	}

}