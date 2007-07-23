<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Controller {

	public function __construct()
	{
		Core::load_file('library', 'Loader') OR Core::show_error
		(
			'core', 'library_not_found', 'Loader'
		);
	}

}

?>