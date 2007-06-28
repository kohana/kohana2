<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| AUTHENTICATION
| -------------------------------------------------------------------
|
| There are two methods to provide database authentication/selection,
| the array method, and the DSN (Data Source Name) method.
|
| Array Method:
| -------------------------------------------------------------------
| ['hostname'] The hostname of your database server.
| ['username'] The username used to connect to the database
| ['password'] The password used to connect to the database
| ['database'] The name of the database you want to connect to
| ['dbdriver'] The database type. ie: mysql.  Currently supported:
|              mysql, mysqli, postgre, odbc, mssql, oci8
|
| DSN Method:
| -------------------------------------------------------------------
| ['database'] DSN formatted connection string
|              example: dbdriver://username:password@hostname/database
|              example: postgre://root:root@localhost/test
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
| ['dbprefix'] You can add an optional prefix, which will be added
|              to the table name when using the  Active Record class
| ['pconnect'] TRUE/FALSE - Whether to use a persistent connection
| ['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
| ['cache_on'] TRUE/FALSE - Enables/disables query caching
| ['cachedir'] The path to the folder where cache files should be stored
|
| The $active_group variable lets you choose which connection group to
| make active.
|
| A database group 'kohana_session' is required if you are using the
| Session_Database driver. If you are not using a database for session
| storage, you may remove this group.
|
*/

$active_group = 'default';

$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = 'pass';
$db['default']['database'] = 'test';
$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['charset']  = '';
//
// Sessions database group
$db['kohana_session']['hostname'] = 'localhost';
$db['kohana_session']['username'] = 'sessions';
$db['kohana_session']['password'] = 'sessions';
$db['kohana_session']['database'] = 'sessions';
$db['kohana_session']['dbdriver'] = 'mysql';
$db['kohana_session']['dbprefix'] = '';
$db['kohana_session']['pconnect'] = TRUE;
$db['kohana_session']['db_debug'] = TRUE;
$db['kohana_session']['cache_on'] = FALSE;
$db['kohana_session']['cachedir'] = '';
$db['kohana_session']['charset']  = '';

?>