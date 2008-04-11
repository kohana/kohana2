<?php defined('SYSPATH') or die('No direct script access.');

$config = array
(
	'integration'           => TRUE,        // Enable/Disable Smarty integration
	'templates_ext'         => 'tpl',
	'cache_path'            => APPPATH.'cache/',
	'global_templates_path' => APPPATH.'views/',
	'debugging_ctrl'        => FALSE,
	'debugging'             => TRUE,
	'caching'               => FALSE,
	'force_compile'         => FALSE,
	'security'              => TRUE,
	'secure_dirs'           => array         // Smarty secure directories
	(
        MODPATH.'smarty/views'
	),    
	'if_funcs'              => array         // We'll allow these functions in if statement
	(
		'array',  'list',     'trim',       'isset', 'empty', 
		'sizeof', 'in_array', 'is_array',   'true',  'false',
		'null',   'reset',    'array_keys', 'end',   'count'
	),
	'modifier_funcs'        => array         // We'll allow these modifiers
	(
		'sprintf', 'count'
	),

	'post_filters'          => array
	(
	),
	'output_filters'        => array
	(
		'trimwhitespace'
	),
	'pre_filters'           => array  
	(
	),  
	'escape_exclude_list'   => array
	(
	),
);
