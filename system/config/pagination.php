<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * File: Pagination
 *
 * Options:
 *  directory      - Views folder in which your pagination style templates reside
 *  style          - Style name (matches template filename)
 *  uri_segment    - URI segment (or 'label') in which the current page number can be found
 *  items_per_page - Number of items in a page of results
 */
$config = array
(
	'directory'      => 'pagination',
	'style'          => 'classic',
	'uri_segment'    => 3,
	'items_per_page' => 20
);