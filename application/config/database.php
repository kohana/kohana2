<?php defined('SYSPATH') or die('No direct script access.');
/*
| Database Configuration
| -----------------------------------------------------------------------------
| Database connection settings, defined by group name. If no group name is used
| when loading the database library, the group named "default" will be used.
| All of the variables listed below are explained in detail in the User Guide.
|
| User Guide: http://kohanaphp.com/user_guide/en/libraries/database.html
|
| Settings:
| - show_errors    Enable or disable database exceptions
| - persistent     Enable or disable a persistent connection
| - connection     DSN identifer: driver://user:password@server/database
| - character_set  Connection character set
| - table_prefix   Database table prefix
*/

$config['default'] = array
(
	'show_errors'   => TRUE,
	'persistent'    => FALSE,
	'connection'    => 'mysql://dbuser:secret@localhost/kohana',
	'character_set' => 'utf-8',
	'table_prefix'  => '',
	'object'        => TRUE
);
