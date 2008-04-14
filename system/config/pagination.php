<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package  Pagination
 *
 * Views folder in which your pagination style templates reside.
 */
$config['directory'] = 'pagination';

/**
 * Pagination style template (matches view filename).
 */
$config['style'] = 'classic';

/**
 * URI segment (or 'label') in which the current page number can be found.
 */
$config['uri_segment'] = 3;

/**
 * Number of items to display per page.
 */
$config['items_per_page'] = 20;

/**
 * Automatically hide pagination completely for single pages.
 */
$config['auto_hide'] = FALSE;