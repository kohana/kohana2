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
 * Model Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/general/models.html
 */
class Model_Core {

	public function __construct()
	{
		// Load the database into the model
		$this->db = isset(Kohana::instance()->db) ? Kohana::instance()->db : new Database('default');
	}

} // End Model Core