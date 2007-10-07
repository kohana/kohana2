<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Route Configuration
 * -----------------------------------------------------------------------------
 * Routes can be defined as literal matches, regular expressions, and shortcuts.
 * The "_default" route is reserved for a blank URI string, eg: home page.
 *
 * Supported shortcuts are:
 *
 *   :any - matches any non-blank string
 *   :num - matches any number
 *
 * User Guide: http://kohanaphp.com/user_guide/en/libraries/database.html
 *
 */
$config = array
(
	'_default'   => 'main',
	'media(.*)'  => 'main/media$1',
	'(.*)'       => 'main/$1',
);