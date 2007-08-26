<?php defined('SYSPATH') or die('No direct script access.');

$config['_active'] = 'default';

$config['default'] = array
(
	'connection'    => 'mysql://dbuser:secret@localhost/kohana',
	'persistent'    => FALSE,
	'show_errors'   => TRUE,
	'character_set' => 'utf-8',
	'table_prefix'  => ''
);
