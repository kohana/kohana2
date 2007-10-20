<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Image API Driver
 *
 * @category    Image
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/image.html
 */
interface Image_Driver {

	/**
	 * Returns the driver version
	 *
	 * @access  public
	 * @return  string
	 */
	public function version();

} // End Image Driver Interface