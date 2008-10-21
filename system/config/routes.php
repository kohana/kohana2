<?php
/**
 * @package  Core
 *
 * Sets default routing, allowing up to 3 segments to be used.
 *
 *     $config['default'] = array
 *     (
 *         // Default routing
 *         'route' => array
 *         (
 *             'uri' => :controller/:method/:id',
 *         ),
 *         
 *         // Defaults for route keys
 *         'controller' => 'welcome',
 *         'method' => 'index',
 *     );
 *
 * To define a specific pattern for a key, you can use the special "regex" key:
 *
 *     $config['default'] = array
 *     (
 *         // Limit the controller to letters and underscores
 *         'route' => array
 *         (
 *             'regex' => array('controller' => '[a-z_]+')
 *         ),
 *     );
 *
 * To add a prefix to any key, you can use the special "prefix" key:
 *
 *     $config['admin'] = array
 *     (
 *         'route' => array
 *         (
 *             'prefix' => array('controller' => 'admin_'),
 *         ),
 *     );
 *
 */
$config['default'] = array
(
	'route' => array
	(
		// Default routing
		'uri' => ':controller/:method/:id',
	),

	// Defaults for route keys
	'controller' => 'welcome',
	'method' => 'index',
);
