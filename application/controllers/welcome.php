<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Default Kohana controller.
 */
class Welcome_Controller extends Controller {

	public function index()
	{
		$welcome = new View('welcome');
		$welcome->message = 'This is the default Kohana index page. You can edit <tt>application/controllers/welcome.php</tt> now.';
		$welcome->render(TRUE);
	}

}