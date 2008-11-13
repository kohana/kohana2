<?php

$config['kohana_documentation'] = array
(
	'uri' => 'docs',

	'defaults' => array
	(
		'controller' => 'kohana_documentation',
		'method'     => 'index'
	),
);


$config['kohana_media'] = array
(
	'uri'   => 'docs/:method/:file',
	'regex' => array('method' => 'js|css|img')

	'defaults' => array
	(
		'controller' => 'kohana_documentation',
		'method'     => 'user_guide',
	),
);

$config['kohana_user_guide'] = array
(
	'uri'   => 'docs/user_guide/:lang/:page/:dummy',
	'regex' => array('dummy' => '.*')

	'defaults' => array
	(
		'controller' => 'kohana_documentation',
		'method'     => 'user_guide',
		'lang'       => 'en',
		'page'       => 'contents'
	),
);

$config['kohana_api_browse'] = array
(
	'uri' => 'docs/api/browse/:sort',

	'defaults' => array
	(
		'controller' => 'kohana_documentation',
		'method'     => 'browse_api',
		'sort'       => 'name'
	),
);

$config['kohana_api'] = array
(
	'uri' => 'docs/api/class/:class',

	'defaults' => array
	(
		'controller' => 'kohana_documentation',
		'method'     => 'api',
		'class'      => false
	),
);
