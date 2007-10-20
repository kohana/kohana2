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
 * $Id: Database_Mysql.php 854 2007-10-19 15:46:31Z Shadowhand $
 */

/**
 * Image GD Driver
 *
 * @category    Image
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/database.html
 */
class Image_Gd_Driver implements Image_Driver {

	public function __construct()
	{
		Log::add('debug', 'Image GD Driver Initialized');
	}
	
	/**
	 * Returns the driver version
	 *
	 * @access  public
	 * @return  string
	 */
	public function version()
	{
		return 'GD '.current(gd_info());
	}

}