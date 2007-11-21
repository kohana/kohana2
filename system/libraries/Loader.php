<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Loader
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Loader_Core {

	/**
	 * Constructor: __construct
	 *  Autoloads libraries and models specified in config file.
	 */
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

	/**
	 * Method: library
	 *  Load library.
	 *
	 * Parameters:
	 *  name   - library name
	 *  config - custom configuration
	 *  return - return library instance instead of adding to Kohana instance
	 *
	 * Returns:
	 *  FALSE  - library is already loaded and 'return' parameter is FALSE
	 *  Object - instance of library if 'return' parameter is TRUE
	 */
	public function library($name, $config = array(), $return = FALSE)
	{
		if (isset(Kohana::instance()->$name) AND $return == FALSE)
			return FALSE;

		if ($name == 'database')
		{
			return $this->database($config, $return);
		}
		else
		{
			$class = ucfirst($name);
			$class = new $class($config);

			if ($return == TRUE)
				return $class;

			Kohana::instance()->$name = $class;
		}
	}

	/**
	 * Method: database
	 *  Load database.
	 *
	 * Parameters:
	 *  group  - Database config group to use
	 *  return - return database instance instead of adding to Kohana instance
	 *
	 * Returns:
	 *  Database instance if 'return' parameter is TRUE
	 */
	public function database($group = 'default', $return = FALSE)
	{
		$db = new Database($group);

		// Return the new database object
		if ($return == TRUE)
			return $db;

		Kohana::instance()->db = $db;
	}

	/**
	 * Method: helper
	 *  Load helper.
	 *
	 * Parameters:
	 *  name - helper name
	 */
	public function helper($name)
	{
		// Just don't do this... there's no point.
		Log::add('debug', 'Using $this->load->helper() is deprecated. See Kohana::auto_load().');
	}

	/**
	 * Method: model
	 *  Load model.
	 *
	 * Parameters:
	 *  name  - model name
	 *  alias - custom name for accessing model, or TRUE to return instance of model
	 *
	 * Returns:
	 *  FALSE  - model is already loaded
	 *  Object - instance of model if 'alias' parameter is TRUE
	 */
	public function model($name, $alias = FALSE)
	{
		// The alias is used for Controller->alias
		$alias = ($alias == FALSE) ? $name : $alias;
		$class = ucfirst($name).'_Model';

		if (isset(Kohana::instance()->$alias))
			return FALSE;

		if (strpos($name, '/') !== FALSE)
		{
			// Handle models in subdirectories
			require_once Kohana::find_file('models', $name);

			// Reset the class name
			$class = end(explode('/', $class));
		}

		// Load the model
		$model = new $class();

		// Return the model
		if ($alias === TRUE)
			return $model;

		Kohana::instance()->$alias = $model;
	}

	/**
	 * Method: view
	 *  Load view.
	 *
	 * Parameters:
	 *  name - view name
	 *  data - data to make accessible within view
	 *
	 * Returns:
	 *  Instance of specified view
	 */
	public function view($name, $data = array())
	{
		return new View($name, $data);
	}

} // End Loader Class