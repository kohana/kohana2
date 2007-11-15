<?php defined('SYSPATH') or die('No direct script access.');

class Kodoc_Controller extends Controller {

	public function index()
	{
		$kodoc = new Kodoc();
		print "Data: ".Kohana::debug($kodoc->get_docs());
		print Kohana::lang('core.stats_footer');
	}

}