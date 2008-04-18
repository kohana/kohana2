<?php defined('SYSPATH') or die('No direct script access.');

class View extends View_Core {

	public function __construct($name, $data = NULL, $type = NULL)
	{
		$type = NULL;

		if (Config::item('smarty.integration') == TRUE AND Kohana::find_file('views', $name, FALSE, $type))
		{
			$type = empty($type) ? Config::item('smarty.templates_ext') : $type;
		}
	
		parent::__construct($name, $data, $type);
	}
}
