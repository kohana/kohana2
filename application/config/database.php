<?php defined('SYSPATH') or die('No direct script access.');

$config['default'] = array
(
	'show_errors'   => TRUE,
	'benchmark'     => ! IN_PRODUCTION,
	'persistent'    => FALSE,
	'connection'    => 'mysql://root:r00tdb@localhost/kohana',
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => IN_PRODUCTION,
);

$config['website'] = $config['default'];