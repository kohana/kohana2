<?php defined('SYSPATH') or die('No direct access allowed.');
/*
 * File: Routes
 *
 * Supported Shortcuts:
 *  :any - matches any non-blank string
 *  :num - matches any number
 *
 * Options:
 *  _allowed - Permitted URI characters
 *  _default - Default route when no URI segments are found
 */
$config = array
(
	'_allowed' => 'a-z 0-9~%.:_-',
	'_default' => 'user_guide'
);