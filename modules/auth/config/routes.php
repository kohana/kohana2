<?php

if ( ! IN_PRODUCTION)
{
	$config['auth_demo'] = array
	(
		'uri' => 'auth/:method/:id',

		'defaults' => array
		(
			'controller' => 'auth',
			'method'     => 'index',
			'id'         => FALSE,
		),
	);
}
