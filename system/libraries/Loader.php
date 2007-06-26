<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Loader
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/libraries/loader.html
 */

if (KOHANA_IS_PHP5)
{
	/**
	 * Autoloader
	 *
	 * Implements auto-loading of libraries in PHP >= 5, using Core_Loader
	 *
	 * @access	public
	 * @param	string	class name
	 */
	function __autoload($class)
	{
		if ( ! function_exists('get_instance'))
			return;

		static $CORE;
		if (is_null($CORE))
		{
			$CORE =& get_instance();
		}

		if (isset($CORE->load))
		{
			$fp = $CORE->load->_find_class($class);

			if ($fp !== FALSE AND $fp !== TRUE)
			{
				require_once($fp);
			}
		}
	}
}

class Core_Loader {

	// All these are set automatically. Don't mess with them.
	var $_ob_level;
	var $_view_path   = '';
	var $_is_instance = FALSE; // Whether we should use $this or $CORE =& get_instance()
	var $_cached_vars = array();
	var $_paths       = array(APPPATH, BASEPATH);
	var $_classes     = array();
	var $_models      = array();
	var $_helpers     = array();
	var $_plugins     = array();
	var $_scripts     = array();
	var $_varmap      = array('unit_test' => 'unit', 'user_agent' => 'agent');

	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 *
	 * @access	public
	 */
	function Core_Loader()
	{
		$this->_view_path = APPPATH.'views/';
		$this->_ob_level  = ob_get_level();

		log_message('debug', 'Loader Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @return	void
	 */
	function library($library = '', $params = NULL)
	{
		if ($library == '')
			return FALSE;

		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->_load_class($class, $params);
			}
		}
		else
		{
			$this->_load_class($library, $params);
		}

		$this->_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	mixed	any initialization parameters
	 * @return	void
	 */
	function model($model, $name = '', $db_conn = FALSE)
	{
		if ($model == '')
			return;

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (strpos($model, '/') === FALSE)
		{
			$path = '';
		}
		else
		{
			$x = explode('/', $model);
			$model = end($x);
			unset($x[count($x)-1]);
			$path = implode('/', $x).'/';
		}

		if ($name == '')
		{
			$name = $model;
		}

		if (in_array($name, $this->_models, TRUE))
		{
			return;
		}

		$CORE =& get_instance();
		if (isset($CORE->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		$model = strtolower($model);

		if ( ! file_exists(APPPATH.'models/'.$path.$model.EXT))
		{
			show_error('Unable to locate the model you have specified: '.$model);
		}

		if ($db_conn !== FALSE AND ! class_exists('Core_DB'))
		{
			if ($db_conn === TRUE)
				$db_conn = '';

			$CORE->load->database($db_conn, FALSE, TRUE);
		}

		load_class('Model', FALSE);
		require_once(APPPATH.'models/'.$path.$model.EXT);

		$model = ucfirst($model);

		$CORE->$name = new $model();
		$CORE->$name->_assign_libraries();

		$this->_models[] = $name;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Loader
	 *
	 * @access	  public
	 * @param	 string	   the DB credentials
	 * @param	 bool	 whether to return the DB object
	 * @return	  object
	 */
	function database($params = '', $return = FALSE)
	{
		//Do we need to load up the DB library?
		if ( ! class_exists('Core_DB'))
		{
			require_once(BASEPATH.'database/DB'.EXT);
		}

		// Grab the super object
		$CORE =& get_instance();

		// If the database interaction object is already set and we are not
		// returning our database object on this call, we need to stop
		if ($return === TRUE OR ($return == FALSE AND isset($CORE->db)))
		{
			if ($return == TRUE)
			{
				$return =& Core_DB($params);
			}

			return $return;
		}

		// Initialize the db variable. Needed to prevent reference errors
		// with some configurations
		$CORE->db = '';

		// Load the DB class
		$CORE->db =& Core_DB($params);

		// Assign the DB object to any existing models
		$this->_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Utilities Class
	 *
	 * @access	public
	 * @return	string
	 */
	function dbutil()
	{
		if ( ! class_exists('Core_DB'))
		{
			$this->database();
		}

		$CORE =& get_instance();

		require_once(BASEPATH.'database/DB_utility'.EXT);
		require_once(BASEPATH.'database/drivers/'.$CORE->db->dbdriver.'/'.$CORE->db->dbdriver.'_utility'.EXT);
		$class = 'Core_DB_'.$CORE->db->dbdriver.'_utility';

		$CORE->dbutil = new $class();
		$CORE->load->_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_load(array('view' => $view, 'vars' => $this->_object_to_array($vars), 'return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function file($path, $return = FALSE)
	{
		return $this->_load(array('path' => $path, 'return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function vars($vars = array())
	{
		$vars = $this->_object_to_array($vars);

		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_cached_vars[$key] = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	function helper($helpers = array())
	{
		if ( ! is_array($helpers))
		{
			$helpers = array($helpers);
		}

		foreach ($helpers as $helper)
		{
			$helper = strtolower(str_replace(array(EXT, '_helper'), '', $helper).'_helper');

			if (isset($this->_helpers[$helper]))
				continue;

			if (file_exists(APPPATH.'helpers/'.$helper.EXT))
			{
				include_once(APPPATH.'helpers/'.$helper.EXT);
			}
			else
			{
				if (file_exists(BASEPATH.'helpers/'.$helper.EXT))
				{
					include(BASEPATH.'helpers/'.$helper.EXT);
				}
				else
				{
					show_error('Unable to load the requested file: helpers/'.$helper.EXT);
				}
			}

			$this->_helpers[$helper] = TRUE;
		}

		log_message('debug', 'Helpers loaded: '.implode(', ', $helpers));
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function helpers($helpers = array())
	{
		$this->helper($helpers);
	}

	// --------------------------------------------------------------------

	/**
	 * Load Plugin
	 *
	 * This function loads the specified plugin.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugin($plugins = array())
	{
		if ( ! is_array($plugins))
		{
			$plugins = array($plugins);
		}

		foreach ($plugins as $plugin)
		{
			$plugin = strtolower(str_replace(array(EXT, '_plugin'), '').'_pi');

			if (isset($this->_plugins[$plugin]))
			{
				continue;
			}

			if (file_exists(APPPATH.'plugins/'.$plugin.EXT))
			{
				include(APPPATH.'plugins/'.$plugin.EXT);
			}
			else
			{
				if (file_exists(BASEPATH.'plugins/'.$plugin.EXT))
				{
					include(BASEPATH.'plugins/'.$plugin.EXT);
				}
				else
				{
					show_error('Unable to load the requested file: plugins/'.$plugin.EXT);
				}
			}

			$this->_plugins[$plugin] = TRUE;
		}

		log_message('debug', 'Plugins loaded: '.implode(', ', $plugins));
	}

	// --------------------------------------------------------------------

	/**
	 * Load Plugins
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugins($plugins = array())
	{
		$this->plugin($plugins);
	}

	// --------------------------------------------------------------------

	/**
	 * Load Script
	 *
	 * This function loads the specified include file from the
	 * application/scripts/ folder.
	 *
	 * NOTE:  This feature has been deprecated but it will remain available
	 * for legacy users.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function script($scripts = array())
	{
		if ( ! is_array($scripts))
		{
			$scripts = array($scripts);
		}

		foreach ($scripts as $script)
		{
			$script = strtolower(str_replace(EXT, '', $script));

			if (isset($this->_scripts[$script]))
			{
				continue;
			}

			if ( ! file_exists(APPPATH.'scripts/'.$script.EXT))
			{
				show_error('Unable to load the requested script: scripts/'.$script.EXT);
			}

			include(APPPATH.'scripts/'.$script.EXT);
		}

		log_message('debug', 'Scripts loaded: '.implode(', ', $scripts));
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a language file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function language($file = '', $lang = '', $return = FALSE)
	{
		$CORE =& get_instance();
		return $CORE->lang->load($file, $lang, $return);
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a config file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$CORE =& get_instance();
		$CORE->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------

	/**
	 * Scaffolding Loader
	 *
	 * This initializing function works a bit different than the
	 * others. It doesn't load the class.  Instead, it simply
	 * sets a flag indicating that scaffolding is allowed to be
	 * used.  The actual scaffolding function below is
	 * called by the front controller based on whether the
	 * second segment of the URL matches the "secret" scaffolding
	 * word stored in the application/config/routes.php
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function scaffolding($table = '')
	{
		if ($table === FALSE)
		{
			show_error('You must include the name of the table you would like to access when you initialize scaffolding');
		}

		$CORE =& get_instance();
		$CORE->_scaffolding = TRUE;
		$CORE->_scaff_table = $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _load($data)
	{
		// Set the default data variables
		foreach (array('view', 'vars', 'path', 'return') as $val)
		{
			$$val = ( ! isset($data[$val])) ? FALSE : $data[$val];
		}

		// Set the path to the requested file
		if ($path == '')
		{
			$ext = pathinfo($view, PATHINFO_EXTENSION);
			$file = ($ext == '') ? $view.EXT : $view;
			$path = $this->_view_path.$file;
		}
		else
		{
			$x = explode('/', $path);
			$file = end($x);
		}

		if ( ! file_exists($path))
		{
			show_error('Unable to load the requested file: '.$file);
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		// Only needed when running PHP 5

		if ($this->_is_instance())
		{
			$CORE =& get_instance();
			foreach (get_object_vars($CORE) as $key => $var)
			{
				if ( ! isset($this->$key))
				{
					$this->$key =& $CORE->$key;
				}
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($vars))
		{
			$this->_cached_vars = array_merge($this->_cached_vars, $vars);
		}
		extract($this->_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.

		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($path))).'<?php ');
		}
		else
		{
			include($path);
		}

		log_message('debug', 'File loaded: '.$path);

		// Return the file data if requested
		if ($return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			// PHP 4 requires that we use a global
			global $OUT;
			$OUT->set_output(ob_get_contents());
			@ob_end_clean();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @return 	void
	 */
	function _load_class($class, $params = NULL)
	{
		// Get the class name
		$class = str_replace(EXT, '', strtolower($class));

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array($class, ucfirst($class)) as $class)
		{
			$fp = $this->_find_class($class);
			$ex = $this->_find_class(config_item('subclass_prefix').$class);

			// Extension found, but no class found
			if ($ex == TRUE AND $fp == FALSE)
			{
				log_message('error', "Unable to load the requested class: ".$class);
				show_error("Unable to load the requested class: ".$class);
			}

			// Class is already loaded, log a message and stop
			if ($fp === TRUE)
			{
				log_message('debug', $class." class already loaded. Second attempt ignored.");
				return;
			}

			// No class found
			if ($fp == FALSE)
			{
				continue;
			}
			else
			{
				include($fp);
			}

			// For safety checks
			$this->_classes[] = $fp;

			// Include extension, if one was found
			if ($ex == TRUE)
			{
				include($ex);
				return $this->_init_class($class, $params);
			}
			else
			{
				eval('class '.$class.' extends Core_'.$class.' {}');
				return $this->_init_class($class, $params);
			}
		}// END FOREACH

		// If we got this far we were unable to find the requested class
		log_message('error', "Unable to load the requested class: ".$class);
		show_error("Unable to load the requested class: ".$class);
	}

	// --------------------------------------------------------------------

	/**
	 * Find class
	 *
	 * This function finds the requested class.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	array	paths to search in
	 * @return 	void
	 */
	function _find_class($class, $paths = NULL)
	{
		$paths = array_merge($this->_paths, (array) $paths);

		foreach ($paths as $path)
		{
			$fp = $path.'libraries/'.$class.EXT;

			// Safety:  Was the class already loaded by a previous call?
			if (in_array($fp, $this->_classes))
				return TRUE;

			// Does the file exist?
			if (file_exists($fp))
				return $fp;
		}

		// No class was found
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Find class
	 *
	 * This function finds the requested class.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	array	paths to search in
	 * @return 	void
	 */
	function _find_driver($library, $name, $paths = NULL)
	{
		$paths = array_merge($this->_paths, (array) $paths);

		foreach ($paths as $path)
		{
			$fp = $path.'libraries/drivers/'.$library.'_'.$name.EXT;

			if (file_exists($fp))
				return $fp;
		}

		// No class was found
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	null
	 */
	function _init_class($class, $config = FALSE)
	{
		// Is there an associated config file for this class?
		if ($config === NULL)
		{
			$config = NULL;
			if (file_exists(APPPATH.'config/'.$class.EXT))
			{
				include(APPPATH.'config/'.$class.EXT);
			}
		}

		// Set the variable name we will assign the class to
		$name  = $class;
		$class = strtolower($class);
		$classvar = ( ! isset($this->_varmap[$class])) ? $class : $this->_varmap[$class];

		// Instantiate the class
		$CORE =& get_instance();
		if ($config !== NULL)
		{
			$CORE->$classvar =& new $name($config);
		}
		else
		{
			$CORE->$classvar =& new $name;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _autoloader()
	{
		include(APPPATH.'config/autoload'.EXT);

		if ( ! isset($autoload))
		{
			return FALSE;
		}

		// Load any custome config file
		if (count($autoload['config']) > 0)
		{
			if (KOHANA_IS_PHP5)
			{
				$CORE = get_instance();
			}
			else
			{
				$CORE =& get_instance();
			}
			foreach ($autoload['config'] as $key => $val)
			{
				$CORE->config->load($val);
			}
		}

		// Load plugins, helpers, and scripts
		foreach (array('helper', 'plugin', 'script') as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load the model class.
			if (in_array('model', $autoload['libraries']))
			{
				$this->model();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('model'));
			}

			// Load scaffolding
			if (in_array('scaffolding', $autoload['libraries']))
			{
				$this->scaffolding();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
			}

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Assign to Models
	 *
	 * Makes sure that anything loaded by the loader class (libraries, plugins, etc.)
	 * will be available to models, if any exist.
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function _assign_to_models()
	{
		if (count($this->_models) == 0)
		{
			return;
		}

		if ($this->_is_instance())
		{
			$CORE =& get_instance();
			foreach ($this->_models as $model)
			{
				$CORE->$model->_assign_libraries();
			}
		}
		else
		{
			foreach ($this->_models as $model)
			{
				$this->$model->_assign_libraries();
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function _object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}

	// --------------------------------------------------------------------

	/**
	 * Determines whether we should use the Core instance or $this
	 *
	 * @access	private
	 * @return	bool
	 */
	function _is_instance()
	{
		if (KOHANA_IS_PHP5)
		{
			return TRUE;
		}

		global $CORE;
		return (is_object($CORE)) ? TRUE : FALSE;
	}

}
?>