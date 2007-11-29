<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Supported Shortcuts:
 *  :any - matches any non-blank string
 *  :num - matches any number
 */

/**
 * Permitted URI characters.
 */
$config['_allowed'] = 'a-z 0-9~%.:_-';

/**
 * Default route when no URI segments are found.
 */
$config['_default'] = 'welcome';