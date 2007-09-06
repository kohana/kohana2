<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
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
 * Kohana Model Class
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/libraries/config.html
 */
class Core_Model {

	var $_parent_name = '';

	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// If the magic __get() or __set() methods are used in a Model references can't be used.
		$this->_assign_libraries( (method_exists($this, '__get') OR method_exists($this, '__set')) ? FALSE : TRUE );
		
		// We don't want to assign the model object to itself when using the
		// assign_libraries function below so we'll grab the name of the model parent
		$this->_parent_name = ucfirst(get_class($this));
		
		Log::add('debug', 'Model Class Initialized');
	}

	/**
	 * Assign Libraries
	 *
	 * Creates local references to all currently instantiated objects
	 * so that any syntax that can be legally used in a controller
	 * can be used within models.  
	 *
	 * @access private
	 */	
	private function _assign_libraries($use_reference = TRUE)
	{
		$CORE = Kohana::$instance;				
		foreach (array_keys(get_object_vars($CORE)) as $key)
		{
			if ( ! isset($this->$key) AND $key != $this->_parent_name)
			{			
				// In some cases using references can cause
				// problems so we'll conditionally use them
				if ($use_reference == TRUE)
				{
					// Needed to prevent reference errors with some configurations
					$this->$key = '';
					$this->$key = $CORE->$key;
				}
				else
				{
					$this->$key = $CORE->$key;
				}
			}
		}		
	}

}
// END Model Class
?>