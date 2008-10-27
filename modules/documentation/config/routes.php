<?php
$config['kohana_documentation'] = array
(
	'route' => array
	(
		'uri' => 'docs',
	),

	'controller' => 'kohana_documentation',
	'method'     => 'index'
);

$config['kohana_media'] = array
(
	'route' => array
	(
		'uri'   => 'docs/:method/:file',
		'regex' => array('method' => 'js|css|img')
	),

	'controller' => 'kohana_documentation',
	'method'     => 'user_guide',

);

$config['kohana_user_guide'] = array
(
	'route' => array
	(
		'uri'   => 'docs/user_guide/:lang/:page/:dummy',
		'regex' => array('dummy' => '.*')
	),

	'controller' => 'kohana_documentation',
	'method'     => 'user_guide',
	'lang'       => 'en',
	'page'       => 'contents'
);

$config['kohana_api_browse'] = array
(
	'route' => array
	(
		'uri' => 'docs/api/browse/:sort',
	),

	'controller' => 'kohana_documentation',
	'method'     => 'browse_api',
	'sort'       => 'name'
);

$config['kohana_api'] = array
(
	'route' => array
	(
		'uri' => 'docs/api/class/:class',
	),

	'controller' => 'kohana_documentation',
	'method'     => 'api',
	'class'      => false
);
