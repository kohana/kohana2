<?php

/**
 * @package  Core
 *
 * Sets default routing, allowing up to 3 segments to be used:
 *  - controller, defaults to "welcome"
 *  - method, defaults to "index"
 *  - id, no default
 *
 * The converted regex for this route is:
 *
 *     (?:([^/]+)(?:/([^/]+)(?:/([^/]+))?)?)?
 *
 */
$config['default'] = array
(
	// Default routing
	':controller/:method/:id.xml',

	// Defaults for route keys
	'controller' => 'welcome',
	'method' => 'index',
);
