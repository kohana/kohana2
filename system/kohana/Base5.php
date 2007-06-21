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
 * Core_Base - For PHP 5
 *
 * This file contains some code used only when Kohana is being
 * run under PHP 5.  It allows us to manage the Core super object more
 * gracefully than what is possible with PHP 4.
 *
 * @package		Kohana
 * @subpackage	Core
 * @category	front-controller
 * @author		Rick Ellis
 */
class Kohana {

	private static $instance;

	public function Kohana()
	{
		self::$instance = $this;
	}

	public static function make_instance(&$obj)
	{
		$obj = self::$instance;
	}

	// ------------------------------------------------------------------------

	private $_shutdown_events = array();

	public function add_shutdown_event($array)
	{
		if ($array != FALSE)
		{
			array_unshift($this->_shutdown_events, $array);
		}
	}

	public function get_shutdown_events()
	{
		return $this->_shutdown_events;
	}
}

function &get_instance()
{
	Kohana::make_instance($CORE);
	return $CORE;
}

function add_shutdown_event($array)
{
	Kohana::make_instance($CORE);
	return $CORE->add_shutdown_event($array);
}

function get_shutdown_events()
{
	Kohana::make_instance($CORE);
	return $CORE->get_shutdown_events();
}

?>