<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database Configuration
 * -----------------------------------------------------------------------------
 * Database connection settings, defined by group name. If no group name is used
 * when loading the database library, the group named "default" will be used.
 * All of the variables listed below are explained in detail in the User Guide.
 *
 * User Guide: http://kohanaphp.com/user_guide/en/libraries/database.html
 *
 * @param boolean show_errors   - Enable or disable database exceptions
 * @param boolean benchmark     - Enable or disable database benchmarking
 * @param boolean persistent    - Enable or disable a persistent connection
 * @param string  connection    - DSN identifier: driver://user:password@server/database
 * @param string  character_set - Connection character set
 * @param string  table_prefix  - Database table prefix
 * @param boolean object        - Return objects (TRUE) or arrays (FALSE)
 *
 */
$config['default'] = array
(
	'show_errors'   => TRUE,
	'benchmark'     => TRUE,
	'persistent'    => FALSE,
	'connection'    => 'mysql://dbuser:secret@localhost/kohana',
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE
);
