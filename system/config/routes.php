<?php
/**
 * @package  Core
 *
 * Sets default routing, allowing up to 3 segments to be used.
 *
 *     $config['default'] = array
 *     (
 *         'uri' => ':controller/:method/:id',
 * 
 *         'keys' => array
 *         (
 *             // Default key values
 *             'controller' => 'welcome',
 *             'method'     => 'index',
 *             'id'         => FALSE,
 *         ),
 *     );
 *
 * To define a specific pattern for a key, you can use the special "regex" key:
 *
 *     'regex' => array('controller' => '[a-z_]+')
 *
 * To add a prefix to any key, you can use the special "prefix" key:
 *
 *     'prefix' => array('controller' => 'admin_')
 *
 */
$config['default'] = array
(
	'uri' => ':controller/:method/:id',

	'defaults' => array
	(
		// Default key values
		'controller' => 'welcome',
		'method'     => 'index',
		'id'         => FALSE,
	),
);
