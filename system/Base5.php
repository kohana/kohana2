<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BlueFlame
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		BlueFlame
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://blueflame.ciforge.com
 * @since		Version 1.3
 * @filesource
 */


// ------------------------------------------------------------------------

/**
 * CI_BASE - For PHP 5
 *
 * This file contains some code used only when BlueFlame is being
 * run under PHP 5.  It allows us to manage the CI super object more
 * gracefully than what is possible with PHP 4.
 *
 * @package		BlueFlame
 * @subpackage	blueflame
 * @category	front-controller
 * @author		Rick Ellis
 * @link		http://blueflame.ciforge.com
 */

class CI_Base {

	private static $instance;

	public function CI_Base()
	{
		self::$instance =& $this;
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
}

function &get_instance()
{
	return CI_Base::get_instance();
}


?>