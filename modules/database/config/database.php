<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package  Database
 *
 * Database connection settings, defined as arrays, or "groups". If no group
 * name is used when loading the database library, the group named "default"
 * will be used.
 *
 * Each group can be connected to independently, and multiple groups can be
 * connected at once.
 *
 * Group Options:
 *  benchmark     - Enable or disable database benchmarking
 *  persistent    - Enable or disable a persistent connection
 *  connection    - Array of connection specific parameters; alternatively,
 *                  you can use a DSN though it is not as fast and certain
 *                  characters could create problems (like an '@' character
 *                  in a password):
 *                  'connection'    => 'mysql://dbuser:secret@localhost/kohana'
 *  character_set - Database character set
 *  table_prefix  - Database table prefix
 *  object        - Enable or disable object results
 *  cache         - Enable or disable query caching
 *	escape        - Enable automatic query builder escaping
 */
$config['default'] = array
(
	'hostname'     => 'localhost',
	'database'     => 'database',
	'username'     => 'username',
	'password'     => 'password',
	'type'         => 'mysql',
	'table_prefix' => 'tbl_',
	'persistent'   => TRUE,



	/*'benchmark'     => TRUE,
	'persistent'    => TRUE,
	'connection'    => array
	(
		'type'     => 'mysql',
		'user'     => 'jheathco_kohana',
		'pass'     => 'heathcodb',
		'host'     => 'heathcosoft.com',
		'port'     => 3306,
		'socket'   => FALSE,
		'database' => 'jheathco_kohana'
	),
	'character_set' => 'utf8',
	'table_prefix'  => 'u',
	'object'        => TRUE,
	'cache'         => 'db',
	'escape'        => TRUE*/
);