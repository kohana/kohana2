<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Outputs the dynamic Captcha resource.
 * Usage: Call the Captcha controller from a view, e.g.
 *        <img src="<?php echo url::site('captcha') ?>" />
 *
 * $Id$
 *
 * @package    Captcha
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Controller extends Controller {

	public function index()
	{
		// Output the Captcha challenge resource (no html)
		$captcha = new Captcha;
		$captcha->render(FALSE);
	}

} // End Captcha_Controller