<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
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
 * Initialize the database
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/database/
 */
function &DB($params = '')
{
	// Load the DB config file if a DSN string wasn't passed
	if (is_string($params))
	{
		if (strpos($params, '://'))
		{
			$params = (array) _parse_db_dsn($params['database']);
		}
		else
		{
			include(APPPATH.'config/database'.EXT);

			$group = ($params == '') ? $active_group : $params;

			if ( ! isset($db[$group]))
			{
				show_error('You have specified an invalid database connection group: '.$group);
			}

			$params = $db[$group];
		}

		// Make sure defaults are defined
		$params += array
		(
			'hostname' => '',
			'username' => '',
			'password' => '',
			'database' => '',
			'conn_id'  => FALSE,
			'dbdriver' => 'mysql',
			'dbprefix' => '',
			'port'     => '',
			'pconnect' => FALSE,
			'db_debug' => FALSE,
			'cachedir' => '',
			'cache_on' => FALSE,
			'charset'  => ''
		);

		if (strpos($params['database'], '://'))
		{
			$params = array_merge($params, (array) _parse_db_dsn($params['database']));
		}
	}

	// No DB specified yet?  Beat them senseless...
	if ($params['dbdriver'] == '')
	{
		show_error('You have not selected a database type to connect to.');
	}

	// Load the DB classes.  Note: Since the active record class is optional
	// we need to dynamically create a class that extends proper parent class
	// based on whether we're using the active record class or not.
	// Kudos to Paul for discovering this clever use of eval()

	require_once(BASEPATH.'database/DB_driver'.EXT);

	$CI =& get_instance();
	if ($CI->config->item('disable_ar')==FALSE)
	{
		require_once(BASEPATH.'database/DB_active_rec'.EXT);

		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_active_record { }');
		}
	}
	else
	{
		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_driver { }');
		}
	}

	require_once(BASEPATH.'database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver'.EXT);

	// Instantiate the DB adapter
	$driver = 'CI_DB_'.$params['dbdriver'].'_driver';
	$DB =& new $driver($params);

	return $DB;
}

// ------------------------------------------------------------------------

/**
 * Parse a Database DSN
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/database/
 */
function _parse_db_dsn($dsn)
{
	if (($dsn = @parse_url($dsn)) == FALSE)
		return FALSE;

	$keys = array
	(
		'scheme' => 'dbdriver',
		'host'   => 'hostname',
		'user'   => 'username',
		'pass'   => 'password',
		'path'   => 'database'
	);

	foreach($keys as $val => $key)
	{
		if ( ! isset($dsn[$val]))
			continue;

		$val = ($key == 'database') ? substr($dsn[$val], 1) : $dsn[$val];
		$config[$key] = rawurldecode($val);
	}

	return $config;
}


?>