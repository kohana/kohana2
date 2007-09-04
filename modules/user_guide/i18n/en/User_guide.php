<?php defined('SYSPATH') or die('No direct access allowed.');
/* $Id: user_guide.php 485 2007-09-04 00:12:20Z Shadowhand $ */

$lang = array
(
	/**
	* Kohana User Guide Menu
	* Topics in Categories (Eg, General, Libraries)
	* Articles in Sections (Eg, Installation)
	* Libraries and Helpers are in alphabetic order
	* Other Categories are in logical order
	*/
	'menu' => array
	(
		'Kohana' => array
		(
			'About',
			'Requirements',
			'Downloads',
			'Installation',
			'Links'
		),
		'General' => array
		(
			'Definitions',
			'Bootstrapping',
			'Configuration',
			'Libraries',
			'Controllers',
			'Models',
			'Views',
			'Helpers'
		),
		'Libraries' => array
		(
			'Cache',
			'Controller',
			'Database',
			'Encryption',
			'Input',
			'Loader',
			'Model',
			'Pagination',
			'Router',
			'Session',
			'URI',
			'View'
		),
		'Helpers' => array
		(
			'Cookie',
			'File',
			'Html',
			'Text',
			'Url'
		)
	),
	'title'     => 'Kohana User Guide',
	'copyright' => 'copyright (c) %s Kohana Team :: All rights reserved :: Rendered in {execution_time} seconds using {memory_usage}MB of memory',
);