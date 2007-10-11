<?php defined('SYSPATH') or die('No direct access allowed.');

class Loader_Core {

	public function __construct()
	{
		foreach(Config::item('core.autoload') as $type => $load)
		{
			if ($load == FALSE) continue;

			foreach(explode(',', $load) as $name)
			{
				if (($name = trim($name)) == FALSE) continue;

				switch($type)
				{
					case 'libraries':
						if ($name == 'database')
						{
							$this->database();
						}
						else
						{
							$this->library($name);
						}
					break;
					case 'models':
						$this->model($name);
					break;
				}
			}
		}
	}

	public function library($name, $config = array())
	{
		if (isset(Kohana::instance()->$name))
			return FALSE;

		if ($name == 'database')
		{
			$this->database($config);
		}
		else
		{
			if (is_array(Config::item($name)))
			{
				$config = array_merge(Config::item($name), $config);
			}

			$class  = ucfirst($name);

			Kohana::instance()->$name = new $class($config);
		}
	}

	public function database($group = 'default', $return = FALSE)
	{
		// Return the new database object
		if ($return == TRUE)
		{
			return new Database($group);
		}
		else
		{
			// Set the database object to Controller->db
			Kohana::instance()->db = new Database($group);
		}
	}

	public function helper($name)
	{
		// Allow recursive loading
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

	public function model($name, $alias = FALSE)
	{
		// The alias is used for Controller->alias
		$alias = ($alias == FALSE) ? $name : $alias;
		$class = ucfirst($name).'_Model';

		if (isset(Kohana::instance()->$alias))
			return FALSE;

		// Load the model
		$model = new $class();

		if ($alias === TRUE)
		{
			return $model;
		}
		else
		{
			Kohana::instance()->$alias = $model;
		}
	}

	public function view($name, $data = array())
	{
		// Fancy! *wink*
		return new View($name, $data);
	}

} // End Loader Class