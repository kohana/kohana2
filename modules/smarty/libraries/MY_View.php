<?php

class View extends View_Core {

	public function __construct($name, $data = NULL, $type = NULL)
	{
		$ext = empty($type) ? Kohana_Config::get('smarty.templates_ext') : $type;

		if (Kohana_Config::get('smarty.integration') == TRUE AND Kohana::find_file('views', $name, FALSE, $ext))
		{
			$type = $ext;
		}

		parent::__construct($name, $data, $type);
	}
}
