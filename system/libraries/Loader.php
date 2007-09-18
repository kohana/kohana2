<?php defined('SYSPATH') or die('No direct access allowed.');

class Loader_Core {

	function __construct()
	{
		foreach(Config::item('core.autoload') as $type => $load)
		{
			if ($load == FALSE) continue;

			foreach(explode(',', $load) as $name)
			{
				if (($name = trim($name)) == FALSE) continue;

				switch($type)
				{
					case 'libraries': $this->library($name); break;
					case 'models':    $this->model($name);   break;
				}
			}
		}
	}

	function library($name)
	{
		Kohana::instance()->$name = Kohana::load_class(ucfirst($name));
	}

	function helper($name)
	{
		if (is_array($name))
		{
			$helpers = $name;

			foreach($helpers as $name)
			{
				$this->helper($name);
			}
		}
		else
		{
			include Kohana::find_file('helpers', $name, TRUE);
		}
	}

	function model($name)
	{
		Kohana::instance()->$name = Kohana::load_class(ucfirst($name).'_Model');
	}

	// Weird prefixes to prevent collisions
	function view($name, $data = array())
	{
		return new View($name, $data);
	}

} // End Loader Class