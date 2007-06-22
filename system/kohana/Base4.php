<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
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
 * Core_Base - For PHP 4
 *
 * This file is used only when Kohana is being run under PHP 4.
 *
 * In order to allow Core to work under PHP 4 we had to make the Loader class
 * the parent of the Controller Base class.  It's the only way we can
 * enable functions like $this->load->library('email') to instantiate
 * classes that can then be used within controllers as $this->email->send()
 *
 * PHP 4 also has trouble referencing the Core super object within application
 * constructors since objects do not exist until the class is fully
 * instantiated.  Basically PHP 4 sucks...
 *
 * Since PHP 5 doesn't suffer from this problem so we load one of
 * two files based on the version of PHP being run.
 *
 * @package		Kohana
 * @subpackage	Core
 * @category	front-controller
 * @author		Rick Ellis
 */
class Kohana extends Core_Loader {

	var $_shutdown_events = array();

	function Kohana()
	{
		// This allows syntax like $this->load->foo() to work
		parent::Core_Loader();
		$this->load =& $this;

		// This allows resources used within controller constructors to work
		global $OBJ;
		$OBJ = $this->load; // Do NOT use a reference.
	}

}

function &get_instance()
{
	global $CORE, $OBJ;
	
	// We can't use a ternary here, PHP4 bug
	if (is_object($CORE))
	{
		return $CORE;
	}
	else
	{
		return $OBJ->load;
	}
}

function add_shutdown_event($array)
{
	if ($array != FALSE)
	{
		global $CORE;

		array_unshift($CORE->_shutdown_events, $array);
	}
}

function get_shutdown_events()
{
	global $CORE;

	return $CORE->_shutdown_events;
}

?>